@extends('websnippet')
@section('title', 'Payment Schedule')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4"><div><h1 class="text-2xl font-semibold">Payments</h1><p class="mt-1 text-sm text-[#5b7567]">Record repayments and review the remaining schedule.</p></div>
        @if ($loans->isNotEmpty())
            <form method="GET" action="{{ route('payments.index') }}" class="flex w-full items-end gap-2 sm:w-auto"><div class="min-w-0 flex-1 sm:flex-none"><label for="loan_id" class="mb-1 block text-xs font-medium text-[#5b7567]">Select loaner</label><select id="loan_id" name="loan_id" required class="h-10 w-full min-w-0 rounded-md border border-[#dbe5de] bg-white px-3 text-sm sm:w-auto sm:min-w-56"><option value="" @selected(!$loan)>Choose a loaner</option>@foreach ($loans as $option)<option value="{{ $option->id }}" @selected($loan?->id === $option->id)>{{ $option->borrower->full_name }} · {{ $option->reference }}</option>@endforeach</select></div><button class="h-10 shrink-0 rounded-md bg-[#17744a] px-4 text-sm font-medium text-white">View</button></form>
        @endif
    </div>
    @if (!$loan)
        <div class="rounded-xl border border-[#e3ebe5] bg-white p-8 text-center"><p class="text-[#5b7567]">{{ $loans->isEmpty() ? 'No saved loans yet.' : 'Choose a loaner above to view payment information.' }}</p>@if ($loans->isEmpty())<a href="{{ route('loans.create') }}" class="mt-4 inline-flex rounded-md bg-[#17744a] px-4 py-2 text-sm text-white">Create a loan</a>@endif</div>
    @else
        @if (session('success'))<div class="mb-5 rounded-lg bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>@endif
        <div class="mb-6"><p class="text-sm"><span class="font-medium">{{ $loan->reference }}</span> · {{ $loan->borrower->full_name }} · {{ number_format((float) $loan->monthly_rate, 2) }}% monthly</p></div>
        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5"><x-stat-card label="Scheduled Repayment" :value="'₱'.number_format((float) $loan->total_payments, 2)" /><x-stat-card label="Late Penalties" :value="'₱'.number_format($accounting['late_penalty'], 2)" tone="muted" /><x-stat-card label="Total to Be Repaid" :value="'₱'.number_format($accounting['total_due'], 2)" /><x-stat-card label="Returned to Date" :value="'₱'.number_format($returned, 2)" /><x-stat-card label="Remaining" :value="'₱'.number_format($accounting['remaining_due'], 2)" /></div>
        <section class="mb-6 rounded-xl border border-[#e3ebe5] bg-white p-5">
            <h2 class="font-semibold">Record a repayment</h2>
            <form method="POST" action="{{ route('payments.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @csrf<input type="hidden" name="loan_id" value="{{ $loan->id }}">
                <div><label for="amount" class="mb-1 block text-sm font-medium">Amount received (₱)</label><input id="amount" name="amount" type="number" min="0.01" max="{{ $accounting['remaining_due'] }}" step="0.01" required value="{{ old('amount') }}" class="h-10 w-full rounded-md border border-[#dbe5de] px-3">@error('amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                <div><label for="paid_at" class="mb-1 block text-sm font-medium">Date received</label><input id="paid_at" name="paid_at" type="date" required value="{{ old('paid_at', now()->toDateString()) }}" class="h-10 w-full rounded-md border border-[#dbe5de] px-3"></div>
                <div><label for="note" class="mb-1 block text-sm font-medium">Note (optional)</label><input id="note" name="note" type="text" maxlength="255" value="{{ old('note') }}" placeholder="Receipt or reference" class="h-10 w-full rounded-md border border-[#dbe5de] px-3"></div>
                <div class="flex items-end"><button type="submit" @disabled($accounting['remaining_due'] <= 0.009) class="h-10 w-full rounded-md bg-[#17744a] px-4 text-sm font-medium text-white hover:bg-[#0f5f3b] disabled:cursor-not-allowed disabled:opacity-50">Save repayment</button></div>
            </form>
            @error('loan_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            @error('paid_at')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            @error('note')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        </section>
        <section class="mb-6 overflow-hidden rounded-xl border border-[#e3ebe5] bg-white">
            <h2 class="border-b border-[#e3ebe5] px-5 py-4 font-semibold">Repayments received</h2>
            @if ($payments->isEmpty())<p class="p-5 text-sm text-[#7a9186]">No repayments have been recorded for this loan.</p>@else
                <div class="overflow-x-auto"><table class="w-full min-w-[500px] text-left text-sm"><thead class="text-xs text-[#5b7567]"><tr class="border-b border-[#e3ebe5]"><th class="px-5 py-3">Date received</th><th class="px-5 py-3">Note</th><th class="px-5 py-3 text-right">Amount</th></tr></thead><tbody>@foreach ($payments as $payment)<tr class="border-b border-[#eef3ef] last:border-0"><td class="px-5 py-3">{{ $payment->paid_at->format('M j, Y') }}</td><td class="px-5 py-3">{{ $payment->note ?: '—' }}</td><td class="px-5 py-3 text-right font-medium">₱{{ number_format((float) $payment->amount, 2) }}</td></tr>@endforeach</tbody></table></div>
            @endif
        </section>
        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4"><x-stat-card label="Installments" :value="$loan->term_months" tone="muted" /><x-stat-card label="Principal" :value="'₱'.number_format((float) $loan->principal, 2)" /><x-stat-card label="Total interest" :value="'₱'.number_format((float) $loan->total_interest, 2)" tone="muted" /><x-stat-card label="Total payments" :value="'₱'.number_format((float) $loan->total_payments, 2)" /></div>
        <div class="overflow-hidden rounded-xl border border-[#e3ebe5] bg-white"><div class="overflow-x-auto"><table class="w-full min-w-[900px] text-left text-sm">
            <thead class="text-xs text-[#5b7567]"><tr class="border-b border-[#e3ebe5]"><th class="px-5 py-3">Month</th><th class="px-5 py-3">Due date</th><th class="px-5 py-3 text-right">Installment</th><th class="px-5 py-3 text-right">Paid</th><th class="px-5 py-3 text-right">Installment remaining</th><th class="px-5 py-3 text-right">Penalty</th><th class="px-5 py-3">Status</th></tr></thead>
            <tbody>
                @foreach ($accounting['installments'] as $installment)
                    <tr class="border-b border-[#eef3ef]"><td class="px-5 py-4">{{ $installment['month'] }}</td><td class="px-5 py-4">{{ \Illuminate\Support\Carbon::parse($installment['due_date'])->format('M j, Y') }}</td><td class="px-5 py-4 text-right">₱{{ number_format((float) $installment['payment'], 2) }}</td><td class="px-5 py-4 text-right">₱{{ number_format((float) $installment['paid_amount'], 2) }}</td><td class="px-5 py-4 text-right">₱{{ number_format((float) $installment['remaining_due'], 2) }}</td><td class="px-5 py-4 text-right">₱{{ number_format((float) $installment['late_penalty'], 2) }}</td><td class="px-5 py-4"><x-status-badge :status="$installment['payment_status']" /></td></tr>
                @endforeach
                <tr class="font-semibold"><td class="px-5 py-4" colspan="2">Totals</td><td class="px-5 py-4 text-right">₱{{ number_format((float) $loan->total_payments, 2) }}</td><td class="px-5 py-4 text-right">₱{{ number_format((float) $returned, 2) }}</td><td class="px-5 py-4 text-right">₱{{ number_format((float) $accounting['remaining_due'], 2) }}</td><td class="px-5 py-4 text-right">₱{{ number_format((float) $accounting['late_penalty'], 2) }}</td><td class="px-5 py-4"></td></tr>
            </tbody>
        </table></div></div>
    @endif
@endsection
