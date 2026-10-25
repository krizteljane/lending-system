@extends('websnippet')
@section('title', 'Promissory Note')

@section('content')
<style>
    @page { size: A4 portrait; margin: 12mm; }
    .note-masthead { display: flex; align-items: center; gap: 14px; }
    .note-masthead img { width: 58px; height: 58px; object-fit: contain; }
    .note-party { border: 1px solid #dbe5de; border-radius: 7px; padding: 10px 12px; }
    .note-label { color: #17744a; font-size: 9px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    @media print {
        html, body { background: #fff !important; color: #111 !important; }
        #sidebar, #backdrop, header.sticky, #adminContextMenu, .note-actions { display: none !important; }
        .lg\:pl-60 { padding-left: 0 !important; }
        main { padding: 0 !important; }
        .note-sheet { width: 100% !important; max-width: none !important; margin: 0 !important; padding: 0 !important; border: 0 !important; box-shadow: none !important; font-size: 9pt !important; line-height: 1.3 !important; }
        .note-sheet table { width: 100% !important; min-width: 0 !important; }
        .note-sheet tr, .note-signatures { break-inside: avoid; page-break-inside: avoid; }
        .note-sheet th, .note-sheet td { padding: 3px 5px !important; }
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    }
</style>

<div class="mx-auto max-w-4xl">
    <div class="note-actions mb-5 flex flex-wrap items-center justify-between gap-3">
        <div><a href="{{ route('contracts.index', ['document' => 'promissory-note']) }}" class="mb-1 inline-flex items-center gap-1 text-sm font-medium text-[#17744a] hover:underline"><span aria-hidden="true">←</span> Back to contracts</a><h1 class="text-2xl font-semibold">Promissory note</h1><p class="mt-1 text-sm text-[#5b7567]">For {{ $loan->borrower->full_name }} · {{ $loan->reference }}</p></div>
        <button type="button" class="password-confirm-trigger rounded-md border border-[#17744a] px-4 py-2 text-sm font-medium text-[#17744a] hover:bg-[#e3f5ea]" data-confirm-action="print">Print note</button>
    </div>
    <nav class="note-actions mb-5 flex gap-6 border-b border-[#e3ebe5]" aria-label="Loan documents">
        <a href="{{ route('loans.contract', $loan) }}" class="border-b-2 border-transparent px-1 py-3 text-sm font-medium text-slate-500">Loan Agreement</a>
        <a href="{{ route('loans.promissory-note', $loan) }}" aria-current="page" class="border-b-2 border-[#111827] px-1 py-3 text-sm font-medium text-slate-700">Promissory Note</a>
    </nav>

    <article class="note-sheet rounded-xl border border-[#d4ddd6] bg-white p-4 text-sm leading-relaxed text-gray-900 shadow-sm sm:p-10">
        <header class="border-b border-gray-400 pb-4 text-center">
            <div class="note-masthead justify-center"><img src="{{ asset('images/logo.jpg') }}" alt="SALUAG logo"><div class="text-left"><p class="font-bold tracking-wide text-[#0f5b40]">SALUAG</p><h2 class="text-sm font-semibold">San Luis Agri-Ventures Cooperative</h2><p class="text-xs text-gray-600">Purok 1, Banoyo, San Luis, Batangas</p></div></div>
            <h2 class="mt-5 text-xl font-bold tracking-wide">PROMISSORY NOTE</h2>
            <p class="mt-1 text-xs text-gray-600">Loan reference: <strong>{{ $loan->reference }}</strong></p>
        </header>

        <div class="mt-5 grid gap-3 sm:grid-cols-2">
            <section class="note-party"><p class="note-label">Borrower / Maker</p><p class="mt-1 font-semibold">{{ $loan->borrower->full_name }}</p><p>{{ $loan->borrower->address }}</p><p class="mt-1 text-xs">Phone: {{ $loan->borrower->phone ?: '—' }} · Email: {{ $loan->borrower->email ?: '—' }}</p><p class="text-xs">Date of birth: {{ $loan->borrower->date_of_birth?->format('M j, Y') ?: '—' }}</p><p class="text-xs">ID: {{ $loan->borrower->id_type ?: '—' }} · {{ $loan->borrower->id_number ?: '—' }}</p></section>
            <section class="note-party"><p class="note-label">Lender / Payee</p><p class="mt-1 font-semibold">San Luis Agri-Ventures Cooperative</p><p>Purok 1, Banoyo, San Luis, Batangas</p></section>
            @if ($loan->co_borrower_name)<section class="note-party"><p class="note-label">Co-borrower</p><p class="mt-1 font-semibold">{{ $loan->co_borrower_name }}</p><p>{{ $loan->co_borrower_address }}</p><p class="text-xs">Phone: {{ $loan->co_borrower_phone ?: '—' }} · Email: {{ $loan->co_borrower_email ?: '—' }}</p><p class="text-xs">Date of birth: {{ $loan->co_borrower_date_of_birth?->format('M j, Y') ?: '—' }}</p><p class="text-xs">ID: {{ $loan->co_borrower_id_type ?: '—' }} · {{ $loan->co_borrower_id_number ?: '—' }}</p></section>@endif
            @if ($loan->second_co_borrower_name)<section class="note-party"><p class="note-label">Second co-borrower</p><p class="mt-1 font-semibold">{{ $loan->second_co_borrower_name }}</p><p>{{ $loan->second_co_borrower_address }}</p><p class="text-xs">Phone: {{ $loan->second_co_borrower_phone ?: '—' }} · Email: {{ $loan->second_co_borrower_email ?: '—' }}</p><p class="text-xs">Date of birth: {{ $loan->second_co_borrower_date_of_birth?->format('M j, Y') ?: '—' }}</p><p class="text-xs">ID: {{ $loan->second_co_borrower_id_type ?: '—' }} · {{ $loan->second_co_borrower_id_number ?: '—' }}</p></section>@endif
        </div>

        <p class="mt-6">For value received, the Borrower promises to pay to the order of the Lender the principal amount of <strong>₱{{ number_format((float) $loan->principal, 2) }}</strong>, together with interest and any charges expressly stated in this note and the related Loan Agreement.</p>

        <h3 class="mb-2 mt-6 border-l-[3px] border-[#6fcf97] pl-2 font-bold text-[#0f4a33]">Promise and repayment terms</h3>
        <div class="overflow-x-auto"><table class="w-full border-collapse"><tbody>
            <tr class="border-t border-gray-300"><th class="py-2 text-left font-medium">Principal amount</th><td class="py-2 text-right">₱{{ number_format((float) $loan->principal, 2) }}</td></tr>
            <tr class="border-t border-gray-300"><th class="py-2 text-left font-medium">Interest rate and method</th><td class="py-2 text-right">{{ number_format((float) $loan->monthly_rate, 2) }}% per month, {{ $loan->calculation_type }} method</td></tr>
            <tr class="border-t border-gray-300"><th class="py-2 text-left font-medium">Scheduled principal and interest</th><td class="py-2 text-right">₱{{ number_format((float) $loan->total_payments, 2) }}</td></tr>
            <tr class="border-t border-gray-300"><th class="py-2 text-left font-medium">Term</th><td class="py-2 text-right">{{ $loan->term_months }} months</td></tr>
            <tr class="border-t border-gray-300"><th class="py-2 text-left font-medium">Date of note / release</th><td class="py-2 text-right">{{ $loan->release_date->format('F j, Y') }}</td></tr>
            <tr class="border-t border-gray-300"><th class="py-2 text-left font-medium">Final scheduled due date</th><td class="py-2 text-right">{{ \Illuminate\Support\Carbon::parse($loan->schedule[array_key_last($loan->schedule)]['due_date'])->format('F j, Y') }}</td></tr>
            <tr class="border-y border-gray-300"><th class="py-2 text-left font-medium">Net proceeds after listed deductions</th><td class="py-2 text-right">₱{{ number_format((float) $loan->net_proceeds, 2) }}</td></tr>
        </tbody></table></div>
        <p class="mt-3">Payments shall be made in the installments and on the due dates shown in the schedule below. Payments recorded by the Lender will be credited against the outstanding installments.</p>

        <h3 class="mb-2 mt-6 border-l-[3px] border-[#6fcf97] pl-2 font-bold text-[#0f4a33]">Installment schedule</h3>
        <div class="overflow-x-auto"><table class="w-full min-w-[600px] border-collapse text-xs sm:text-sm">
            <thead><tr class="border-y-2 border-black text-left"><th class="px-2 py-2">Installment</th><th class="px-2 py-2">Due date</th><th class="px-2 py-2 text-right">Payment</th><th class="px-2 py-2 text-right">Principal</th><th class="px-2 py-2 text-right">Interest</th><th class="px-2 py-2 text-right">Balance</th></tr></thead>
            <tbody>@foreach ($loan->schedule as $row)<tr class="border-b border-gray-300"><td class="px-2 py-2">{{ $row['month'] }}</td><td class="px-2 py-2">{{ \Illuminate\Support\Carbon::parse($row['due_date'])->format('M j, Y') }}</td><td class="px-2 py-2 text-right">₱{{ number_format($row['payment'], 2) }}</td><td class="px-2 py-2 text-right">₱{{ number_format($row['principal'], 2) }}</td><td class="px-2 py-2 text-right">₱{{ number_format($row['interest'], 2) }}</td><td class="px-2 py-2 text-right">₱{{ number_format($row['balance'], 2) }}</td></tr>@endforeach
                <tr class="border-b-2 border-black font-bold"><td class="px-2 py-2" colspan="2">TOTAL</td><td class="px-2 py-2 text-right">₱{{ number_format((float) $loan->total_payments, 2) }}</td><td class="px-2 py-2 text-right">₱{{ number_format((float) $loan->principal, 2) }}</td><td class="px-2 py-2 text-right">₱{{ number_format((float) $loan->total_interest, 2) }}</td><td class="px-2 py-2 text-right">₱0.00</td></tr>
            </tbody>
        </table></div>

        <h3 class="mb-2 mt-6 border-l-[3px] border-[#6fcf97] pl-2 font-bold text-[#0f4a33]">Late-payment charge</h3>
        <p>An installment unpaid for 5 days after its due date incurs a late charge of 1.25% of the original principal for that late month. An additional 1.25% charge applies for each additional 30-day period that the installment remains unpaid. For example, on a ₱20,000 principal, each late-month charge is ₱250 per overdue installment. Late charges are additional to the scheduled payments above.</p>

        <h3 class="mb-2 mt-6 border-l-[3px] border-[#6fcf97] pl-2 font-bold text-[#0f4a33]">Additional agreed terms</h3>
        @if (filled($loan->loan_terms))<p class="whitespace-pre-line">{{ $loan->loan_terms }}</p>@else<p class="text-gray-600">No additional terms were entered for this note.</p>@endif

        <p class="mt-6">The Borrower acknowledges receipt of the loan proceeds and agrees to repay the amount according to this note and the related Loan Agreement. This note should be read together with the Loan Agreement for the complete terms of the transaction.</p>
        <div class="note-signatures mt-12 grid gap-x-12 gap-y-10 sm:grid-cols-2">
            <div class="border-t border-gray-500 pt-2 text-center"><p class="font-semibold">{{ $loan->borrower->full_name }}</p><p class="text-xs">Borrower signature over printed name</p><p class="mt-4 text-left text-xs">Date: ____________________</p></div>
            <div class="border-t border-gray-500 pt-2 text-center"><p class="font-semibold">For the Cooperative</p><p class="text-xs">Authorized representative</p><p class="mt-4 text-left text-xs">Date: ____________________</p></div>
            @if ($loan->co_borrower_name)<div class="border-t border-gray-500 pt-2 text-center"><p class="font-semibold">{{ $loan->co_borrower_name }}</p><p class="text-xs">Co-borrower signature over printed name</p><p class="mt-4 text-left text-xs">Date: ____________________</p></div>@endif
            @if ($loan->second_co_borrower_name)<div class="border-t border-gray-500 pt-2 text-center"><p class="font-semibold">{{ $loan->second_co_borrower_name }}</p><p class="text-xs">Second co-borrower signature over printed name</p><p class="mt-4 text-left text-xs">Date: ____________________</p></div>@endif
        </div>
        <div class="mt-10 max-w-sm">
            <p class="mb-1 text-right text-xs font-semibold">Witness</p>
            <div class="border-b border-gray-500"></div>
            <p class="mt-2 text-xs">Signature and date: ____________________</p>
        </div>
    </article>
</div>
@endsection
