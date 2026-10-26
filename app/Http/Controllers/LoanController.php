<?php

namespace App\Http\Controllers;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\LoanDraft;
use App\Models\LoanPayment;
use App\Mail\PaymentReceiptMail;
use App\Services\LoanCalculator;
use App\Services\LoanAccounting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LoanController extends Controller
{
    public function __construct(private readonly LoanCalculator $calculator, private readonly LoanAccounting $accounting) {}

    public function index(): View
    {
        return view('loans', [
            'loans' => Loan::with('borrower')->latest()->get(),
            'drafts' => LoanDraft::where('user_id', auth()->id())->latest('updated_at')->get(),
        ]);
    }

    public function search(Request $request): View
    {
        $term = trim((string) $request->query('q', ''));
        $borrowers = collect();
        $loans = collect();

        if ($term !== '') {
            $pattern = '%'.$term.'%';
            $borrowers = Borrower::query()
                ->where('full_name', 'like', $pattern)
                ->orWhere('phone', 'like', $pattern)
                ->orWhere('email', 'like', $pattern)
                ->latest()
                ->limit(25)
                ->get();

            $loans = Loan::with('borrower')
                ->where(function ($query) use ($pattern, $term) {
                    $query->where('reference', 'like', $pattern)
                        ->orWhereHas('borrower', fn ($borrowerQuery) => $borrowerQuery->where('full_name', 'like', $pattern));

                    if (ctype_digit($term)) {
                        $query->orWhereKey((int) $term);
                    }
                })
                ->latest()
                ->limit(25)
                ->get();
        }

        return view('search', compact('term', 'borrowers', 'loans'));
    }

    public function searchSuggestions(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $pattern = '%'.$term.'%';
        $borrowers = Borrower::query()
            ->where('full_name', 'like', $pattern)
            ->orWhere('phone', 'like', $pattern)
            ->orWhere('email', 'like', $pattern)
            ->latest()
            ->limit(4)
            ->get(['id', 'full_name', 'phone', 'email'])
            ->map(fn (Borrower $borrower) => [
                'type' => 'Borrower',
                'label' => $borrower->full_name,
                'description' => $borrower->phone ?: ($borrower->email ?: 'Borrower record'),
                'url' => route('borrowers.show', $borrower),
            ]);

        $loans = Loan::with('borrower:id,full_name')
            ->where(function ($query) use ($pattern, $term) {
                $query->where('reference', 'like', $pattern)
                    ->orWhereHas('borrower', fn ($borrowerQuery) => $borrowerQuery->where('full_name', 'like', $pattern));

                if (ctype_digit($term)) {
                    $query->orWhereKey((int) $term);
                }
            })
            ->latest()
            ->limit(4)
            ->get()
            ->map(fn (Loan $loan) => [
                'type' => 'Loan',
                'label' => $loan->reference,
                'description' => $loan->borrower->full_name.' · ₱'.number_format((float) $loan->principal, 2),
                'url' => route('loans.disclosure', $loan),
            ]);

        return response()->json($borrowers->concat($loans)->take(8)->values());
    }

    public function dashboard(): View
    {
        $allLoans = Loan::with('payments')->get();
        $analyticsLoans = $allLoans;
        $accountingSummaries = $allLoans->map(fn (Loan $loan) => $this->accounting->summary($loan));
        $monthKeys = collect(range(5, 0))->map(fn (int $monthsAgo) => now()->startOfMonth()->subMonths($monthsAgo));
        $monthlyAnalytics = $monthKeys->map(function ($month) use ($analyticsLoans) {
            $loans = $analyticsLoans->filter(fn (Loan $loan) => $loan->release_date->format('Y-m') === $month->format('Y-m'));

            return [
                'label' => $month->format('M Y'),
                'short_label' => $month->format('M'),
                'count' => $loans->count(),
                'principal' => (float) $loans->sum('principal'),
            ];
        });
        $purposeAnalytics = $analyticsLoans->groupBy(fn (Loan $loan) => $loan->purpose ?: 'Unspecified')
            ->map(fn ($loans, $purpose) => ['purpose' => $purpose, 'count' => $loans->count(), 'principal' => (float) $loans->sum('principal')])
            ->sortByDesc('principal')->values();

        return view('dashboard', [
            'loanCount' => Loan::count(),
            'borrowerCount' => Borrower::count(),
            'principalTotal' => (float) Loan::sum('principal'),
            'repaymentTotal' => round((float) $allLoans->sum('total_payments') + (float) $accountingSummaries->sum('late_penalty'), 2),
            'returnedTotal' => (float) $accountingSummaries->sum('returned'),
            'remainingTotal' => (float) $accountingSummaries->sum('remaining_due'),
            'recentLoans' => Loan::with('borrower')->latest()->take(5)->get(),
            'monthlyAnalytics' => $monthlyAnalytics,
            'purposeAnalytics' => $purposeAnalytics,
        ]);
    }

    public function reports(Request $request): View
    {
        $filters = $request->validate([
            'report' => ['nullable', 'in:collections,outstanding,overdue,borrower-statements'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'borrower_id' => ['nullable', 'integer', 'exists:borrowers,id'],
        ]);
        $activeReport = $filters['report'] ?? 'collections';
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;
        $borrowers = Borrower::orderBy('full_name')->get(['id', 'full_name']);

        $collectionQuery = LoanPayment::with('loan.borrower')
            ->when($from, fn ($query) => $query->whereDate('paid_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('paid_at', '<=', $to))
            ->orderByDesc('paid_at');
        $collectionPayments = $collectionQuery->get();

        $outstandingLoans = Loan::with(['borrower', 'payments'])
            ->when($from, fn ($query) => $query->whereDate('release_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('release_date', '<=', $to))
            ->latest('release_date')->get()
            ->map(function (Loan $loan) {
                return ['loan' => $loan, 'summary' => $this->accounting->summary($loan)];
            })->filter(fn (array $row) => $row['summary']['remaining_due'] > 0.009)->values();

        $overduePayments = Loan::with(['borrower', 'payments'])->get()
            ->flatMap(function (Loan $loan) use ($from, $to) {
                return collect($this->accounting->summary($loan)['installments'])
                    ->filter(function (array $installment) use ($from, $to) {
                        return $installment['payment_status'] === 'Overdue'
                            && (! $from || $installment['due_date'] >= $from)
                            && (! $to || $installment['due_date'] <= $to);
                    })
                    ->map(fn (array $installment) => [
                        'loan' => $loan,
                        'installment' => $installment,
                        'days_late' => Carbon::parse($installment['due_date'])->startOfDay()->diffInDays(now()->startOfDay()),
                    ]);
            })->values();

        $selectedBorrower = ! empty($filters['borrower_id'])
            ? Borrower::with(['loans' => fn ($query) => $query->with('payments')->latest('release_date')])->find($filters['borrower_id'])
            : null;
        $statementPayments = $selectedBorrower
            ? LoanPayment::with('loan')->whereHas('loan', fn ($query) => $query->where('borrower_id', $selectedBorrower->id))
                ->when($from, fn ($query) => $query->whereDate('paid_at', '>=', $from))
                ->when($to, fn ($query) => $query->whereDate('paid_at', '<=', $to))
                ->orderBy('paid_at')->get()
            : collect();

        return view('reports', compact(
            'activeReport', 'from', 'to', 'borrowers', 'collectionPayments',
            'outstandingLoans', 'overduePayments', 'selectedBorrower', 'statementPayments'
        ));
    }

    public function exportLoansCsv(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'Loan reference', 'Status', 'Borrower name', 'Borrower address', 'Borrower phone', 'Borrower email',
                'Borrower date of birth', 'Co-borrower name', 'Co-borrower address', 'Co-borrower phone',
                'Co-borrower email', 'Second co-borrower name', 'Second co-borrower address',
                'Second co-borrower phone', 'Second co-borrower email', 'Release date', 'Purpose', 'Principal',
                'Monthly rate', 'Term (months)', 'Total interest', 'Total charges', 'Net proceeds', 'Total payments',
                'Payment date', 'Payment amount', 'Payment note',
            ], ',', '"', '');

            $safeText = static function ($value): string {
                $value = (string) ($value ?? '');

                return preg_match('/^[\s\x00-\x1F]*[=+@\-]/u', $value) ? "'".$value : $value;
            };

            Loan::with(['borrower', 'payments'])->chunkById(100, function ($loans) use ($output, $safeText) {
                foreach ($loans as $loan) {
                    $borrower = $loan->borrower;
                    $payments = $loan->payments;
                    $paymentRows = $payments->isEmpty() ? [null] : $payments;

                    foreach ($paymentRows as $payment) {
                        fputcsv($output, [
                            $safeText($loan->reference), $safeText($loan->status), $safeText($borrower?->full_name),
                            $safeText($borrower?->address), $safeText($borrower?->phone), $safeText($borrower?->email),
                            $borrower?->date_of_birth?->format('Y-m-d'), $safeText($loan->co_borrower_name),
                            $safeText($loan->co_borrower_address), $safeText($loan->co_borrower_phone),
                            $safeText($loan->co_borrower_email), $safeText($loan->second_co_borrower_name),
                            $safeText($loan->second_co_borrower_address), $safeText($loan->second_co_borrower_phone),
                            $safeText($loan->second_co_borrower_email), $loan->release_date?->format('Y-m-d'),
                            $safeText($loan->purpose), $loan->principal, $loan->monthly_rate, $loan->term_months,
                            $loan->total_interest, $loan->total_charges, $loan->net_proceeds, $loan->total_payments,
                            $payment?->paid_at?->format('Y-m-d'), $payment?->amount, $safeText($payment?->note),
                        ], ',', '"', '');
                    }
                }
            });

            fclose($output);
        }, 'loan-records-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, private',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function create(Request $request): View
    {
        $savedDraft = null;
        if ($request->filled('draft')) {
            $savedDraft = LoanDraft::where('user_id', $request->user()->id)->findOrFail($request->integer('draft'));
        }
        if ($request->boolean('resume_preview') && ! $savedDraft) {
            $request->session()->put('resume_preview_edit', true);
        } else {
            $request->session()->forget('resume_preview_edit');
        }
        $draftData = $savedDraft?->data ?? ($request->boolean('resume_preview') ? session('loan_draft', []) : []);

        return view('loans.create', [
            'draft' => $draftData,
            'savedDraftId' => $savedDraft?->id,
        ]);
    }

    public function saveDraft(Request $request): RedirectResponse
    {
        $rules = [
            'saved_draft_id' => ['nullable', 'integer'],
            'full_name' => ['nullable', 'string', 'max:150'],
            'first_name' => ['nullable', 'string', 'max:60'],
            'middle_name' => ['nullable', 'string', 'max:60'],
            'last_name' => ['nullable', 'string', 'max:60'],
            'suffix' => ['nullable', 'in:Jr.,Sr.,II,III,IV,V'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:150'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'id_type' => ['nullable', 'in:PhilID,Passport,Driver License,UMID,PRC ID,Postal ID,Voter ID,Other'],
            'id_number' => ['nullable', 'string', 'max:100'],
            'id_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'co_borrower_name' => ['nullable', 'string', 'max:150'],
            'co_borrower_first_name' => ['nullable', 'string', 'max:60'],
            'co_borrower_middle_name' => ['nullable', 'string', 'max:60'],
            'co_borrower_last_name' => ['nullable', 'string', 'max:60'],
            'co_borrower_suffix' => ['nullable', 'in:Jr.,Sr.,II,III,IV,V'],
            'co_borrower_enabled' => ['nullable', 'in:1'],
            'co_borrower_address' => ['nullable', 'string', 'max:255'],
            'co_borrower_phone' => ['nullable', 'string', 'max:40'],
            'co_borrower_email' => ['nullable', 'email', 'max:150'],
            'co_borrower_date_of_birth' => ['nullable', 'date', 'before:today'],
            'co_borrower_id_type' => ['nullable', 'in:PhilID,Passport,Driver License,UMID,PRC ID,Postal ID,Voter ID,Other'],
            'co_borrower_id_number' => ['nullable', 'string', 'max:100'],
            'co_borrower_id_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'second_co_borrower_name' => ['nullable', 'string', 'max:150'],
            'second_co_borrower_first_name' => ['nullable', 'string', 'max:60'],
            'second_co_borrower_middle_name' => ['nullable', 'string', 'max:60'],
            'second_co_borrower_last_name' => ['nullable', 'string', 'max:60'],
            'second_co_borrower_suffix' => ['nullable', 'in:Jr.,Sr.,II,III,IV,V'],
            'second_co_borrower_enabled' => ['nullable', 'in:1'],
            'second_co_borrower_address' => ['nullable', 'string', 'max:255'],
            'second_co_borrower_phone' => ['nullable', 'string', 'max:40'],
            'second_co_borrower_email' => ['nullable', 'email', 'max:150'],
            'second_co_borrower_date_of_birth' => ['nullable', 'date', 'before:today'],
            'second_co_borrower_id_type' => ['nullable', 'in:PhilID,Passport,Driver License,UMID,PRC ID,Postal ID,Voter ID,Other'],
            'second_co_borrower_id_number' => ['nullable', 'string', 'max:100'],
            'second_co_borrower_id_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'purpose' => ['nullable', 'in:Agriculture / farming,Business / livelihood,Education,Medical expenses,Home improvement,Emergency,Other'],
            'purpose_other' => ['nullable', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0.01', 'max:9999999999'],
            'term' => ['nullable', 'integer', 'min:1', 'max:120'],
            'monthly_rate' => ['nullable', 'numeric', 'min:0.01', 'max:100'],
            'calculation_type' => ['nullable', 'in:flat,diminishing'],
            'start_date' => ['nullable', 'date'],
            'notarial_fee' => ['nullable', 'numeric', 'min:0'],
            'inspection_fee' => ['nullable', 'numeric', 'min:0'],
            'registration_fee' => ['nullable', 'numeric', 'min:0'],
            'documentary_stamp_fee' => ['nullable', 'numeric', 'min:0'],
            'service_fee' => ['nullable', 'numeric', 'min:0'],
            'loan_terms' => ['nullable', 'string', 'max:3000'],
        ];
        $draftInput = $request->all();
        foreach (['', 'co_borrower_', 'second_co_borrower_'] as $prefix) {
            $middleName = trim($draftInput[$prefix.'middle_name'] ?? '');
            $nameParts = [
                trim($draftInput[$prefix.'first_name'] ?? ''),
                strcasecmp($middleName, 'N/A') === 0 ? '' : $middleName,
                trim($draftInput[$prefix.'last_name'] ?? ''),
                trim($draftInput[$prefix.'suffix'] ?? ''),
            ];
            $draftInput[$prefix.($prefix === '' ? 'full_name' : 'name')] = implode(' ', array_filter($nameParts, fn ($part) => $part !== ''));
        }
        $data = Validator::make($draftInput, $rules)->validate();
        $savedDraft = null;
        if (! empty($data['saved_draft_id'])) {
            $savedDraft = LoanDraft::where('user_id', $request->user()->id)->findOrFail($data['saved_draft_id']);
        }
        unset($data['saved_draft_id']);
        $previousDraftData = $savedDraft?->data;
        if (! $previousDraftData && (bool) $request->session()->get('resume_preview_edit')) {
            $previousDraftData = $request->session()->get('loan_draft', []);
        }

        foreach ([
            'id_file' => 'id_file_path',
            'co_borrower_id_file' => 'co_borrower_id_file_path',
            'second_co_borrower_id_file' => 'second_co_borrower_id_file_path',
        ] as $uploadField => $pathField) {
            $identityFields = match ($uploadField) {
                'co_borrower_id_file' => ['co_borrower_name', 'co_borrower_id_type', 'co_borrower_id_number'],
                'second_co_borrower_id_file' => ['second_co_borrower_name', 'second_co_borrower_id_type', 'second_co_borrower_id_number'],
                default => ['full_name', 'id_type', 'id_number'],
            };
            $sameIdentity = $previousDraftData && collect($identityFields)->every(
                fn (string $field) => (string) ($data[$field] ?? '') === (string) ($previousDraftData[$field] ?? '')
            );
            $previousPath = $sameIdentity ? ($previousDraftData[$pathField] ?? null) : null;
            $participantName = match ($uploadField) {
                'co_borrower_id_file' => 'co_borrower_name',
                'second_co_borrower_id_file' => 'second_co_borrower_name',
                default => null,
            };
            if ($request->hasFile($uploadField) && ($participantName === null || ! empty($data[$participantName]))) {
                $data[$pathField] = $request->file($uploadField)->store('loan-ids', 'local');
            } elseif ($previousPath) {
                $data[$pathField] = $previousPath;
            }
            unset($data[$uploadField]);
        }

        $savedDraft ??= new LoanDraft(['user_id' => $request->user()->id]);
        $savedDraft->data = $data;
        $savedDraft->save();
        $request->session()->forget('resume_preview_edit');

        return redirect()->route('loans.create', ['draft' => $savedDraft->id])->with('success', 'Draft saved. You can continue it later before the agreement is signed.');
    }

    public function store(Request $request): RedirectResponse
    {
        $savedDraft = null;
        if ($request->filled('saved_draft_id')) {
            $savedDraft = LoanDraft::where('user_id', $request->user()->id)->findOrFail($request->integer('saved_draft_id'));
        }
        $previousDraftData = $savedDraft?->data;
        if (! $previousDraftData && (bool) $request->session()->get('resume_preview_edit')) {
            $previousDraftData = $request->session()->get('loan_draft', []);
        }
        $input = $request->all();
        unset($input['id_file_path'], $input['co_borrower_id_file_path'], $input['second_co_borrower_id_file_path']);
        foreach ([
            'id_file_path' => ['full_name', 'id_type', 'id_number'],
            'co_borrower_id_file_path' => ['co_borrower_name', 'co_borrower_id_type', 'co_borrower_id_number'],
            'second_co_borrower_id_file_path' => ['second_co_borrower_name', 'second_co_borrower_id_type', 'second_co_borrower_id_number'],
        ] as $pathField => $identityFields) {
            $sameIdentity = $previousDraftData && collect($identityFields)->every(
                fn (string $field) => (string) ($input[$field] ?? '') === (string) ($previousDraftData[$field] ?? '')
            );
            if ($sameIdentity && ! empty($previousDraftData[$pathField])) {
                $input[$pathField] = $previousDraftData[$pathField];
            }
        }
        $data = $this->validateLoanData($input);
        foreach ([
            'id_file' => 'id_file_path',
            'co_borrower_id_file' => 'co_borrower_id_file_path',
            'second_co_borrower_id_file' => 'second_co_borrower_id_file_path',
        ] as $uploadField => $pathField) {
            $participantName = match ($uploadField) {
                'co_borrower_id_file' => 'co_borrower_name',
                'second_co_borrower_id_file' => 'second_co_borrower_name',
                default => null,
            };
            if ($request->hasFile($uploadField) && ($participantName === null || ! empty($data[$participantName]))) {
                $data[$pathField] = $request->file($uploadField)->store('loan-ids', 'local');
            }
            unset($data[$uploadField]);
        }
        $request->session()->put('loan_draft', $data);
        if ($savedDraft) {
            $savedDraft->data = $data;
            $savedDraft->save();
            $request->session()->put('loan_draft_record_id', $savedDraft->id);
        } else {
            $request->session()->forget('loan_draft_record_id');
        }
        $request->session()->forget('resume_preview_edit');

        return redirect()->route('loans.preview');
    }

    public function preview(Request $request): View|RedirectResponse
    {
        $data = $request->session()->get('loan_draft');
        if (! is_array($data)) {
            return redirect()->route('loans.create')->with('error', 'Enter the loan details to generate a disclosure preview.');
        }

        $data = $this->normalizeLoanPurpose($this->validateLoanData($data));
        $loan = $this->makeDraftLoan($data);
        $isPreview = true;

        return view('contracts', compact('loan', 'isPreview'));
    }

    public function savePreviewDraft(Request $request): RedirectResponse
    {
        $data = $request->session()->get('loan_draft');
        if (! is_array($data)) {
            return redirect()->route('loans.create')->with('error', 'The loan preview expired. Please enter the loan details again.');
        }

        $data = $this->validateLoanData($data);
        $draftId = $request->session()->get('loan_draft_record_id');
        $loanDraft = $draftId
            ? LoanDraft::where('user_id', $request->user()->id)->findOrFail($draftId)
            : new LoanDraft(['user_id' => $request->user()->id]);
        $loanDraft->data = $data;
        $loanDraft->save();

        $request->session()->forget(['loan_draft', 'loan_draft_record_id']);

        return redirect()->route('loans.index')->with('success', 'Unsigned loan saved to drafts. Continue it after the agreement has been signed.');
    }

    public function approve(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        $draft = $request->session()->get('loan_draft');
        if (! is_array($draft)) {
            return redirect()->route('loans.create')->with('error', 'The loan draft expired. Please enter the loan details again.');
        }

        $data = $this->normalizeLoanPurpose($this->validateLoanData($draft));
        $values = $this->calculatedLoanValues($data);
        $loan = DB::transaction(function () use ($data, $values) {
            $borrowerQuery = Borrower::query()->where('full_name', $data['full_name']);
            if (! empty($data['email'])) {
                $borrowerQuery->where('email', $data['email']);
            } elseif (! empty($data['phone'])) {
                $borrowerQuery->where('phone', $data['phone']);
            } else {
                $borrowerQuery->whereRaw('1 = 0');
            }

            $borrower = $borrowerQuery->first() ?? new Borrower;
            $borrower->fill([
                'full_name' => $data['full_name'],
                'address' => $data['address'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'date_of_birth' => $data['date_of_birth'],
                'id_type' => $data['id_type'],
                'id_number' => $data['id_number'],
                'id_file_path' => $data['id_file_path'],
            ]);
            $borrower->save();

            $period = now()->format('Y-m');
            $sequence = Loan::where('reference', 'like', $period.'-%')->count() + 1;
            $reference = $period.'-'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);

            return Loan::create([
                'borrower_id' => $borrower->id,
                'reference' => $reference,
                ...$values,
                'term_months' => $data['term'],
                'release_date' => $data['start_date'],
                'purpose' => $data['purpose'],
                'co_borrower_name' => $data['co_borrower_name'] ?? null,
                'co_borrower_address' => $data['co_borrower_address'] ?? null,
                'co_borrower_phone' => $data['co_borrower_phone'] ?? null,
                'co_borrower_email' => $data['co_borrower_email'] ?? null,
                'co_borrower_date_of_birth' => $data['co_borrower_date_of_birth'] ?? null,
                'co_borrower_id_type' => $data['co_borrower_id_type'] ?? null,
                'co_borrower_id_number' => $data['co_borrower_id_number'] ?? null,
                'co_borrower_id_file_path' => $data['co_borrower_id_file_path'] ?? null,
                'second_co_borrower_name' => $data['second_co_borrower_name'] ?? null,
                'second_co_borrower_address' => $data['second_co_borrower_address'] ?? null,
                'second_co_borrower_phone' => $data['second_co_borrower_phone'] ?? null,
                'second_co_borrower_email' => $data['second_co_borrower_email'] ?? null,
                'second_co_borrower_date_of_birth' => $data['second_co_borrower_date_of_birth'] ?? null,
                'second_co_borrower_id_type' => $data['second_co_borrower_id_type'] ?? null,
                'second_co_borrower_id_number' => $data['second_co_borrower_id_number'] ?? null,
                'second_co_borrower_id_file_path' => $data['second_co_borrower_id_file_path'] ?? null,
                'loan_terms' => $data['loan_terms'] ?? null,
            ]);
        });

        $request->session()->forget('loan_draft');
        if ($draftId = $request->session()->pull('loan_draft_record_id')) {
            LoanDraft::where('user_id', $request->user()->id)->whereKey($draftId)->delete();
        }

        return redirect()->route('loans.disclosure', $loan)->with('success', 'Loan approved and saved as an official loan.');
    }

    private function validateLoanData(array $input): array
    {
        foreach (['co_borrower', 'second_co_borrower'] as $prefix) {
            $middleName = trim($input[$prefix.'_middle_name'] ?? '');
            if (strcasecmp($middleName, 'N/A') === 0) {
                $middleName = '';
            }
            $parts = array_filter([
                trim($input[$prefix.'_first_name'] ?? ''),
                $middleName,
                trim($input[$prefix.'_last_name'] ?? ''),
                trim($input[$prefix.'_suffix'] ?? ''),
            ], fn ($part) => $part !== '');
            $input[$prefix.'_name'] = implode(' ', $parts);

            $participantFields = [$prefix.'_first_name', $prefix.'_middle_name', $prefix.'_last_name', $prefix.'_suffix', $prefix.'_address', $prefix.'_phone', $prefix.'_email', $prefix.'_date_of_birth', $prefix.'_id_type', $prefix.'_id_number'];
            if ($prefix === 'second_co_borrower' ? ! empty($input['second_co_borrower_enabled']) : collect($participantFields)->contains(fn ($field) => filled($input[$field] ?? null))) {
                $input[$prefix.'_enabled'] = '1';
            }
        }

        $nameParts = array_filter([
            trim($input['first_name'] ?? ''),
            strcasecmp(trim($input['middle_name'] ?? ''), 'N/A') === 0 ? '' : trim($input['middle_name'] ?? ''),
            trim($input['last_name'] ?? ''),
            trim($input['suffix'] ?? ''),
        ], fn ($part) => $part !== '');
        $input['full_name'] = implode(' ', $nameParts);

        $data = Validator::make($input, [
            'full_name' => ['required', 'string', 'max:150'],
            'first_name' => ['required', 'string', 'max:60', 'regex:/^[\pL]+(?:[ \x{27}\-][\pL]+)*$/u'],
            'middle_name' => ['required', 'string', 'max:60', 'regex:/^(?:N\/A|[\pL]+(?:[ \x{27}\-][\pL]+)*)$/iu'],
            'last_name' => ['required', 'string', 'max:60', 'regex:/^[\pL]+(?:[ \x{27}\-][\pL]+)*$/u'],
            'suffix' => ['nullable', 'in:Jr.,Sr.,II,III,IV,V'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^[0-9]{11}$/'],
            'email' => ['nullable', 'email', 'regex:/@gmail\.com$/i', 'max:150'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'id_type' => ['required', 'in:PhilID,Passport,Driver License,UMID,PRC ID,Postal ID,Voter ID,Other'],
            'id_number' => ['required', 'string', 'max:100'],
            'id_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'id_file_path' => ['nullable', 'string', 'max:255'],
            'co_borrower_name' => ['nullable', 'string', 'max:150'],
            'co_borrower_first_name' => ['required_if:co_borrower_enabled,1', 'string', 'max:60', 'regex:/^[\pL]+(?:[ \x{27}\-][\pL]+)*$/u'],
            'co_borrower_middle_name' => ['required_if:co_borrower_enabled,1', 'string', 'max:60', 'regex:/^(?:N\/A|[\pL]+(?:[ \x{27}\-][\pL]+)*)$/iu'],
            'co_borrower_last_name' => ['required_if:co_borrower_enabled,1', 'string', 'max:60', 'regex:/^[\pL]+(?:[ \x{27}\-][\pL]+)*$/u'],
            'co_borrower_suffix' => ['nullable', 'in:Jr.,Sr.,II,III,IV,V'],
            'co_borrower_enabled' => ['nullable', 'in:1'],
            'co_borrower_address' => ['required_if:co_borrower_enabled,1', 'string', 'max:255'],
            'co_borrower_phone' => ['required_if:co_borrower_enabled,1', 'string', 'max:40'],
            'co_borrower_email' => ['nullable', 'email', 'max:150'],
            'co_borrower_date_of_birth' => ['required_if:co_borrower_enabled,1', 'date', 'before:today'],
            'co_borrower_id_type' => ['required_if:co_borrower_enabled,1', 'in:PhilID,Passport,Driver License,UMID,PRC ID,Postal ID,Voter ID,Other'],
            'co_borrower_id_number' => ['required_if:co_borrower_enabled,1', 'string', 'max:100'],
            'co_borrower_id_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'co_borrower_id_file_path' => ['nullable', 'string', 'max:255'],
            'second_co_borrower_name' => ['nullable', 'string', 'max:150'],
            'second_co_borrower_first_name' => ['required_if:second_co_borrower_enabled,1', 'string', 'max:60', 'regex:/^[\pL]+(?:[ \x{27}\-][\pL]+)*$/u'],
            'second_co_borrower_middle_name' => ['required_if:second_co_borrower_enabled,1', 'string', 'max:60', 'regex:/^(?:N\/A|[\pL]+(?:[ \x{27}\-][\pL]+)*)$/iu'],
            'second_co_borrower_last_name' => ['required_if:second_co_borrower_enabled,1', 'string', 'max:60', 'regex:/^[\pL]+(?:[ \x{27}\-][\pL]+)*$/u'],
            'second_co_borrower_suffix' => ['nullable', 'in:Jr.,Sr.,II,III,IV,V'],
            'second_co_borrower_enabled' => ['nullable', 'in:1'],
            'second_co_borrower_address' => ['required_if:second_co_borrower_enabled,1', 'string', 'max:255'],
            'second_co_borrower_phone' => ['required_if:second_co_borrower_enabled,1', 'string', 'max:40'],
            'second_co_borrower_email' => ['nullable', 'email', 'max:150'],
            'second_co_borrower_date_of_birth' => ['required_if:second_co_borrower_enabled,1', 'date', 'before:today'],
            'second_co_borrower_id_type' => ['required_if:second_co_borrower_enabled,1', 'in:PhilID,Passport,Driver License,UMID,PRC ID,Postal ID,Voter ID,Other'],
            'second_co_borrower_id_number' => ['required_if:second_co_borrower_enabled,1', 'string', 'max:100'],
            'second_co_borrower_id_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'second_co_borrower_id_file_path' => ['nullable', 'string', 'max:255'],
            'loan_terms' => ['nullable', 'string', 'max:3000'],
            'purpose' => ['required', 'in:Agriculture / farming,Business / livelihood,Education,Medical expenses,Home improvement,Emergency,Other'],
            'purpose_other' => ['nullable', 'required_if:purpose,Other', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999'],
            'term' => ['required', 'integer', 'min:1', 'max:120'],
            'monthly_rate' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'calculation_type' => ['required', 'in:flat,diminishing'],
            'start_date' => ['required', 'date'],
            'notarial_fee' => ['nullable', 'numeric', 'min:0'],
            'inspection_fee' => ['nullable', 'numeric', 'min:0'],
            'registration_fee' => ['nullable', 'numeric', 'min:0'],
            'documentary_stamp_fee' => ['nullable', 'numeric', 'min:0'],
            'service_fee' => ['nullable', 'numeric', 'min:0'],
        ])->validate();

        foreach ([
            'id_file' => ['id_file_path', 'ID file'],
            'co_borrower_id_file' => ['co_borrower_id_file_path', 'Co-borrower ID file'],
            'second_co_borrower_id_file' => ['second_co_borrower_id_file_path', 'Second co-borrower ID file'],
        ] as $fileField => [$pathField, $label]) {
            $participantName = match ($fileField) {
                'co_borrower_id_file' => 'co_borrower_name',
                'second_co_borrower_id_file' => 'second_co_borrower_name',
                default => null,
            };
            $requiresFile = $participantName === null || filled($data[$participantName] ?? null);
            if ($requiresFile && empty($data[$fileField]) && empty($data[$pathField])) {
                throw ValidationException::withMessages([$fileField => $label.' is required.']);
            }
        }

        return $data;
    }

    private function normalizeLoanPurpose(array $data): array
    {
        if ($data['purpose'] === 'Other') {
            $data['purpose'] = $data['purpose_other'];
        }

        return $data;
    }

    private function calculatedLoanValues(array $data): array
    {
        $principal = round((float) $data['amount'], 2);
        $fees = [
            'notarial_fee' => round((float) ($data['notarial_fee'] ?? 0), 2),
            'inspection_fee' => round((float) ($data['inspection_fee'] ?? 0), 2),
            'registration_fee' => round((float) ($data['registration_fee'] ?? 0), 2),
            'documentary_stamp_fee' => round((float) ($data['documentary_stamp_fee'] ?? 0), 2),
            'service_fee' => round((float) ($data['service_fee'] ?? 0), 2),
        ];
        $totalCharges = round(array_sum($fees), 2);
        if ($totalCharges > $principal) {
            throw ValidationException::withMessages(['service_fee' => 'Total deductions cannot exceed the principal loan amount.']);
        }

        $monthlyRate = (float) $data['monthly_rate'];
        $calculation = $this->calculator->schedule($principal, (int) $data['term'], $monthlyRate, $data['start_date'], $data['calculation_type']);

        return [
            'principal' => $principal,
            'monthly_rate' => $monthlyRate,
            'calculation_type' => $data['calculation_type'],
            ...$fees,
            'additional_charges' => [],
            'total_charges' => $totalCharges,
            'net_proceeds' => round($principal - $totalCharges, 2),
            'total_interest' => $calculation['total_interest'],
            'total_payments' => $calculation['total_payments'],
            'schedule' => $calculation['schedule'],
        ];
    }

    private function makeDraftLoan(array $data): Loan
    {
        $borrower = new Borrower([
            'full_name' => $data['full_name'],
            'address' => $data['address'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'date_of_birth' => $data['date_of_birth'],
            'id_type' => $data['id_type'],
            'id_number' => $data['id_number'],
            'id_file_path' => $data['id_file_path'] ?? null,
        ]);
        $loan = new Loan([
            ...$this->calculatedLoanValues($data),
            'term_months' => $data['term'],
            'release_date' => $data['start_date'],
            'purpose' => $data['purpose'],
            'co_borrower_name' => $data['co_borrower_name'] ?? null,
            'co_borrower_address' => $data['co_borrower_address'] ?? null,
            'co_borrower_phone' => $data['co_borrower_phone'] ?? null,
            'co_borrower_email' => $data['co_borrower_email'] ?? null,
            'co_borrower_date_of_birth' => $data['co_borrower_date_of_birth'] ?? null,
            'co_borrower_id_type' => $data['co_borrower_id_type'] ?? null,
            'co_borrower_id_number' => $data['co_borrower_id_number'] ?? null,
            'co_borrower_id_file_path' => $data['co_borrower_id_file_path'] ?? null,
            'second_co_borrower_name' => $data['second_co_borrower_name'] ?? null,
            'second_co_borrower_address' => $data['second_co_borrower_address'] ?? null,
            'second_co_borrower_phone' => $data['second_co_borrower_phone'] ?? null,
            'second_co_borrower_email' => $data['second_co_borrower_email'] ?? null,
            'second_co_borrower_date_of_birth' => $data['second_co_borrower_date_of_birth'] ?? null,
            'second_co_borrower_id_type' => $data['second_co_borrower_id_type'] ?? null,
            'second_co_borrower_id_number' => $data['second_co_borrower_id_number'] ?? null,
            'second_co_borrower_id_file_path' => $data['second_co_borrower_id_file_path'] ?? null,
            'loan_terms' => $data['loan_terms'] ?? null,
            'reference' => 'DRAFT - NOT APPROVED',
        ]);
        $loan->setRelation('borrower', $borrower);

        return $loan;
    }

    public function disclosure(Request $request, Loan $loan): View
    {
        $loan->load('borrower');
        $returnTo = match ($request->query('return')) {
            'loans' => route('loans.index'),
            'dashboard' => route('dashboard'),
            default => route('borrowers.show', $loan->borrower),
        };

        return view('contracts', compact('loan', 'returnTo'));
    }

    public function contract(Loan $loan): View
    {
        $loan->load('borrower');

        return view('loan-contract', compact('loan'));
    }

    public function promissoryNote(Loan $loan): View
    {
        $loan->load('borrower');

        return view('promissory-note', compact('loan'));
    }

    public function downloadBorrowerId(Borrower $borrower): StreamedResponse
    {
        abort_unless($borrower->id_file_path && Storage::disk('local')->exists($borrower->id_file_path), 404);

        return Storage::disk('local')->response($borrower->id_file_path, null, [], 'inline');
    }

    public function downloadLoanParticipantId(Loan $loan, string $participant): StreamedResponse
    {
        $path = match ($participant) {
            'co-borrower' => $loan->co_borrower_id_file_path,
            'second-co-borrower' => $loan->second_co_borrower_id_file_path,
            default => null,
        };
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, [], 'inline');
    }

    public function contracts(): View
    {
        return view('contracts.index', [
            'loans' => Loan::with('borrower')->latest()->get(),
        ]);
    }

    public function payments(Request $request): View
    {
        $loans = Loan::with('borrower')->latest()->get();
        $loan = $request->filled('loan_id')
            ? $loans->firstWhere('id', (int) $request->query('loan_id'))
            : null;
        $payments = $loan ? $loan->payments()->latest('paid_at')->get() : collect();
        $accounting = $loan ? $this->accounting->summary($loan) : null;
        $returned = $accounting['returned'] ?? 0.0;

        return view('payment', compact('loan', 'loans', 'payments', 'accounting', 'returned'));
    }

    public function recordPayment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'loan_id' => ['required', 'integer', 'exists:loans,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['required', 'date', 'before_or_equal:today'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);
        $payment = DB::transaction(function () use ($data) {
            $loan = Loan::whereKey($data['loan_id'])->lockForUpdate()->firstOrFail();
            $remaining = $this->accounting->summary($loan)['remaining_due'];
            if (round((float) $data['amount'], 2) > $remaining) {
                throw ValidationException::withMessages(['amount' => 'Payment cannot be greater than the remaining loan balance (₱'.number_format(max(0, $remaining), 2).').']);
            }

            return $loan->payments()->create($data);
        });

        $payment->load('loan.borrower');
        $borrower = $payment->loan->borrower;
        $message = 'Payment recorded.';

        if (! $borrower?->email) {
            $message .= ' No receipt was sent because the borrower has no email address on file.';
        } elseif (config('mail.default') === 'log') {
            $message .= ' No receipt was sent because email delivery is not configured.';
        } else {
            try {
                Mail::to($borrower->email)->send(new PaymentReceiptMail(
                    borrowerName: $borrower->full_name,
                    loanReference: $payment->loan->reference,
                    amount: number_format((float) $payment->amount, 2),
                    paymentDate: $payment->paid_at->format('F j, Y'),
                ));
                $message .= ' A receipt was emailed to the borrower.';
            } catch (\Throwable $exception) {
                report($exception);
                $message .= ' The payment was saved, but its email receipt could not be sent.';
            }
        }

        return redirect()->route('payments.index', ['loan_id' => $payment->loan_id])->with('success', $message);
    }

    public function borrowers(): View
    {
        $borrowers = Borrower::withCount('loans')->with(['loans' => fn ($query) => $query->latest()])->orderBy('full_name')->get();

        return view('borrowers', compact('borrowers'));
    }

    public function borrower(Borrower $borrower): View
    {
        $borrower->load(['loans' => fn ($query) => $query->with('payments')->latest()]);
        $installmentStatuses = [];

        $loanSummaries = [];
        foreach ($borrower->loans as $loan) {
            $loanSummaries[$loan->id] = $this->accounting->summary($loan);
            $installmentStatuses[$loan->id] = $loanSummaries[$loan->id]['installments'];
        }

        return view('barrower_details', compact('borrower', 'installmentStatuses', 'loanSummaries'));
    }
}
