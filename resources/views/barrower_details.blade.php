@extends('websnippet')
@section('title', 'Borrower Information')

@section('content')
<style>
    .borrower-profile-heading {
        display: grid;
        grid-template-columns: 64px minmax(0, 1fr);
        align-items: center;
        gap: 16px;
        text-align: left;
    }

    .borrower-contact-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px 24px;
        margin-top: 16px;
    }

    .borrower-contact-grid dd {
        overflow-wrap: anywhere;
    }

    .loan-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0 32px;
    }

    .loan-detail-row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 16px;
        border-bottom: 1px solid #eef3ef;
        padding: 11px 0;
    }

    @media (max-width: 640px) {
        .borrower-contact-grid,
        .loan-detail-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }
</style>

<div class="mx-auto max-w-6xl">
    <a href="{{ route('borrowers.index') }}" class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-[#17744a] hover:underline">
        <x-icon name="back" class="h-4 w-4" /> Back to Borrowers
    </a>

    <section class="mb-6 rounded-xl border border-[#e3ebe5] bg-white p-4 sm:p-5">
        <div class="borrower-profile-heading">
            <span class="grid h-16 w-16 place-items-center rounded-full bg-[#e3f5ea] text-xl font-semibold text-[#17744a]">
                {{ \Illuminate\Support\Str::initials($borrower->full_name) }}
            </span>
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wide text-[#7a9186]">Borrower information</p>
                <h1 class="mt-1 break-words text-xl font-semibold text-[#0f3d2b]">{{ $borrower->full_name }}</h1>
                <dl class="borrower-contact-grid text-sm">
                    <div>
                        <dt class="text-xs font-medium text-[#7a9186]">Phone</dt>
                        <dd class="mt-0.5 font-medium text-[#0f3d2b]">{{ $borrower->phone ?: 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-[#7a9186]">Email</dt>
                        <dd class="mt-0.5 font-medium text-[#0f3d2b]">{{ $borrower->email ?: 'Not provided' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
        <dl class="mt-4 border-t border-[#eef3ef] pt-3">
            <dt class="text-xs font-medium text-[#7a9186]">Complete address</dt>
            <dd class="mt-0.5 break-words text-sm font-medium leading-5 text-[#0f3d2b]">{{ $borrower->address }}</dd>
        </dl>
        <dl class="borrower-contact-grid border-t border-[#eef3ef] pt-3 text-sm">
            <div><dt class="text-xs font-medium text-[#7a9186]">Date of birth</dt><dd class="mt-0.5 font-medium text-[#0f3d2b]">{{ $borrower->date_of_birth?->format('M j, Y') ?: 'Not provided' }}</dd></div>
            <div><dt class="text-xs font-medium text-[#7a9186]">Valid ID</dt><dd class="mt-0.5 font-medium text-[#0f3d2b]">{{ $borrower->id_type ?: 'Not provided' }} · {{ $borrower->id_number ?: 'Not provided' }} @if($borrower->id_file_path)<a href="{{ route('borrowers.identity', $borrower) }}" target="_blank" rel="noopener" class="ml-2 text-[#17744a] underline">View ID copy</a>@endif</dd></div>
        </dl>
    </section>

    <div class="mb-3 flex items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-[#0f3d2b]">Loan history</h2>
            <p class="mt-1 text-sm text-[#5b7567]">Loan details and disclosure documents for this borrower.</p>
        </div>
        <span class="rounded-full bg-[#e7f3eb] px-3 py-1 text-xs font-medium text-[#17744a]">{{ $borrower->loans->count() }} {{ \Illuminate\Support\Str::plural('loan', $borrower->loans->count()) }}</span>
    </div>

    @forelse ($borrower->loans as $loan)
        <section class="mb-4 overflow-hidden rounded-xl border border-[#e3ebe5] bg-white">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#eef3ef] px-4 py-3 sm:px-5">
                <div class="flex items-center gap-2.5">
                    <h3 class="font-semibold text-[#0f3d2b]">Loan {{ $loan->reference }}</h3>
                    <x-status-badge :status="$loan->status" />
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('loans.disclosure', $loan) }}" class="rounded-md border border-[#17744a]/40 px-3.5 py-2 text-sm font-medium text-[#17744a] hover:bg-[#e3f5ea]">View disclosure</a>
                </div>
            </div>

            <dl class="loan-detail-grid px-4 text-sm sm:px-5">
                @foreach ([
                    'Principal' => '₱'.number_format((float) $loan->principal, 2),
                    'Interest rate' => number_format((float) $loan->monthly_rate, 2).'% per month',
                    'Term' => $loan->term_months.' months',
                    'Release date' => $loan->release_date->format('M j, Y'),
                    'Total deductions' => '₱'.number_format((float) $loan->total_charges, 2),
                    'Net proceeds' => '₱'.number_format((float) $loan->net_proceeds, 2),
                    'Total interest' => '₱'.number_format((float) $loan->total_interest, 2),
                    'Total payments' => '₱'.number_format((float) $loan->total_payments, 2),
                    'Late penalties accrued' => '₱'.number_format($loanSummaries[$loan->id]['late_penalty'], 2),
                    'Total due including penalties' => '₱'.number_format($loanSummaries[$loan->id]['total_due'], 2),
                ] as $label => $value)
                    <div class="loan-detail-row">
                        <dt class="text-[#5b7567]">{{ $label }}</dt>
                        <dd class="text-right font-medium text-[#0f3d2b]">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="border-t border-[#eef3ef] px-4 py-4 sm:px-5">
                <div class="mb-3"><h4 class="font-semibold text-[#0f3d2b]">Monthly installment status</h4><p class="mt-1 text-xs text-[#7a9186]">Recorded repayments are applied to the oldest installments first. A 1.25% monthly penalty on principal applies after an installment is at least 5 days late.</p></div>
                <div class="overflow-x-auto rounded-lg border border-[#e3ebe5]"><table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="bg-[#f6f9f6] text-xs text-[#5b7567]"><tr><th class="px-4 py-3">Month</th><th class="px-4 py-3">Due date</th><th class="px-4 py-3 text-right">Installment</th><th class="px-4 py-3 text-right">Paid</th><th class="px-4 py-3 text-right">Remaining</th><th class="px-4 py-3 text-right">Penalty</th><th class="px-4 py-3">Status</th></tr></thead>
                    <tbody>
                        @foreach ($installmentStatuses[$loan->id] as $installment)
                            <tr class="border-t border-[#eef3ef]"><td class="px-4 py-3">Month {{ $installment['month'] }}</td><td class="px-4 py-3">{{ \Illuminate\Support\Carbon::parse($installment['due_date'])->format('M j, Y') }}</td><td class="px-4 py-3 text-right">₱{{ number_format((float) $installment['payment'], 2) }}</td><td class="px-4 py-3 text-right">₱{{ number_format($installment['paid_amount'], 2) }}</td><td class="px-4 py-3 text-right">₱{{ number_format($installment['remaining_due'], 2) }}</td><td class="px-4 py-3 text-right">₱{{ number_format($installment['late_penalty'], 2) }}</td><td class="px-4 py-3"><x-status-badge :status="$installment['payment_status']" /></td></tr>
                        @endforeach
                    </tbody>
                </table></div>
            </div>
        </section>
    @empty
        <p class="rounded-xl border border-[#e3ebe5] bg-white p-5 text-sm text-[#5b7567]">No loan records for this borrower yet.</p>
    @endforelse
</div>
@endsection
