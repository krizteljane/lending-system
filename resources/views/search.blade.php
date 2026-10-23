@extends('websnippet')
@section('title', 'Search')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Search results</h1>
        @if ($term !== '')
            <p class="mt-1 text-sm text-[#5b7567]">Results for “{{ $term }}”</p>
        @else
            <p class="mt-1 text-sm text-[#5b7567]">Enter a borrower name, phone, email, or loan reference in the search bar.</p>
        @endif
    </div>

    @if ($term !== '')
        <section class="mb-6 overflow-hidden rounded-xl border border-[#e3ebe5] bg-white">
            <div class="border-b border-[#e3ebe5] px-5 py-4"><h2 class="font-semibold">Borrowers</h2></div>
            @if ($borrowers->isEmpty())
                <p class="px-5 py-6 text-sm text-[#5b7567]">No matching borrowers.</p>
            @else
                <div class="divide-y divide-[#eef2ef]">
                    @foreach ($borrowers as $borrower)
                        <a href="{{ route('borrowers.show', $borrower) }}" class="flex flex-wrap items-center justify-between gap-2 px-5 py-4 hover:bg-[#f6f9f6]">
                            <span><span class="block font-medium text-[#0f3d2b]">{{ $borrower->full_name }}</span><span class="mt-1 block text-sm text-[#5b7567]">{{ $borrower->phone ?: 'No phone' }} · {{ $borrower->email ?: 'No email' }}</span></span>
                            <span class="text-sm font-medium text-[#17744a]">View borrower</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="overflow-hidden rounded-xl border border-[#e3ebe5] bg-white">
            <div class="border-b border-[#e3ebe5] px-5 py-4"><h2 class="font-semibold">Loans</h2></div>
            @if ($loans->isEmpty())
                <p class="px-5 py-6 text-sm text-[#5b7567]">No matching loans.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left text-sm">
                        <thead class="bg-[#f6f9f6] text-xs text-[#5b7567]"><tr class="border-b border-[#e3ebe5]"><th class="px-5 py-3">Loan reference</th><th class="px-5 py-3">Borrower</th><th class="px-5 py-3">Principal</th><th class="px-5 py-3">Term</th><th class="px-5 py-3"></th></tr></thead>
                        <tbody>
                            @foreach ($loans as $loan)
                                <tr class="border-b border-[#eef2ef] last:border-0"><td class="px-5 py-4 font-medium">{{ $loan->reference }}</td><td class="px-5 py-4">{{ $loan->borrower->full_name }}</td><td class="px-5 py-4">₱{{ number_format((float) $loan->principal, 2) }}</td><td class="px-5 py-4">{{ $loan->term_months }} months</td><td class="px-5 py-4 text-right"><div class="flex items-center justify-end gap-3"><details class="text-left"><summary class="cursor-pointer list-none text-xs font-medium text-[#17744a] hover:underline">Preview</summary><div class="mt-2 w-64 rounded-lg border border-[#d4ddd6] bg-white p-4 text-left shadow-lg"><p class="font-semibold text-[#0f3d2b]">{{ $loan->borrower->full_name }}</p><dl class="mt-3 space-y-2 text-xs"><div class="flex justify-between gap-3"><dt class="text-[#5b7567]">Monthly interest</dt><dd class="font-medium">{{ number_format((float) $loan->monthly_rate, 2) }}%</dd></div><div class="flex justify-between gap-3"><dt class="text-[#5b7567]">Release date</dt><dd class="font-medium">{{ $loan->release_date->format('M j, Y') }}</dd></div><div class="flex justify-between gap-3"><dt class="text-[#5b7567]">Net proceeds</dt><dd class="font-medium">₱{{ number_format((float) $loan->net_proceeds, 2) }}</dd></div><div class="flex justify-between gap-3"><dt class="text-[#5b7567]">Total payments</dt><dd class="font-medium">₱{{ number_format((float) $loan->total_payments, 2) }}</dd></div><div class="border-t border-[#eef2ef] pt-2"><dt class="text-[#5b7567]">Purpose</dt><dd class="mt-1 font-medium">{{ $loan->purpose ?: 'Not specified' }}</dd></div></dl></div></details><a href="{{ route('loans.disclosure', $loan) }}" class="rounded-md border border-[#17744a]/40 px-3 py-2 text-xs font-medium text-[#17744a] hover:bg-[#e3f5ea]">View disclosure</a></div></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endif
@endsection
