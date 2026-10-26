@extends('websnippet')
@section('title', 'Blank Loan Agreement')

@section('content')
<style>
    @page { size: A4 portrait; margin: 14mm; }
    .blank-contract { color: #111827; font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.45; }
    .blank-contract h2 { margin-top: 18px; margin-bottom: 6px; font-size: 12pt; font-weight: 700; }
    .blank-contract p { margin: 7px 0; }
    .blank-line { display: inline-block; min-width: 180px; height: 1.2em; border-bottom: 1px solid #111827; vertical-align: bottom; }
    .blank-line.short { min-width: 90px; }
    .blank-line.long { min-width: 320px; }
    .blank-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    .blank-table th, .blank-table td { border: 1px solid #6b7280; padding: 7px; text-align: left; }
    .blank-table td { height: 28px; }
    @media print {
        body { background: white !important; color: #111 !important; }
        #sidebar, #backdrop, header.sticky, #adminContextMenu, .blank-page-heading { display: none !important; }
        .lg\:pl-60 { padding-left: 0 !important; }
        main { padding: 0 !important; }
        .blank-contract { max-width: none !important; padding: 0 !important; border: 0 !important; box-shadow: none !important; }
        .blank-signatures { break-inside: avoid; }
    }
</style>
<div class="mx-auto max-w-4xl">
    <div class="blank-page-heading mb-5 flex flex-wrap items-start justify-between gap-3">
        <div>
            <a href="{{ route('contracts.index') }}" class="text-sm font-medium text-[#17744a] hover:underline">← Back to contracts</a>
            <h1 class="mt-2 text-2xl font-semibold">Blank loan agreement</h1>
            <p class="mt-1 text-sm text-[#5b7567]">Fill in the details by hand, then print or save as PDF.</p>
        </div>
        <button type="button" onclick="window.print()" class="rounded-lg bg-[#17744a] px-4 py-2 text-sm font-medium text-white hover:bg-[#0f5f3b]">Print / Save as PDF</button>
    </div>

    <article class="blank-contract rounded-xl border border-[#d4ddd6] bg-white p-8 shadow-sm">
        <header class="border-b-2 border-[#17744a] pb-4 text-center">
            <p class="font-bold tracking-wide text-[#0f5b40]">SAN LUIS AGRI-VENTURES COOPERATIVE</p>
            <p>Purok 1, Banoyo, San Luis, Batangas</p>
            <h1 class="mt-4 text-xl font-bold">LOAN AGREEMENT</h1>
            <p>Agreement date: <span class="blank-line short"></span> &nbsp; Loan reference: <span class="blank-line short"></span></p>
        </header>

        <h2>1. Parties</h2>
        <p>This Loan Agreement is between San Luis Agri-Ventures Agriculture Cooperative (the “Lender”) and the following borrower:</p>
        <p>Borrower full name: <span class="blank-line long"></span></p>
        <p>Address: <span class="blank-line long"></span></p>
        <p>Phone / email: <span class="blank-line long"></span></p>
        <p>Co-borrower full name (if any): <span class="blank-line"></span></p>
        <p>Address / contact: <span class="blank-line long"></span></p>
        <p>Second co-borrower full name (if any): <span class="blank-line"></span></p>
        <p>Address / contact: <span class="blank-line long"></span></p>

        <h2>2. Loan amount and release</h2>
        <p>Principal amount: PHP <span class="blank-line short"></span> &nbsp; Purpose: <span class="blank-line"></span></p>
        <p>Date released: <span class="blank-line short"></span> &nbsp; Net amount received: PHP <span class="blank-line short"></span></p>
        <p>Deductions (describe): <span class="blank-line long"></span></p>
        <p>Total deductions: PHP <span class="blank-line short"></span></p>

        <h2>3. Interest and term</h2>
        <p>Monthly interest rate: <span class="blank-line short"></span>% &nbsp; Calculation method: ☐ Flat &nbsp; ☐ Diminishing balance &nbsp; ☐ No interest</p>
        <p>Loan term: <span class="blank-line short"></span> months &nbsp; Release date: <span class="blank-line short"></span></p>
        <p>Final due date: <span class="blank-line short"></span> &nbsp; Total scheduled repayment: PHP <span class="blank-line short"></span></p>

        <h2>4. Repayment schedule</h2>
        <p>The borrower will make payments according to the schedule below. Attach additional pages if needed.</p>
        <table class="blank-table">
            <thead><tr><th>Payment no.</th><th>Due date</th><th>Amount (PHP)</th><th>Received / initials</th></tr></thead>
            <tbody>@for ($i = 1; $i <= 8; $i++)<tr><td>{{ $i }}</td><td></td><td></td><td></td></tr>@endfor</tbody>
        </table>

        <h2>5. Late payment charge</h2>
        <p>If an installment remains unpaid for 5 days after its due date, a penalty of 1.25% of the original principal will be charged for that late month. An additional 1.25% charge applies for each additional 30-day period that the installment remains unpaid.</p>

        <h2>6. Additional terms</h2>
        <p><span class="blank-line long"></span></p>
        <p><span class="blank-line long"></span></p>
        <p><span class="blank-line long"></span></p>
        <p><span class="blank-line long"></span></p>
        <p><span class="blank-line long"></span></p>

        <p class="mt-5">By signing below, the parties confirm that they have reviewed this agreement and its repayment schedule.</p>
        <div class="blank-signatures mt-12 grid grid-cols-2 gap-x-12 gap-y-10">
            <div class="border-t border-gray-700 pt-2 text-center">Borrower signature over printed name<br><span class="blank-line"></span><br>Date: <span class="blank-line short"></span></div>
            <div class="border-t border-gray-700 pt-2 text-center">Lender authorized representative<br><span class="blank-line"></span><br>Date: <span class="blank-line short"></span></div>
            <div class="border-t border-gray-700 pt-2 text-center">Co-borrower signature (if applicable)<br><span class="blank-line"></span><br>Date: <span class="blank-line short"></span></div>
            <div class="border-t border-gray-700 pt-2 text-center">Second co-borrower signature (if applicable)<br><span class="blank-line"></span><br>Date: <span class="blank-line short"></span></div>
            <div class="border-t border-gray-700 pt-2 text-center">Witness name and signature<br><span class="blank-line"></span><br>Date: <span class="blank-line short"></span></div>
        </div>
    </article>
</div>
@endsection
