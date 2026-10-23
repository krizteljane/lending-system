@extends('websnippet')
@section('title', 'Loans')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div><h1 class="text-2xl font-semibold">Loans</h1><p class="mt-1 text-sm text-[#5b7567]">Loan records and repayment schedules.</p></div>
        <a href="{{ route('loans.create') }}" class="inline-flex items-center gap-2 rounded-md bg-[#17744a] px-4 py-2.5 text-sm font-medium text-white hover:bg-[#0f5f3b]"><x-icon name="plus" class="h-4 w-4" /> New Loan</a>
    </div>
    @if (session('success'))<div class="mb-5 rounded-lg bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>@endif
    @if ($drafts->isNotEmpty())
        <section class="mb-6 overflow-hidden rounded-xl border border-amber-200 bg-white">
            <div class="border-b border-amber-100 bg-amber-50 px-5 py-3"><h2 class="font-semibold text-amber-900">Saved drafts</h2><p class="mt-1 text-sm text-amber-800">Incomplete or unsigned loan applications are kept here until completed.</p></div>
            <div class="divide-y divide-[#eef2ef]">
                @foreach ($drafts as $loanDraft)
                    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                        <div><p class="font-medium text-[#0f3d2b]">{{ $loanDraft->data['full_name'] ?? 'Borrower details not finished' }}</p><p class="mt-1 text-xs text-[#7a9186]">Last saved {{ $loanDraft->updated_at->format('M j, Y g:i A') }}</p></div>
                        <a href="{{ route('loans.create', ['draft' => $loanDraft->id]) }}" class="rounded-md border border-[#17744a]/40 px-3 py-2 text-sm font-medium text-[#17744a] hover:bg-[#e3f5ea]">Continue draft</a>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
    <div class="overflow-hidden rounded-xl border border-[#e3ebe5] bg-white"><div class="overflow-x-auto">
        <table class="w-full min-w-[900px] text-left text-sm">
            <thead class="text-xs text-[#5b7567]"><tr class="border-b border-[#e3ebe5]"><th class="px-5 py-3">Loan Ref.</th><th class="px-5 py-3">Borrower</th><th class="px-5 py-3">Principal</th><th class="px-5 py-3">Rate</th><th class="px-5 py-3">Monthly payment</th><th class="px-5 py-3">Term</th><th class="px-5 py-3">Status</th><th class="px-5 py-3"></th></tr></thead>
            <tbody>
                @forelse ($loans as $loan)
                    <tr class="border-b border-[#eef2ef] last:border-0"><td class="px-5 py-4 font-medium">{{ $loan->reference }}</td><td class="px-5 py-4">{{ $loan->borrower->full_name }}</td><td class="px-5 py-4">₱{{ number_format((float) $loan->principal, 2) }}</td><td class="px-5 py-4">{{ number_format((float) $loan->monthly_rate, 2) }}% / month</td><td class="px-5 py-4">₱{{ number_format((float) ($loan->schedule[0]['payment'] ?? 0), 2) }}</td><td class="px-5 py-4">{{ $loan->term_months }} months</td><td class="px-5 py-4"><x-status-badge :status="$loan->status" /></td><td class="px-5 py-4"><div class="flex items-center gap-2"><button type="button" class="loan-preview-trigger rounded-md border border-[#17744a]/40 px-3 py-1 text-xs font-medium text-[#17744a] hover:bg-[#e3f5ea]" data-reference="{{ $loan->reference }}" data-borrower="{{ $loan->borrower->full_name }}" data-principal="{{ number_format((float) $loan->principal, 2) }}" data-rate="{{ number_format((float) $loan->monthly_rate, 2) }}" data-months="{{ $loan->term_months }}" data-type="{{ $loan->calculation_type }}" data-fees="{{ number_format((float) $loan->total_charges, 2) }}" data-net="{{ number_format((float) $loan->net_proceeds, 2) }}" data-interest="{{ number_format((float) $loan->total_interest, 2) }}" data-total="{{ number_format((float) $loan->total_payments, 2) }}" data-schedule="{{ json_encode($loan->schedule) }}" data-fee-items="{{ json_encode(array_merge([['label' => 'Notarial fee', 'amount' => (float) $loan->notarial_fee], ['label' => 'Inspection fee', 'amount' => (float) $loan->inspection_fee], ['label' => 'Registration fee', 'amount' => (float) $loan->registration_fee], ['label' => 'Documentary stamp fee', 'amount' => (float) $loan->documentary_stamp_fee], ['label' => 'Service fee', 'amount' => (float) $loan->service_fee]], array_map(fn ($charge) => ['label' => $charge['description'], 'amount' => (float) $charge['amount']], $loan->additional_charges ?? []))) }}">View loan</button><a href="{{ route('loans.disclosure', ['loan' => $loan, 'return' => 'loans']) }}" class="rounded-md border border-[#17744a]/40 px-3 py-1 text-xs font-medium text-[#17744a] hover:bg-[#e3f5ea]">Disclosure</a></div></td></tr>
                @empty
                    <tr><td colspan="8" class="px-5 py-10 text-center text-[#5b7567]">No loan records yet. Select “New Loan” to add a borrower and generate a disclosure.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div></div>

    <dialog id="loanPreviewDialog" class="fixed left-1/2 top-1/2 m-0 w-[min(94vw,58rem)] -translate-x-1/2 -translate-y-1/2 rounded-xl border border-[#d4ddd6] p-0 shadow-[0_24px_70px_rgba(15,61,43,0.3)] backdrop:bg-black/40">
        <div class="flex items-start justify-between border-b border-[#eef3ef] px-5 py-4 sm:px-6">
            <div><p class="text-xs font-medium uppercase tracking-wide text-[#7a9186]">Loan overview</p><h2 id="loanPreviewTitle" class="mt-1 text-lg font-semibold text-[#0f3d2b]"></h2><p id="loanPreviewBorrower" class="mt-1 text-sm text-[#5b7567]"></p></div>
            <button type="button" id="loanPreviewClose" aria-label="Close" class="rounded-md px-2 py-1 text-xl leading-none text-[#5b7567] hover:bg-[#f3f7f4]">&times;</button>
        </div>
        <div class="max-h-[75vh] overflow-y-auto px-5 py-5 sm:px-6">
            <dl class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                <div class="rounded-lg border border-[#dbe5de] p-3"><dt class="text-xs uppercase text-[#63786c]">Principal</dt><dd id="loanPreviewPrincipal" class="mt-1 font-semibold text-[#0f4a33]"></dd></div>
                <div class="rounded-lg border border-[#dbe5de] p-3"><dt class="text-xs uppercase text-[#63786c]">Monthly interest</dt><dd id="loanPreviewRate" class="mt-1 font-semibold text-[#0f4a33]"></dd></div>
                <div class="rounded-lg border border-[#dbe5de] p-3"><dt class="text-xs uppercase text-[#63786c]">Term</dt><dd id="loanPreviewTerm" class="mt-1 font-semibold text-[#0f4a33]"></dd></div>
                <div class="rounded-lg border border-[#dbe5de] p-3"><dt class="text-xs uppercase text-[#63786c]">Total fees / deductions</dt><dd id="loanPreviewFees" class="mt-1 font-semibold text-[#0f4a33]"></dd></div>
                <div class="rounded-lg border border-[#dbe5de] p-3"><dt class="text-xs uppercase text-[#63786c]">Net proceeds</dt><dd id="loanPreviewNet" class="mt-1 font-semibold text-[#0f4a33]"></dd></div>
                <div class="rounded-lg border border-[#dbe5de] p-3"><dt class="text-xs uppercase text-[#63786c]">Total interest</dt><dd id="loanPreviewInterest" class="mt-1 font-semibold text-[#0f4a33]"></dd></div>
                <div class="rounded-lg border border-[#dbe5de] p-3 sm:col-span-2"><dt class="text-xs uppercase text-[#63786c]">Total payments</dt><dd id="loanPreviewTotal" class="mt-1 font-semibold text-[#0f4a33]"></dd></div>
                <div class="rounded-lg border border-[#dbe5de] p-3"><dt class="text-xs uppercase text-[#63786c]">Interest calculation</dt><dd id="loanPreviewMethod" class="mt-1 font-semibold capitalize text-[#0f4a33]"></dd></div>
            </dl>
            <h3 class="mb-3 mt-6 font-semibold text-[#0f3d2b]">Fees and deductions</h3>
            <div class="overflow-x-auto rounded-lg border border-[#e3ebe5]">
                <table class="w-full text-left text-sm"><thead class="bg-[#f6f9f6] text-xs text-[#5b7567]"><tr><th class="px-3 py-2">Fee</th><th class="px-3 py-2 text-right">Amount</th></tr></thead><tbody id="loanPreviewFeeItems" class="divide-y divide-[#eef2ef]"></tbody></table>
            </div>
            <h3 class="mb-3 mt-6 font-semibold text-[#0f3d2b]">Monthly amortization schedule</h3>
            <div class="overflow-x-auto rounded-lg border border-[#e3ebe5]">
                <table class="w-full min-w-[620px] text-left text-sm">
                    <thead class="bg-[#f6f9f6] text-xs text-[#5b7567]"><tr><th class="px-3 py-2">Month</th><th class="px-3 py-2">Due date</th><th class="px-3 py-2 text-right">Payment</th><th class="px-3 py-2 text-right">Principal</th><th class="px-3 py-2 text-right">Interest</th><th class="px-3 py-2 text-right">Balance</th></tr></thead>
                    <tbody id="loanPreviewSchedule" class="divide-y divide-[#eef2ef]"></tbody>
                </table>
            </div>
        </div>
    </dialog>
    <script>
        (() => {
            const dialog = document.getElementById('loanPreviewDialog');
            const setText = (id, value) => { document.getElementById(id).textContent = value; };
            const money = (value) => {
                const amount = Number(String(value ?? '0').replace(/,/g, ''));
                return `₱${(Number.isFinite(amount) ? amount : 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            };

            document.querySelectorAll('.loan-preview-trigger').forEach((button) => {
                button.addEventListener('click', () => {
                    setText('loanPreviewTitle', `Loan ${button.dataset.reference}`);
                    setText('loanPreviewBorrower', button.dataset.borrower);
                    setText('loanPreviewPrincipal', money(button.dataset.principal));
                    setText('loanPreviewRate', `${button.dataset.rate}% / month`);
                    setText('loanPreviewTerm', `${button.dataset.months} months`);
                    setText('loanPreviewFees', money(button.dataset.fees));
                    setText('loanPreviewNet', money(button.dataset.net));
                    setText('loanPreviewInterest', money(button.dataset.interest));
                    setText('loanPreviewTotal', money(button.dataset.total));
                    setText('loanPreviewMethod', button.dataset.type === 'flat' ? 'Flat interest' : 'Diminishing balance');
                    const feeItems = JSON.parse(button.dataset.feeItems || '[]');
                    const feeBody = document.getElementById('loanPreviewFeeItems');
                    feeBody.replaceChildren();
                    const chargedItems = feeItems.filter((item) => Number(item.amount) > 0);
                    if (chargedItems.length === 0) {
                        const emptyRow = document.createElement('tr');
                        const emptyCell = document.createElement('td');
                        emptyCell.colSpan = 2;
                        emptyCell.className = 'px-3 py-2 text-[#5b7567]';
                        emptyCell.textContent = 'No fees were charged for this loan.';
                        emptyRow.append(emptyCell);
                        feeBody.append(emptyRow);
                    }
                    chargedItems.forEach((item) => {
                        const tr = document.createElement('tr');
                        [item.label, money(item.amount)].forEach((value, index) => {
                            const td = document.createElement('td');
                            td.className = `px-3 py-2${index === 1 ? ' text-right' : ''}`;
                            td.textContent = value;
                            tr.append(td);
                        });
                        feeBody.append(tr);
                    });
                    const totalRow = document.createElement('tr');
                    totalRow.className = 'bg-[#f6f9f6] font-semibold';
                    totalRow.innerHTML = '<td class="px-3 py-2">Total fees / deductions</td><td class="px-3 py-2 text-right"></td>';
                    totalRow.lastElementChild.textContent = money(button.dataset.fees);
                    feeBody.append(totalRow);
                    const schedule = JSON.parse(button.dataset.schedule || '[]');
                    const body = document.getElementById('loanPreviewSchedule');
                    body.replaceChildren();
                    schedule.forEach((row) => {
                        const tr = document.createElement('tr');
                        const dueDate = new Date(`${row.due_date}T00:00:00`).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
                        [row.month, dueDate, money(row.payment), money(row.principal), money(row.interest), money(row.balance)].forEach((value, index) => {
                            const td = document.createElement('td');
                            td.className = `px-3 py-2${index > 1 ? ' text-right' : ''}`;
                            td.textContent = value;
                            tr.append(td);
                        });
                        body.append(tr);
                    });
                    dialog.showModal();
                });
            });
            document.getElementById('loanPreviewClose').addEventListener('click', () => dialog.close());
            dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
        })();
    </script>@endsection
