@extends('websnippet')

@section('title', 'Reports')

@section('content')
@php
    $tabs = [
        'collections' => 'Collections',
        'outstanding' => 'Outstanding loans',
        'overdue' => 'Overdue payments',
        'borrower-statements' => 'Borrower statements',
    ];
    $queryFor = fn (string $report) => array_filter([
        'report' => $report,
        'from' => $from,
        'to' => $to,
        'borrower_id' => $selectedBorrower?->id,
    ], fn ($value) => $value !== null && $value !== '');
    $peso = html_entity_decode('&#8369;', ENT_HTML5, 'UTF-8');
    $money = fn ($amount) => $peso.number_format((float) $amount, 2);
@endphp

<div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-[#0f3d2b]">Reports</h1>
            <p class="mt-1 text-sm text-slate-600">Review collections, balances, overdue installments, and borrower payment history.</p>
        </div>
        <button type="button" class="password-confirm-trigger print:hidden rounded-lg border border-[#0f4a33] px-4 py-2 text-sm font-medium text-[#0f4a33] hover:bg-[#0f4a33]/5" data-confirm-action="print">Print report</button>
    </div>

    <nav class="mb-5 flex gap-2 overflow-x-auto" aria-label="Report types">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('reports.index', $queryFor($key)) }}" @if($activeReport === $key) aria-current="page" @endif
               style="border-bottom: 2px solid {{ $activeReport === $key ? '#111827' : 'transparent' }}"
               class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-600">
                {{ $label }}
            </a>
        @endforeach
    </nav>

    <form method="GET" action="{{ route('reports.index') }}" class="print:hidden mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <input type="hidden" name="report" value="{{ $activeReport }}">
        <label class="grid gap-1 text-xs font-medium text-slate-600">From
            <input type="date" name="from" value="{{ $from }}" class="rounded-lg border-slate-300 text-sm focus:border-[#16834a] focus:ring-[#16834a]">
        </label>
        <label class="grid gap-1 text-xs font-medium text-slate-600">To
            <input type="date" name="to" value="{{ $to }}" class="rounded-lg border-slate-300 text-sm focus:border-[#16834a] focus:ring-[#16834a]">
        </label>
        @if ($activeReport === 'borrower-statements')
            <label class="grid min-w-56 gap-1 text-xs font-medium text-slate-600">Borrower
                <select name="borrower_id" class="rounded-lg border-slate-300 text-sm focus:border-[#16834a] focus:ring-[#16834a]">
                    <option value="">Select borrower</option>
                    @foreach ($borrowers as $borrower)
                        <option value="{{ $borrower->id }}" @selected($selectedBorrower?->id === $borrower->id)>{{ $borrower->full_name }}</option>
                    @endforeach
                </select>
            </label>
        @endif
        <button class="rounded-lg bg-[#0f4a33] px-4 py-2 text-sm font-medium text-white hover:bg-[#0b3b29]">Apply filters</button>
        <a href="{{ route('reports.index', ['report' => $activeReport]) }}" class="px-2 py-2 text-sm text-slate-500 hover:text-slate-800">Clear</a>
    </form>

    <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="font-semibold text-slate-900">{{ $tabs[$activeReport] }}</h2>
            @if ($from || $to)
                <p class="mt-1 text-xs text-slate-500">{{ $from ? \Illuminate\Support\Carbon::parse($from)->format('M j, Y') : 'Beginning' }} – {{ $to ? \Illuminate\Support\Carbon::parse($to)->format('M j, Y') : 'Today' }}</p>
            @endif
        </div>

        @if ($activeReport === 'collections')
            <div class="flex flex-wrap justify-between gap-2 bg-[#f4faf5] px-5 py-3 text-sm"><span class="text-slate-600">Payments received: <strong>{{ $collectionPayments->count() }}</strong></span><span class="font-semibold text-[#0f4a33]">Total collected: {{ $money($collectionPayments->sum('amount')) }}</span></div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Date</th><th class="px-5 py-3">Borrower</th><th class="px-5 py-3">Loan reference</th><th class="px-5 py-3">Note</th><th class="px-5 py-3 text-right">Amount</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">@forelse ($collectionPayments as $payment)<tr><td class="px-5 py-3">{{ $payment->paid_at?->format('M j, Y') }}</td><td class="px-5 py-3">{{ $payment->loan?->borrower?->full_name ?? '—' }}</td><td class="px-5 py-3">{{ $payment->loan?->reference ?? '—' }}</td><td class="px-5 py-3">{{ $payment->note ?: '—' }}</td><td class="px-5 py-3 text-right font-medium">{{ $money($payment->amount) }}</td></tr>@empty<tr><td colspan="5" class="px-5 py-12 text-center text-slate-500">No payments found for this period.</td></tr>@endforelse</tbody>
                </table>
            </div>
        @elseif ($activeReport === 'outstanding')
            <div class="flex flex-wrap justify-between gap-2 bg-[#f4faf5] px-5 py-3 text-sm"><span class="text-slate-600">Loans with balance: <strong>{{ $outstandingLoans->count() }}</strong></span><span class="font-semibold text-[#0f4a33]">Remaining to collect: {{ $money($outstandingLoans->sum(fn ($row) => $row['summary']['remaining_due'])) }}</span></div>
            <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Loan</th><th class="px-5 py-3">Borrower</th><th class="px-5 py-3">Released</th><th class="px-5 py-3 text-right">Principal</th><th class="px-5 py-3 text-right">Returned</th><th class="px-5 py-3 text-right">Penalty</th><th class="px-5 py-3 text-right">Balance</th></tr></thead>
                <tbody class="divide-y divide-slate-100">@forelse ($outstandingLoans as $row) @php($loan = $row['loan']) @php($summary = $row['summary'])<tr><td class="px-5 py-3">{{ $loan->reference }}</td><td class="px-5 py-3">{{ $loan->borrower?->full_name ?? '—' }}</td><td class="px-5 py-3">{{ $loan->release_date?->format('M j, Y') }}</td><td class="px-5 py-3 text-right">{{ $money($loan->principal) }}</td><td class="px-5 py-3 text-right">{{ $money($summary['returned']) }}</td><td class="px-5 py-3 text-right">{{ $money($summary['late_penalty']) }}</td><td class="px-5 py-3 text-right font-semibold">{{ $money($summary['remaining_due']) }}</td></tr>@empty<tr><td colspan="7" class="px-5 py-12 text-center text-slate-500">No outstanding loans found.</td></tr>@endforelse</tbody>
            </table></div>
        @elseif ($activeReport === 'overdue')
            <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Borrower</th><th class="px-5 py-3">Loan</th><th class="px-5 py-3">Installment</th><th class="px-5 py-3">Due date</th><th class="px-5 py-3 text-right">Days late</th><th class="px-5 py-3 text-right">Installment balance</th><th class="px-5 py-3 text-right">Penalty</th></tr></thead>
                <tbody class="divide-y divide-slate-100">@forelse ($overduePayments as $row) @php($loan = $row['loan']) @php($installment = $row['installment'])<tr><td class="px-5 py-3">{{ $loan->borrower?->full_name ?? '—' }}</td><td class="px-5 py-3">{{ $loan->reference }}</td><td class="px-5 py-3">Month {{ $installment['month'] ?? '—' }}</td><td class="px-5 py-3">{{ \Illuminate\Support\Carbon::parse($installment['due_date'])->format('M j, Y') }}</td><td class="px-5 py-3 text-right font-medium text-red-700">{{ $row['days_late'] }}</td><td class="px-5 py-3 text-right">{{ $money($installment['remaining_due']) }}</td><td class="px-5 py-3 text-right">{{ $money($installment['late_penalty']) }}</td></tr>@empty<tr><td colspan="7" class="px-5 py-12 text-center text-slate-500">No overdue installments found.</td></tr>@endforelse</tbody>
            </table></div>
        @else
            @if (! $selectedBorrower)
                <div class="px-5 py-12 text-center text-slate-500">Choose a borrower above to view their loan and payment statement.</div>
            @else
                <div class="grid gap-3 border-b border-slate-100 bg-[#f4faf5] px-5 py-4 sm:grid-cols-3"><div><p class="text-xs text-slate-500">Borrower</p><p class="font-semibold">{{ $selectedBorrower->full_name }}</p></div><div><p class="text-xs text-slate-500">Address</p><p>{{ $selectedBorrower->address ?: '—' }}</p></div><div><p class="text-xs text-slate-500">Contact</p><p>{{ $selectedBorrower->phone ?: $selectedBorrower->email ?: '—' }}</p></div></div>
                <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Payment date</th><th class="px-5 py-3">Loan reference</th><th class="px-5 py-3">Details</th><th class="px-5 py-3 text-right">Amount paid</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">@forelse ($statementPayments as $payment)<tr><td class="px-5 py-3">{{ $payment->paid_at?->format('M j, Y') }}</td><td class="px-5 py-3">{{ $payment->loan?->reference ?? '—' }}</td><td class="px-5 py-3">{{ $payment->note ?: 'Payment' }}</td><td class="px-5 py-3 text-right font-medium">{{ $money($payment->amount) }}</td></tr>@empty<tr><td colspan="4" class="px-5 py-10 text-center text-slate-500">No payments found for this borrower and period.</td></tr>@endforelse</tbody>
                    <tfoot class="bg-slate-50"><tr><td colspan="3" class="px-5 py-3 text-right font-semibold">Total paid in selected period</td><td class="px-5 py-3 text-right font-semibold">{{ $money($statementPayments->sum('amount')) }}</td></tr></tfoot>
                </table></div>
                <div class="border-t border-slate-100 px-5 py-4"><h3 class="mb-3 text-sm font-semibold">Loans</h3><div class="grid gap-3 sm:grid-cols-2">@forelse ($selectedBorrower->loans as $loan) @php($summary = app(\App\Services\LoanAccounting::class)->summary($loan))<div class="rounded-lg border border-slate-200 p-4 text-sm"><div class="flex justify-between gap-3"><span class="font-medium">{{ $loan->reference }}</span><span class="text-slate-500">{{ $loan->release_date?->format('M j, Y') }}</span></div><div class="mt-3 grid grid-cols-2 gap-2 text-slate-600"><span>Principal</span><span class="text-right">{{ $money($loan->principal) }}</span><span>Returned</span><span class="text-right">{{ $money($summary['returned']) }}</span><span>Remaining</span><span class="text-right font-semibold text-[#0f4a33]">{{ $money($summary['remaining_due']) }}</span></div></div>@empty<p class="text-sm text-slate-500">No loans for this borrower.</p>@endforelse</div></div>
            @endif
        @endif
    </section>
</div>

<style>
@media print {
    body { background: white !important; }
    #sidebar, header, nav[aria-label="Main"], .print\:hidden { display: none !important; }
    main { max-width: none !important; padding: 0 !important; }
    section { border: 0 !important; box-shadow: none !important; }
    tr { break-inside: avoid; }
}
</style>
@endsection
