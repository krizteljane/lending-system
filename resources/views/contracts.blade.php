@extends('websnippet')
@section('title', 'Loan Disclosure')

@section('content')
@php($isPreview = $isPreview ?? false)
<style>
    @page { size: A4 portrait; margin: 11mm; }
    .disclosure-masthead { display: flex; align-items: center; gap: 16px; }
    .disclosure-masthead img { width: 68px; height: 68px; object-fit: contain; }
    .disclosure-brand { color: #0f5b40; font-size: 15px; font-weight: 700; letter-spacing: .04em; }
    .disclosure-tagline { color: #63786c; font-size: 11px; }
    .disclosure-title { margin-top: 14px; border-top: 3px solid #17744a; padding-top: 10px; text-align: center; }
    .disclosure-title h3 { color: #0f4a33; font-size: 18px; font-weight: 700; letter-spacing: .04em; }
    .disclosure-party-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-top: 14px; }
    .disclosure-party { min-height: 100px; border: 1px solid #dbe5de; border-radius: 8px; padding: 10px 12px; }
    .disclosure-party-label { color: #17744a; font-size: 9px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
    .disclosure-summary-cards { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; margin: 12px 0; }
    .disclosure-summary-card { border: 1px solid #dbe5de; border-radius: 7px; padding: 8px; }
    .disclosure-summary-label { color: #63786c; font-size: 9px; text-transform: uppercase; }
    .disclosure-summary-value { color: #0f4a33; font-size: 13px; font-weight: 700; }
    .disclosure-detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 4px 16px; margin: 12px 0; }
    @media screen and (max-width: 640px) {
        .disclosure-party-grid,
        .disclosure-detail-grid { grid-template-columns: minmax(0, 1fr); }
        .disclosure-summary-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .signature-block { grid-template-columns: minmax(0, 1fr); gap: 24px; }
    }
    @media print {
        html, body { background: #fff !important; }
        body { color: #111 !important; font-size: 9pt !important; }
        #sidebar, #backdrop, header.sticky, #adminContextMenu, .disclosure-page-heading { display: none !important; }
        .lg\:pl-60 { padding-left: 0 !important; }
        main { padding: 0 !important; }
        .disclosure-page { width: 100% !important; max-width: none !important; margin: 0 !important; }
        .disclosure-sheet { width: 100% !important; max-width: none !important; margin: 0 !important; padding: 0 !important; border: 0 !important; border-radius: 0 !important; box-shadow: none !important; font-size: 9pt !important; line-height: 1.28 !important; }
        .disclosure-sheet header { padding-bottom: 8px !important; }
        .disclosure-masthead { gap: 8px !important; }
        .disclosure-masthead img { width: 44px !important; height: 44px !important; }
        .disclosure-brand { font-size: 11pt !important; }
        .disclosure-tagline { font-size: 7pt !important; }
        .disclosure-title { margin-top: 7px !important; padding-top: 5px !important; }
        .disclosure-title h3 { font-size: 12pt !important; }
        .disclosure-party-grid { gap: 6px !important; margin-top: 8px !important; }
        .disclosure-party { min-height: 0 !important; padding: 5px 7px !important; }
        .disclosure-summary-cards { gap: 5px !important; margin: 7px 0 !important; }
        .disclosure-summary-card { padding: 5px !important; }
        .disclosure-summary-value { font-size: 9pt !important; }
        .disclosure-detail-grid { gap: 2px 12px !important; margin: 8px 0 !important; }
        .disclosure-sheet p { margin-top: 3px !important; margin-bottom: 3px !important; line-height: 1.25 !important; }
        .disclosure-sheet .disclosure-summary th, .disclosure-sheet .disclosure-summary td { padding: 2px 4px !important; line-height: 1.15 !important; }
        .disclosure-sheet h3 { margin-top: 8px !important; margin-bottom: 3px !important; }
        .disclosure-sheet table { width: 100% !important; min-width: 0 !important; font-size: 7pt !important; }
        .disclosure-sheet .overflow-x-auto { overflow: visible !important; }
        .disclosure-sheet .schedule-table th, .disclosure-sheet .schedule-table td { padding: 2px 4px !important; line-height: 1.15 !important; }
        .disclosure-sheet .schedule-continuation { break-before: page; page-break-before: always; }
        .disclosure-sheet .late-charge-note { margin-top: 5px !important; }
        .disclosure-sheet .signature-block { margin-top: 18px !important; gap: 32px !important; }
        .disclosure-sheet tr { break-inside: avoid; page-break-inside: avoid; }
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    }
</style>
<div class="disclosure-page mx-auto max-w-5xl">
    <div class="disclosure-page-heading mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            @if ($loan && !$isPreview)
                <a href="{{ $returnTo ?? route('borrowers.show', $loan->borrower) }}" class="mb-2 inline-flex items-center gap-1.5 text-sm font-medium text-[#17744a] hover:underline print:hidden"><x-icon name="back" class="h-4 w-4" /> Back</a>
            @elseif ($isPreview)
                <a href="{{ route('loans.create', session('loan_draft_record_id') ? ['draft' => session('loan_draft_record_id')] : ['resume_preview' => 1]) }}" class="mb-2 inline-flex items-center gap-1.5 text-sm font-medium text-[#17744a] hover:underline print:hidden"><x-icon name="back" class="h-4 w-4" /> Back to loan information</a>
            @endif
            <h1 class="text-2xl font-semibold">{{ $isPreview ? 'Loan Disclosure Preview' : 'Loan Disclosure' }}</h1>
            <p class="mt-1 text-sm text-[#5b7567]">{{ $isPreview ? 'Review the disclosure. This draft has not been saved as an official loan.' : 'Generated from the saved borrower and loan details.' }}</p>
        </div>
        @if ($loan)<button type="button" class="password-confirm-trigger inline-flex items-center gap-2 rounded-md border border-[#17744a] px-4 py-2 text-sm text-[#17744a] hover:bg-[#e3f5ea] print:hidden" data-confirm-action="print"><x-icon name="print" class="h-4 w-4" /> Print</button>@endif
    </div>
    @if (!$loan)
        <div class="rounded-xl border border-[#e3ebe5] bg-white p-8 text-center"><p class="text-[#5b7567]">No saved loans yet.</p><a href="{{ route('loans.create') }}" class="mt-4 inline-flex rounded-md bg-[#17744a] px-4 py-2 text-sm text-white">Create a loan</a></div>
    @else
        @if ($isPreview)
            <div class="mb-5 rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 print:hidden"><strong>Draft preview only.</strong> No borrower or loan record has been saved yet. Review the details and enter your password below to approve and save this loan.</div>
        @endif
        @if (session('success'))<div class="mb-5 rounded-lg bg-green-50 p-3 text-sm text-green-800">{{ session('success') }} · <a class="font-semibold underline" href="{{ route('borrowers.index') }}">View borrower list</a></div>@endif
        @php($firstScheduleRows = array_slice($loan->schedule, 0, 12))
        @php($remainingScheduleRows = array_slice($loan->schedule, 12))
        <article class="disclosure-sheet rounded-xl border border-[#d4ddd6] bg-white p-5 text-sm text-gray-900 shadow-sm sm:p-8 print:border-0 print:shadow-none">
            <header class="border-b border-gray-300 pb-4">
                <div class="disclosure-masthead"><img src="{{ asset('images/logo.jpg') }}" alt="SALUAG logo"><div><p class="disclosure-brand">SALUAG</p><h2 class="text-sm font-semibold">San Luis Agri-Ventures Agriculture Cooperative</h2><p class="disclosure-tagline">Purok 1, Banoyo, San Luis, Batangas</p></div></div>
                <div class="disclosure-title"><h3>DISCLOSURE STATEMENT OF LOAN / CREDIT TRANSACTION</h3><p class="mt-1 text-xs text-gray-600">As required under R.A. 3765, Truth in Lending Act (Appendix to Subsec. 4305 Q.7)</p><p class="text-xs">Loan reference: <strong>{{ $isPreview ? 'Assigned after approval' : $loan->reference }}</strong></p></div>
            </header>
            <p class="mt-4">Prepared on <strong>{{ $loan->release_date->format('F j, Y') }}</strong> for the loan parties listed below.</p>
            <div class="disclosure-party-grid">
                <div class="disclosure-party"><p class="disclosure-party-label">Borrower</p><p class="mt-1 font-semibold">{{ $loan->borrower->full_name }}</p><p class="text-xs">{{ $loan->borrower->address }}</p><p class="text-xs">Phone: {{ $loan->borrower->phone ?: '—' }} · Email: {{ $loan->borrower->email ?: '—' }}</p><p class="text-xs">Date of birth: {{ $loan->borrower->date_of_birth?->format('M j, Y') ?: '—' }}</p><p class="text-xs">ID: {{ $loan->borrower->id_type ?: '—' }} · {{ $loan->borrower->id_number ?: '—' }}{{ $loan->borrower->id_file_path ? ' (copy on file)' : '' }}</p></div>
                <div class="disclosure-party"><p class="disclosure-party-label">Lender</p><p class="mt-1 font-semibold">San Luis Agri-Ventures Agriculture Cooperative</p><p class="text-xs">Purok 1, Banoyo, San Luis, Batangas</p></div>
            </div>
            @if ($loan->co_borrower_name || $loan->second_co_borrower_name)
                <div class="disclosure-party-grid mt-2">
                    @if ($loan->co_borrower_name)<div class="disclosure-party"><p class="disclosure-party-label">Co-borrower</p><p class="mt-1 font-semibold">{{ $loan->co_borrower_name }}</p><p class="text-xs">{{ $loan->co_borrower_address }}</p><p class="text-xs">Phone: {{ $loan->co_borrower_phone ?: '—' }} · Email: {{ $loan->co_borrower_email ?: '—' }}</p><p class="text-xs">Date of birth: {{ $loan->co_borrower_date_of_birth?->format('M j, Y') ?: '—' }}</p><p class="text-xs">ID: {{ $loan->co_borrower_id_type ?: '—' }} · {{ $loan->co_borrower_id_number ?: '—' }}{{ $loan->co_borrower_id_file_path ? ' (copy on file)' : '' }}</p></div>@endif
                    @if ($loan->second_co_borrower_name)<div class="disclosure-party"><p class="disclosure-party-label">Second co-borrower</p><p class="mt-1 font-semibold">{{ $loan->second_co_borrower_name }}</p><p class="text-xs">{{ $loan->second_co_borrower_address }}</p><p class="text-xs">Phone: {{ $loan->second_co_borrower_phone ?: '—' }} · Email: {{ $loan->second_co_borrower_email ?: '—' }}</p><p class="text-xs">Date of birth: {{ $loan->second_co_borrower_date_of_birth?->format('M j, Y') ?: '—' }}</p><p class="text-xs">ID: {{ $loan->second_co_borrower_id_type ?: '—' }} · {{ $loan->second_co_borrower_id_number ?: '—' }}{{ $loan->second_co_borrower_id_file_path ? ' (copy on file)' : '' }}</p></div>@endif
                </div>
            @endif
            <div class="disclosure-summary-cards">
                <div class="disclosure-summary-card"><p class="disclosure-summary-label">Principal</p><p class="disclosure-summary-value">₱{{ number_format((float) $loan->principal, 2) }}</p></div>
                <div class="disclosure-summary-card"><p class="disclosure-summary-label">Monthly interest</p><p class="disclosure-summary-value">{{ number_format((float) $loan->monthly_rate, 2) }}%</p></div>
                <div class="disclosure-summary-card"><p class="disclosure-summary-label">Term</p><p class="disclosure-summary-value">{{ $loan->term_months }} months</p></div>
            </div>
            <dl class="disclosure-detail-grid">
                <div><dt class="inline font-medium">{{ $isPreview ? 'Proposed release date:' : 'Date granted:' }}</dt> <dd class="inline">{{ $loan->release_date->format('M j, Y') }}</dd></div>
                <div><dt class="inline font-medium">PN / Loan reference:</dt> <dd class="inline">{{ $isPreview ? 'Assigned after approval' : $loan->reference }}</dd></div>
                <div><dt class="inline font-medium">Due date:</dt> <dd class="inline">{{ \Illuminate\Support\Carbon::parse($loan->schedule[array_key_last($loan->schedule)]['due_date'])->format('M j, Y') }}</dd></div>
                <div><dt class="inline font-medium">Specific purpose:</dt> <dd class="inline">{{ $loan->purpose ?: '—' }}</dd></div>
            </dl>
            <div class="overflow-x-auto"><table class="disclosure-summary w-full min-w-[560px] border-collapse"><tbody>
                <tr class="border-t border-gray-300"><th class="py-2 text-left font-medium">Principal loan amount</th><td class="py-2 text-right">₱{{ number_format((float) $loan->principal, 2) }}</td></tr>
                <tr class="border-t border-gray-300"><th class="py-2 text-left font-medium">Notarial fee</th><td class="py-2 text-right">₱{{ number_format((float) $loan->notarial_fee, 2) }}</td></tr>
                <tr class="border-t border-gray-300"><th class="py-2 text-left font-medium">Inspection fee</th><td class="py-2 text-right">₱{{ number_format((float) $loan->inspection_fee, 2) }}</td></tr>
                <tr class="border-t border-gray-300"><th class="py-2 text-left font-medium">Registration fees</th><td class="py-2 text-right">₱{{ number_format((float) $loan->registration_fee, 2) }}</td></tr>
                <tr class="border-t border-gray-300"><th class="py-2 text-left font-medium">Documentary / science stamps</th><td class="py-2 text-right">₱{{ number_format((float) $loan->documentary_stamp_fee, 2) }}</td></tr>
                <tr class="border-t border-gray-300"><th class="py-2 text-left font-medium">Service fee</th><td class="py-2 text-right">₱{{ number_format((float) $loan->service_fee, 2) }}</td></tr>
                @foreach (($loan->additional_charges ?? []) as $charge)
                    <tr class="border-t border-gray-300"><th class="py-2 text-left font-medium">{{ $charge['description'] }}</th><td class="py-2 text-right">₱{{ number_format((float) $charge['amount'], 2) }}</td></tr>
                @endforeach
                <tr class="border-t border-gray-300 font-medium"><th class="py-2 text-left">Total charges / deductions</th><td class="py-2 text-right">₱{{ number_format((float) $loan->total_charges, 2) }}</td></tr>
                <tr class="border-t border-gray-300 font-semibold"><th class="py-2 text-left">Net proceeds</th><td class="py-2 text-right">₱{{ number_format((float) $loan->net_proceeds, 2) }}</td></tr>
                <tr class="border-t border-gray-300"><th class="py-2 text-left font-medium">Repayment method</th><td class="py-2 text-right">Equal principal installments; {{ $loan->calculation_type === 'flat' ? 'interest calculated on the original principal' : 'interest calculated on the remaining balance' }}</td></tr>
                <tr class="border-t border-gray-300"><th class="py-2 text-left font-medium">Interest rate</th><td class="py-2 text-right">{{ number_format((float) $loan->monthly_rate, 2) }}% monthly, {{ $loan->calculation_type }}</td></tr>
                <tr class="border-t border-gray-300"><th class="py-2 text-left font-medium">Late payment charge</th><td class="py-2 text-right">1.25% of original principal per late month, after a 5-day grace period</td></tr>
                <tr class="border-y border-gray-300 font-semibold"><th class="py-2 text-left">Total installment payments</th><td class="py-2 text-right">₱{{ number_format((float) $loan->total_payments, 2) }}</td></tr>
            </tbody></table></div>

            <h3 class="mb-2 mt-6 font-bold">Schedule of Payment</h3>
            <div class="overflow-x-auto"><table class="schedule-table w-full min-w-[680px] border-collapse text-xs sm:text-sm">
                <thead><tr class="border-y-2 border-black text-left"><th class="px-2 py-2">Month</th><th class="px-2 py-2">Check date</th><th class="px-2 py-2 text-right">Monthly amortization</th><th class="px-2 py-2 text-right">Interest</th><th class="px-2 py-2 text-right">Principal</th><th class="px-2 py-2 text-right">Remaining balance</th></tr></thead>
                <tbody>
                    @foreach ($firstScheduleRows as $row)
                        <tr class="border-b border-gray-400"><td class="px-2 py-2">{{ $row['month'] }}</td><td class="px-2 py-2">{{ \Illuminate\Support\Carbon::parse($row['due_date'])->format('M j, Y') }}</td><td class="px-2 py-2 text-right">₱{{ number_format($row['payment'], 2) }}</td><td class="px-2 py-2 text-right">₱{{ number_format($row['interest'], 2) }}</td><td class="px-2 py-2 text-right">₱{{ number_format($row['principal'], 2) }}</td><td class="px-2 py-2 text-right">₱{{ number_format($row['balance'], 2) }}</td></tr>
                    @endforeach
                    @if (empty($remainingScheduleRows))
                        <tr class="border-b-2 border-black font-bold"><td class="px-2 py-2" colspan="2">TOTAL</td><td class="px-2 py-2 text-right">₱{{ number_format((float) $loan->total_payments, 2) }}</td><td class="px-2 py-2 text-right">₱{{ number_format((float) $loan->total_interest, 2) }}</td><td class="px-2 py-2 text-right">₱{{ number_format((float) $loan->principal, 2) }}</td><td class="px-2 py-2 text-right">₱0.00</td></tr>
                    @endif
                </tbody>
            </table></div>
            @if (!empty($remainingScheduleRows))
                <div class="schedule-continuation">
                    <h3 class="mb-2 mt-6 font-bold">Schedule of Payment (continued)</h3>
                    <div class="overflow-x-auto"><table class="schedule-table w-full min-w-[680px] border-collapse text-xs sm:text-sm"><thead><tr class="border-y-2 border-black text-left"><th class="px-2 py-2">Month</th><th class="px-2 py-2">Check date</th><th class="px-2 py-2 text-right">Monthly amortization</th><th class="px-2 py-2 text-right">Interest</th><th class="px-2 py-2 text-right">Principal</th><th class="px-2 py-2 text-right">Remaining balance</th></tr></thead><tbody>
                        @foreach ($remainingScheduleRows as $row)
                            <tr class="border-b border-gray-400"><td class="px-2 py-2">{{ $row['month'] }}</td><td class="px-2 py-2">{{ \Illuminate\Support\Carbon::parse($row['due_date'])->format('M j, Y') }}</td><td class="px-2 py-2 text-right">₱{{ number_format($row['payment'], 2) }}</td><td class="px-2 py-2 text-right">₱{{ number_format($row['interest'], 2) }}</td><td class="px-2 py-2 text-right">₱{{ number_format($row['principal'], 2) }}</td><td class="px-2 py-2 text-right">₱{{ number_format($row['balance'], 2) }}</td></tr>
                        @endforeach
                        <tr class="border-b-2 border-black font-bold"><td class="px-2 py-2" colspan="2">TOTAL</td><td class="px-2 py-2 text-right">₱{{ number_format((float) $loan->total_payments, 2) }}</td><td class="px-2 py-2 text-right">₱{{ number_format((float) $loan->total_interest, 2) }}</td><td class="px-2 py-2 text-right">₱{{ number_format((float) $loan->principal, 2) }}</td><td class="px-2 py-2 text-right">₱0.00</td></tr>
                    </tbody></table></div>
                </div>
            @endif
            <p class="late-charge-note mt-3 text-xs text-gray-600">A late charge applies to each unpaid installment after it is 5 days overdue. The charge is 1.25% of original principal for the first late month and each additional 30-day period it remains unpaid.</p>
            <div class="signature-block mt-12 grid grid-cols-1 gap-x-10 gap-y-8 sm:grid-cols-2">
                <div class="border-t border-gray-500 pt-2 text-center"><p class="font-medium">San Luis Agri-Ventures Agriculture Cooperative</p><p class="text-xs italic">Released by</p><p class="mt-3 text-left text-xs">Date: ____________________</p></div>
                <div class="border-t border-gray-500 pt-2 text-center"><p class="font-medium">{{ $loan->borrower->full_name }}</p><p class="text-xs italic">Borrower signature</p><p class="mt-3 text-left text-xs">Date: ____________________</p></div>
                @if ($loan->co_borrower_name)<div class="border-t border-gray-500 pt-2 text-center"><p class="font-medium">{{ $loan->co_borrower_name }}</p><p class="text-xs italic">Co-borrower signature</p><p class="mt-3 text-left text-xs">Date: ____________________</p></div>@endif
                @if ($loan->second_co_borrower_name)<div class="border-t border-gray-500 pt-2 text-center"><p class="font-medium">{{ $loan->second_co_borrower_name }}</p><p class="text-xs italic">Second co-borrower signature</p><p class="mt-3 text-left text-xs">Date: ____________________</p></div>@endif
            </div>
            <div class="mt-8 max-w-sm">
                <p class="mb-1 text-right text-xs font-semibold">Witness</p>
                <div class="border-b border-gray-500"></div>
                <p class="mt-2 text-xs">Signature and date: ____________________</p>
            </div>
        </article>
        @if ($isPreview)
            <form method="POST" action="{{ route('loans.preview.draft') }}" class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-6 print:hidden">
                @csrf
                <h2 class="font-semibold text-amber-900">Agreement not signed yet?</h2>
                <p class="mt-1 text-sm text-amber-800">Save this application as a draft and return after the borrower and co-borrowers have signed the printed agreement.</p>
                <button type="submit" class="mt-4 rounded-md border border-amber-700 px-5 py-2.5 text-sm font-medium text-amber-900 hover:bg-amber-100">Save as unsigned draft</button>
            </form>
            <form method="POST" action="{{ route('loans.approve') }}" class="mt-6 rounded-xl border border-[#e3ebe5] bg-white p-6 print:hidden">
                @csrf
                <h2 class="text-lg font-semibold">Approve signed loan agreement</h2>
                <p class="mt-1 text-sm text-[#5b7567]">After all required parties have signed the printed agreement, enter your password to save this as an official loan.</p>
                <div class="mt-4 max-w-md">
                    <label for="approval_password" class="mb-1 block text-sm font-medium">Admin password</label>
                    <input id="approval_password" name="current_password" type="password" required autocomplete="current-password" placeholder="Re-enter your password" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 placeholder:text-gray-400 focus:border-green-600 focus:outline-none focus:ring-2 focus:ring-green-600/20">
                    @error('current_password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="mt-5 flex flex-wrap gap-3">
                    <button type="submit" class="rounded-md bg-[#17744a] px-5 py-2.5 text-sm font-medium text-white hover:bg-[#0f5f3b]">Confirm signatures and approve loan</button>
                    <a href="{{ route('loans.create', session('loan_draft_record_id') ? ['draft' => session('loan_draft_record_id')] : ['resume_preview' => 1]) }}" class="rounded-md border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Edit details</a>
                </div>
            </form>
        @endif
    @endif
</div>
@endsection
