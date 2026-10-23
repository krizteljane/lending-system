@extends('websnippet')
@section('title', 'Dashboard')

@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
@endphp

@section('content')
    <div class="mb-8 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">{{ $greeting }}, {{ auth()->user()->name }}!</h1>
            <p class="mt-1 text-sm text-[#5b7567]">Dashboard totals include official loans approved with the admin password.</p>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <x-stat-card label="Official Loans" :value="$loanCount" />
        <x-stat-card label="Borrowers" :value="$borrowerCount" />
        <x-stat-card label="Principal Granted" :value="'₱'.number_format($principalTotal, 2)" />
        <x-stat-card label="Total to Be Repaid" :value="'₱'.number_format($repaymentTotal, 2)" />
        <x-stat-card label="Returned to Date" :value="'₱'.number_format($returnedTotal, 2)" />
        <x-stat-card label="Remaining to Collect" :value="'₱'.number_format($remainingTotal, 2)" />
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="rounded-xl border border-[#e3ebe5] bg-white p-6">
            <div class="mb-5"><h2 class="font-semibold">Loan activity</h2><p class="mt-1 text-sm text-[#5b7567]">Principal granted over the past six months.</p></div>
            @php($maxMonthlyPrincipal = max(1, (float) $monthlyAnalytics->max('principal')))
            <div class="flex h-56 items-end gap-3 border-b border-l border-[#dce7df] px-3 pb-0 pt-5 sm:gap-5">
                @foreach ($monthlyAnalytics as $month)
                    <div class="flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-2 text-center">
                        <span class="text-[10px] font-medium text-[#5b7567] sm:text-xs">₱{{ number_format($month['principal'] / 1000, 0) }}k</span>
                        <div class="group relative flex w-full flex-1 items-end justify-center">
                            <div title="{{ $month['label'] }}: ₱{{ number_format($month['principal'], 2) }} across {{ $month['count'] }} loan(s)" class="w-full max-w-12 rounded-t-md bg-[#17744a] transition-colors hover:bg-[#0f5f3b]" style="height: {{ $month['principal'] > 0 ? max(4, $month['principal'] / $maxMonthlyPrincipal * 100) : 2 }}%"></div>
                        </div>
                        <span class="pb-2 text-[10px] text-[#5b7567] sm:text-xs">{{ $month['short_label'] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 flex items-center justify-between text-xs text-[#7a9186]"><span>Columns show principal; labels show ₱ thousands</span><span>Number of loans appears on hover</span></div>
        </section>

        <section class="rounded-xl border border-[#e3ebe5] bg-white p-6">
            <div class="mb-5"><h2 class="font-semibold">Loans by purpose</h2><p class="mt-1 text-sm text-[#5b7567]">Principal granted across official loans.</p></div>
            @if ($purposeAnalytics->isEmpty())
                <p class="py-6 text-center text-sm text-[#7a9186]">Purpose analytics will appear after loans are approved.</p>
            @else
                @php($maxPurposePrincipal = max(1, (float) $purposeAnalytics->max('principal')))
                <div class="space-y-4">
                    @foreach ($purposeAnalytics as $purpose)
                        <div>
                            <div class="mb-1 flex justify-between gap-3 text-sm"><span>{{ $purpose['purpose'] }}</span><span class="shrink-0 font-medium">₱{{ number_format($purpose['principal'], 2) }} <span class="text-xs font-normal text-[#7a9186]">· {{ $purpose['count'] }} loan(s)</span></span></div>
                            <div class="h-2 overflow-hidden rounded-full bg-[#edf3ef]"><div class="h-full rounded-full bg-[#6fcf97]" style="width: {{ max(3, $purpose['principal'] / $maxPurposePrincipal * 100) }}%"></div></div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <section class="mt-6 overflow-hidden rounded-xl border border-[#e3ebe5] bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#e3ebe5] px-6 py-4">
            <div>
                <h2 class="font-semibold">Recent official loans</h2>
                <p class="mt-1 text-sm text-[#5b7567]">Only loans approved by the admin appear here.</p>
            </div>
            <a href="{{ route('loans.index') }}" class="text-sm font-medium text-[#17744a] hover:underline">View all loans</a>
        </div>
        @if ($recentLoans->isEmpty())
            <div class="px-6 py-12 text-center">
                <p class="font-medium text-[#0f3d2b]">No official loans yet</p>
                <p class="mt-1 text-sm text-[#5b7567]">A loan will appear here after its disclosure is reviewed and approved with the admin password.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px] text-left text-sm">
                    <thead class="text-xs text-[#5b7567]"><tr class="border-b border-[#e3ebe5]"><th class="px-6 py-3">Reference</th><th class="px-6 py-3">Borrower</th><th class="px-6 py-3">Principal</th><th class="px-6 py-3">Release date</th><th class="px-6 py-3"></th></tr></thead>
                    <tbody>
                        @foreach ($recentLoans as $loan)
                            <tr class="border-b border-[#eef2ef] last:border-0">
                                <td class="px-6 py-4 font-medium">{{ $loan->reference }}</td>
                                <td class="px-6 py-4">{{ $loan->borrower->full_name }}</td>
                                <td class="px-6 py-4">₱{{ number_format((float) $loan->principal, 2) }}</td>
                                <td class="px-6 py-4">{{ $loan->release_date->format('M j, Y') }}</td>
                                <td class="px-6 py-4"><a href="{{ route('loans.disclosure', ['loan' => $loan, 'return' => 'dashboard']) }}" class="font-medium text-[#17744a] hover:underline">Disclosure</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
