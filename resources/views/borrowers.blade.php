@extends('websnippet')
@section('title', 'Borrowers')

@section('content')
    <div class="mb-6"><h1 class="text-2xl font-semibold">Borrowers</h1><p class="mt-1 text-sm text-[#5b7567]">Borrowers are added here when a loan is generated.</p></div>
    <div class="overflow-hidden rounded-xl border border-[#e3ebe5] bg-white"><div class="overflow-x-auto"><table class="w-full min-w-[760px] text-left text-sm">
        <thead class="text-xs text-[#5b7567]"><tr class="border-b border-[#e3ebe5]"><th class="px-5 py-3">Borrower</th><th class="px-5 py-3">Phone</th><th class="px-5 py-3">Address</th><th class="px-5 py-3">Loans</th><th class="px-5 py-3">Latest loan</th><th class="px-5 py-3"></th></tr></thead>
        <tbody>
            @forelse ($borrowers as $borrower)
                @php($latestLoan = $borrower->loans->first())
                <tr class="border-b border-[#eef2ef] last:border-0"><td class="px-5 py-4 font-medium">{{ $borrower->full_name }}</td><td class="px-5 py-4">{{ $borrower->phone ?: '—' }}</td><td class="px-5 py-4">{{ $borrower->address }}</td><td class="px-5 py-4">{{ $borrower->loans_count }}</td><td class="px-5 py-4">{{ $latestLoan?->reference ?? '—' }}</td><td class="px-5 py-4"><a href="{{ route('borrowers.show', $borrower) }}" class="rounded-md border border-[#17744a]/40 px-3 py-1 text-xs font-medium text-[#17744a] hover:bg-[#e3f5ea]">View</a></td></tr>
            @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-[#5b7567]">No borrowers yet. New borrower details are added when you save a loan.</td></tr>
            @endforelse
        </tbody>
    </table></div></div>
@endsection
