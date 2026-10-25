@extends('websnippet')
@section('title', 'Loan Agreement')

@section('content')
<style>
    @page { size: A4 portrait; margin: 11mm; }
    .agreement-masthead { display: flex; align-items: center; gap: 16px; }
    .agreement-masthead img { width: 68px; height: 68px; object-fit: contain; }
    .agreement-brand { color: #0f5b40; font-size: 15px; font-weight: 700; letter-spacing: .04em; }
    .agreement-tagline { color: #63786c; font-size: 11px; }
    .agreement-title { margin-top: 14px; border-top: 3px solid #17744a; padding-top: 10px; text-align: center; }
    .agreement-title h3 { color: #0f4a33; font-size: 20px; font-weight: 700; letter-spacing: .08em; }
    .agreement-parties { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-top: 14px; }
    .agreement-party { border: 1px solid #dbe5de; border-radius: 8px; padding: 10px 12px; }
    .agreement-party-label { color: #17744a; font-size: 9px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
    .agreement-summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; margin: 12px 0; }
    .agreement-summary-item { border: 1px solid #dbe5de; border-radius: 7px; padding: 8px; }
    .agreement-summary-label { color: #63786c; font-size: 9px; text-transform: uppercase; }
    .agreement-summary-value { color: #0f4a33; font-size: 13px; font-weight: 700; }
    .agreement-section-title { border-left: 3px solid #6fcf97; padding-left: 8px; color: #0f4a33; }
    @media print {
        html, body { background: #fff !important; }
        #sidebar, #backdrop, header.sticky, #adminContextMenu, .agreement-actions { display: none !important; }
        .lg\:pl-60 { padding-left: 0 !important; }
        main { padding: 0 !important; }
        .agreement-sheet { width: 100% !important; max-width: none !important; margin: 0 !important; padding: 0 !important; border: 0 !important; box-shadow: none !important; font-size: 9pt !important; line-height: 1.28 !important; }
        .agreement-sheet header { padding-bottom: 8px !important; }
        .agreement-sheet header img { width: 42px !important; height: 42px !important; margin-bottom: 2px !important; }
        .agreement-masthead { gap: 8px !important; }
        .agreement-masthead img { width: 44px !important; height: 44px !important; }
        .agreement-brand { font-size: 11pt !important; }
        .agreement-tagline { font-size: 7pt !important; }
        .agreement-title { margin-top: 7px !important; padding-top: 5px !important; }
        .agreement-title h3 { font-size: 14pt !important; }
        .agreement-parties { gap: 6px !important; margin-top: 8px !important; }
        .agreement-party { padding: 5px 7px !important; }
        .agreement-summary { gap: 5px !important; margin: 7px 0 !important; }
        .agreement-summary-item { padding: 5px !important; }
        .agreement-summary-value { font-size: 9pt !important; }
        .agreement-sheet header h3 { margin-top: 5px !important; }
        .agreement-sheet h3 { margin-top: 10px !important; margin-bottom: 3px !important; line-height: 1.2 !important; }
        .agreement-sheet p { margin-top: 3px !important; margin-bottom: 3px !important; line-height: 1.25 !important; }
        .agreement-sheet tr, .agreement-signatures { break-inside: avoid; page-break-inside: avoid; }
        .agreement-sheet table { width: 100% !important; min-width: 0 !important; }
        .agreement-sheet th, .agreement-sheet td { padding-top: 3px !important; padding-bottom: 3px !important; line-height: 1.15 !important; }
        .agreement-sheet .overflow-x-auto { overflow: visible !important; }
        .agreement-signatures { margin-top: 24px !important; gap: 24px !important; }
    }
</style>

<div class="mx-auto max-w-4xl">
    <div class="agreement-actions mb-5 flex items-center justify-between gap-3">
        <div><a href="{{ route('contracts.index', ['document' => 'contract']) }}" class="mb-1 inline-flex items-center gap-1 text-sm font-medium text-[#17744a] hover:underline"><span aria-hidden="true">←</span> Back to contracts</a><h1 class="text-2xl font-semibold">Loan agreement</h1><p class="mt-1 text-sm text-[#5b7567]">Agreement for {{ $loan->borrower->full_name }} · {{ $loan->reference }}</p></div>
        <button type="button" class="password-confirm-trigger rounded-md border border-[#17744a] px-4 py-2 text-sm font-medium text-[#17744a] hover:bg-[#e3f5ea]" data-confirm-action="print">Print agreement</button>
    </div>

    <article class="agreement-sheet rounded-xl border border-[#d4ddd6] bg-white p-8 text-sm leading-relaxed text-gray-900 shadow-sm">
        <header class="border-b border-gray-300 pb-4">
            <div class="agreement-masthead">
                <img src="{{ asset('images/logo.jpg') }}" alt="SALUAG logo">
                <div><p class="agreement-brand">SALUAG</p><h2 class="text-sm font-semibold">San Luis Agri-Ventures Cooperative</h2><p class="agreement-tagline">Purok 1, Banoyo, San Luis, Batangas</p></div>
            </div>
            <div class="agreement-title"><h3>LOAN AGREEMENT</h3><p class="mt-1 text-xs text-gray-600">Agreement reference: <strong>{{ $loan->reference }}</strong></p></div>
        </header>

        <p class="mt-4">Prepared on <strong>{{ $loan->release_date->format('F j, Y') }}</strong> for the loan parties listed below.</p>
        <div class="agreement-parties">
            <div class="agreement-party"><p class="agreement-party-label">Borrower</p><p class="mt-1 font-semibold">{{ $loan->borrower->full_name }}</p><p class="text-xs">{{ $loan->borrower->address }}</p><p class="mt-1 text-xs">Phone: {{ $loan->borrower->phone ?: '—' }} · Email: {{ $loan->borrower->email ?: '—' }}</p><p class="text-xs">Date of birth: {{ $loan->borrower->date_of_birth?->format('M j, Y') ?: '—' }}</p><p class="text-xs">ID: {{ $loan->borrower->id_type }} · {{ $loan->borrower->id_number }}</p>@if($loan->borrower->id_file_path)<a href="{{ route('borrowers.identity', $loan->borrower) }}" class="agreement-private-link text-xs font-medium text-[#17744a] underline print:hidden">View private ID copy</a><p class="hidden print:block text-xs">ID copy held in the loan file.</p>@endif</div>
            <div class="agreement-party"><p class="agreement-party-label">Lender</p><p class="mt-1 font-semibold">San Luis Agri-Ventures Cooperative</p><p class="text-xs">Purok 1, Banoyo, San Luis, Batangas</p></div>
        </div>
        @if ($loan->co_borrower_name || $loan->second_co_borrower_name)
            <div class="agreement-parties mt-2">
                @if ($loan->co_borrower_name)<div class="agreement-party"><p class="agreement-party-label">Co-borrower</p><p class="mt-1 font-semibold">{{ $loan->co_borrower_name }}</p><p class="text-xs">{{ $loan->co_borrower_address }}</p><p class="text-xs">Date of birth: {{ $loan->co_borrower_date_of_birth?->format('M j, Y') ?: '—' }}</p><p class="text-xs">Phone: {{ $loan->co_borrower_phone ?: '—' }} · Email: {{ $loan->co_borrower_email ?: '—' }}</p><p class="text-xs">ID: {{ $loan->co_borrower_id_type }} · {{ $loan->co_borrower_id_number }}</p>@if($loan->co_borrower_id_file_path)<a href="{{ route('loans.identity', [$loan, 'co-borrower']) }}" class="agreement-private-link text-xs font-medium text-[#17744a] underline print:hidden">View private ID copy</a><p class="hidden print:block text-xs">ID copy held in the loan file.</p>@endif</div>@endif
                @if ($loan->second_co_borrower_name)<div class="agreement-party"><p class="agreement-party-label">Second co-borrower</p><p class="mt-1 font-semibold">{{ $loan->second_co_borrower_name }}</p><p class="text-xs">{{ $loan->second_co_borrower_address }}</p><p class="text-xs">Date of birth: {{ $loan->second_co_borrower_date_of_birth?->format('M j, Y') ?: '—' }}</p><p class="text-xs">Phone: {{ $loan->second_co_borrower_phone ?: '—' }} · Email: {{ $loan->second_co_borrower_email ?: '—' }}</p><p class="text-xs">ID: {{ $loan->second_co_borrower_id_type }} · {{ $loan->second_co_borrower_id_number }}</p>@if($loan->second_co_borrower_id_file_path)<a href="{{ route('loans.identity', [$loan, 'second-co-borrower']) }}" class="agreement-private-link text-xs font-medium text-[#17744a] underline print:hidden">View private ID copy</a><p class="hidden print:block text-xs">ID copy held in the loan file.</p>@endif</div>@endif
            </div>
        @endif

        <div class="agreement-summary">
            <div class="agreement-summary-item"><p class="agreement-summary-label">Principal</p><p class="agreement-summary-value">₱{{ number_format((float) $loan->principal, 2) }}</p></div>
            <div class="agreement-summary-item"><p class="agreement-summary-label">Monthly interest</p><p class="agreement-summary-value">{{ number_format((float) $loan->monthly_rate, 2) }}%</p></div>
            <div class="agreement-summary-item"><p class="agreement-summary-label">Term</p><p class="agreement-summary-value">{{ $loan->term_months }} months</p></div>
        </div>

        <h3 class="agreement-section-title mb-2 mt-5 font-bold">Loan amount and release</h3>
        <p>The total amount borrowed from the Lender is <strong>₱{{ number_format((float) $loan->principal, 2) }}</strong> (the “Borrowed Money”). Applicable deductions are:</p>
        <div class="mt-2 overflow-x-auto"><table class="w-full border-collapse"><tbody>
            <tr class="border-t border-gray-300"><th class="py-1.5 text-left font-medium">Notarial fee</th><td class="py-1.5 text-right">₱{{ number_format((float) $loan->notarial_fee, 2) }}</td></tr>
            <tr class="border-t border-gray-300"><th class="py-1.5 text-left font-medium">Inspection fee</th><td class="py-1.5 text-right">₱{{ number_format((float) $loan->inspection_fee, 2) }}</td></tr>
            <tr class="border-t border-gray-300"><th class="py-1.5 text-left font-medium">Registration fees</th><td class="py-1.5 text-right">₱{{ number_format((float) $loan->registration_fee, 2) }}</td></tr>
            <tr class="border-t border-gray-300"><th class="py-1.5 text-left font-medium">Documentary / science stamps</th><td class="py-1.5 text-right">₱{{ number_format((float) $loan->documentary_stamp_fee, 2) }}</td></tr>
            <tr class="border-t border-gray-300"><th class="py-1.5 text-left font-medium">Service fee</th><td class="py-1.5 text-right">₱{{ number_format((float) $loan->service_fee, 2) }}</td></tr>
            @foreach (($loan->additional_charges ?? []) as $charge)
                <tr class="border-t border-gray-300"><th class="py-1.5 text-left font-medium">{{ $charge['description'] }}</th><td class="py-1.5 text-right">₱{{ number_format((float) $charge['amount'], 2) }}</td></tr>
            @endforeach
            <tr class="border-t border-gray-300 font-medium"><th class="py-1.5 text-left">Total deductions</th><td class="py-1.5 text-right">₱{{ number_format((float) $loan->total_charges, 2) }}</td></tr>
            <tr class="border-y border-gray-300 font-semibold"><th class="py-1.5 text-left">Net proceeds to Borrower</th><td class="py-1.5 text-right">₱{{ number_format((float) $loan->net_proceeds, 2) }}</td></tr>
        </tbody></table></div>

        <h3 class="agreement-section-title mb-2 mt-5 font-bold">Interest percentage and calculation</h3>
        <p>☒ The Borrowed Money bears interest at <strong>{{ number_format((float) $loan->monthly_rate, 2) }}% per month</strong>, calculated on the {{ $loan->calculation_type === 'flat' ? 'original principal (flat method)' : 'remaining principal balance (diminishing method)' }}.</p>
        <p>☐ The Borrowed Money does not bear interest.</p>

        <h3 class="agreement-section-title mb-2 mt-5 font-bold">Loan term and final due date</h3>
        <p>The term is <strong>{{ $loan->term_months }} months</strong>, beginning {{ $loan->release_date->format('F j, Y') }}. The scheduled principal and interest total <strong>₱{{ number_format((float) $loan->total_payments, 2) }}</strong>, payable in full by <strong>{{ \Illuminate\Support\Carbon::parse($loan->schedule[array_key_last($loan->schedule)]['due_date'])->format('F j, Y') }}</strong>, subject to any late payment charges described below.</p>

        <h3 class="agreement-section-title mb-2 mt-5 font-bold">Repayment plan</h3>
        <p>☒ <strong>Monthly payments.</strong> The Borrower agrees to make the monthly payments shown in the schedule below, on each installment’s due date, until the Borrowed Money and interest are paid in full.</p>
        <p class="mt-2">Loan purpose: <strong>{{ $loan->purpose ?: 'Not specified' }}</strong>.</p>

        <h3 class="agreement-section-title mb-2 mt-4 font-bold">Installment schedule</h3>
        <div class="overflow-x-auto"><table class="w-full min-w-[600px] border-collapse text-xs sm:text-sm">
            <thead><tr class="border-y-2 border-black text-left"><th class="px-2 py-1.5">Payment</th><th class="px-2 py-1.5">Due date</th><th class="px-2 py-1.5 text-right">Amount</th><th class="px-2 py-1.5 text-right">Principal</th><th class="px-2 py-1.5 text-right">Interest</th><th class="px-2 py-1.5 text-right">Balance</th></tr></thead>
            <tbody>
                @foreach ($loan->schedule as $row)
                    <tr class="border-b border-gray-300"><td class="px-2 py-1.5">{{ $row['month'] }}</td><td class="px-2 py-1.5">{{ \Illuminate\Support\Carbon::parse($row['due_date'])->format('M j, Y') }}</td><td class="px-2 py-1.5 text-right">₱{{ number_format($row['payment'], 2) }}</td><td class="px-2 py-1.5 text-right">₱{{ number_format($row['principal'], 2) }}</td><td class="px-2 py-1.5 text-right">₱{{ number_format($row['interest'], 2) }}</td><td class="px-2 py-1.5 text-right">₱{{ number_format($row['balance'], 2) }}</td></tr>
                @endforeach
                <tr class="border-b-2 border-black font-bold"><td class="px-2 py-1.5" colspan="2">TOTAL</td><td class="px-2 py-1.5 text-right">₱{{ number_format((float) $loan->total_payments, 2) }}</td><td class="px-2 py-1.5 text-right">₱{{ number_format((float) $loan->principal, 2) }}</td><td class="px-2 py-1.5 text-right">₱{{ number_format((float) $loan->total_interest, 2) }}</td><td class="px-2 py-1.5 text-right">₱0.00</td></tr>
            </tbody>
        </table></div>

        <h3 class="agreement-section-title mb-2 mt-5 font-bold">Additional terms and stipulations</h3>
        @if (filled($loan->loan_terms))
            <p class="whitespace-pre-line">{{ $loan->loan_terms }}</p>
        @endif
        <div class="mt-3 space-y-7" aria-label="Space for additional written loan terms"><div class="border-b border-gray-400"></div><div class="border-b border-gray-400"></div><div class="border-b border-gray-400"></div><div class="border-b border-gray-400"></div></div>

        <h3 class="agreement-section-title mb-2 mt-5 font-bold">Late-payment charge</h3>
        <p>If an installment remains unpaid for 5 days after its due date, a penalty of 1.25% of the original principal will be charged for that late month. An additional 1.25% charge applies for each additional 30-day period that the installment remains unpaid. For example, on a ₱20,000 principal, each late-month charge is ₱250 per overdue installment.</p>

        <h3 class="agreement-section-title mb-2 mt-5 font-bold">Important information for the borrower</h3>
        <ul class="list-disc space-y-1 pl-5">
            <li>The interest rate is monthly. The agreement states whether interest is flat or calculated on the remaining balance; check Section III and the repayment schedule.</li>
            <li>The scheduled total is principal plus interest. Late penalties are additional and are not included in that scheduled total.</li>
            <li>The principal is the amount borrowed. Deductions listed in Section II are taken from it; net proceeds are the amount the Borrower receives.</li>
            <li>Each unpaid installment has a 5-day grace period. After that, the monthly penalty is 1.25% of original principal for each overdue installment, with another charge each additional 30 days it remains unpaid.</li>
            <li>Recorded payments are applied to the oldest scheduled installments first. Keep proof of each payment and request an updated balance when needed.</li>
            <li>Any additional terms should be written in Section VI and agreed to by both parties before signing.</li>
        </ul>

        <p class="mt-5">By signing below, the parties confirm that they have reviewed and agree to the loan amount, monthly interest percentage and method, repayment dates, charges, penalties, and any additional terms written in this agreement.</p>
        <div class="agreement-signatures mt-12 grid gap-12 sm:grid-cols-2">
            <div class="border-t border-gray-500 pt-2 text-center"><p class="font-semibold">{{ $loan->borrower->full_name }}</p><p class="text-xs">Borrower signature over printed name</p><p class="mt-4 text-left text-xs">Date: ____________________</p></div>
            <div class="border-t border-gray-500 pt-2 text-center"><p class="font-semibold">San Luis Agri-Ventures Cooperative</p><p class="text-xs">Authorized representative signature</p><p class="mt-3 text-left text-xs">Name: ____________________</p><p class="mt-2 text-left text-xs">Date: ____________________</p></div>
        </div>
        @if ($loan->co_borrower_name || $loan->second_co_borrower_name)
            <div class="agreement-signatures mt-8 grid gap-12 sm:grid-cols-2">
                @if ($loan->co_borrower_name)<div class="border-t border-gray-500 pt-2 text-center"><p class="font-semibold">{{ $loan->co_borrower_name }}</p><p class="text-xs">Co-borrower signature over printed name</p><p class="mt-4 text-left text-xs">Date: ____________________</p></div>@endif
                @if ($loan->second_co_borrower_name)<div class="border-t border-gray-500 pt-2 text-center"><p class="font-semibold">{{ $loan->second_co_borrower_name }}</p><p class="text-xs">Second co-borrower signature over printed name</p><p class="mt-4 text-left text-xs">Date: ____________________</p></div>@endif
            </div>
        @endif
        <div class="mt-8 max-w-sm">
            <p class="mb-1 text-right text-xs font-semibold">Witness</p>
            <div class="border-b border-gray-500"></div>
            <p class="mt-2 text-xs">Signature and date: ____________________</p>
        </div>
    </article>
</div>
@endsection
