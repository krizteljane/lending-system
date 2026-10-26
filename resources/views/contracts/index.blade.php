@extends('websnippet')
@section('title', 'Contracts')

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">Contracts</h1>
            <p class="mt-1 text-sm text-[#5b7567]">View and print each borrower’s loan agreement.</p>
        </div>
        <a href="{{ route('contracts.blank') }}" class="rounded-lg border border-[#17744a] px-4 py-2 text-sm font-medium text-[#17744a] hover:bg-[#17744a]/5">Blank contract / Save as PDF</a>
    </div>

    <section class="overflow-hidden rounded-xl border border-[#e3ebe5] bg-white">
        @if ($loans->isEmpty())
            <div class="p-8 text-center"><p class="font-medium text-[#0f3d2b]">No loan contracts yet</p><p class="mt-1 text-sm text-[#5b7567]">Contracts appear here after a loan is approved.</p></div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="bg-[#f6f9f6] text-xs text-[#5b7567]"><tr class="border-b border-[#e3ebe5]"><th class="px-5 py-3">Loan reference</th><th class="px-5 py-3">Borrower</th><th class="px-5 py-3">Principal</th><th class="px-5 py-3">Term</th><th class="px-5 py-3">Release date</th><th class="px-5 py-3"></th></tr></thead>
                    <tbody>
                        @foreach ($loans as $loan)
                            <tr class="border-b border-[#eef2ef] last:border-0">
                                <td class="px-5 py-4 font-medium">{{ $loan->reference }}</td>
                                <td class="px-5 py-4"><button type="button" class="borrower-info-trigger text-left text-[#17744a] hover:underline" data-name="{{ $loan->borrower->full_name }}" data-phone="{{ $loan->borrower->phone ?: 'Not provided' }}" data-email="{{ $loan->borrower->email ?: 'Not provided' }}" data-address="{{ $loan->borrower->address ?: 'Not provided' }}" data-dob="{{ $loan->borrower->date_of_birth?->format('M j, Y') ?: 'Not provided' }}" data-id="{{ $loan->borrower->id_type ?: 'Not provided' }}" data-id-number="{{ $loan->borrower->id_number ?: 'Not provided' }}" data-id-url="{{ $loan->borrower->id_file_path ? route('borrowers.identity', $loan->borrower) : '' }}">{{ $loan->borrower->full_name }}</button></td>
                                <td class="px-5 py-4">₱{{ number_format((float) $loan->principal, 2) }}</td>
                                <td class="px-5 py-4">{{ $loan->term_months }} months</td>
                                <td class="px-5 py-4">{{ $loan->release_date->format('M j, Y') }}</td>
                                <td class="px-5 py-4 text-right"><a href="{{ route('loans.contract', $loan) }}" class="rounded-md bg-[#17744a] px-3 py-2 text-xs font-medium text-white hover:bg-[#0f5f3b]">View agreement</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <dialog id="borrowerInfoDialog" class="fixed left-1/2 top-1/2 m-0 w-[min(92vw,32rem)] -translate-x-1/2 -translate-y-1/2 rounded-xl border border-[#d4ddd6] p-0 shadow-[0_24px_70px_rgba(15,61,43,0.3)] backdrop:bg-black/40">
        <div class="flex items-start justify-between border-b border-[#eef3ef] px-5 py-4">
            <div><p class="text-xs font-medium uppercase tracking-wide text-[#7a9186]">Borrower information</p><h2 id="borrowerInfoName" class="mt-1 text-lg font-semibold text-[#0f3d2b]"></h2></div>
            <button type="button" id="borrowerInfoClose" aria-label="Close" class="rounded-md px-2 py-1 text-xl leading-none text-[#5b7567] hover:bg-[#f3f7f4]">&times;</button>
        </div>
        <dl class="grid grid-cols-1 gap-4 px-5 py-5 text-sm sm:grid-cols-2">
            <div><dt class="text-xs text-[#7a9186]">Phone</dt><dd id="borrowerInfoPhone" class="mt-1 font-medium text-[#0f3d2b]"></dd></div>
            <div><dt class="text-xs text-[#7a9186]">Email</dt><dd id="borrowerInfoEmail" class="mt-1 break-words font-medium text-[#0f3d2b]"></dd></div>
            <div class="sm:col-span-2"><dt class="text-xs text-[#7a9186]">Address</dt><dd id="borrowerInfoAddress" class="mt-1 font-medium text-[#0f3d2b]"></dd></div>
            <div><dt class="text-xs text-[#7a9186]">Date of birth</dt><dd id="borrowerInfoDob" class="mt-1 font-medium text-[#0f3d2b]"></dd></div>
            <div><dt class="text-xs text-[#7a9186]">Valid ID</dt><dd class="mt-1 font-medium text-[#0f3d2b]"><span id="borrowerInfoId"></span> · <span id="borrowerInfoIdNumber"></span></dd></div>
            <div id="borrowerInfoIdFileRow" class="hidden sm:col-span-2"><a id="borrowerInfoIdFile" class="text-[#17744a] underline" href="#" target="_blank" rel="noopener">View ID copy</a></div>
        </dl>
    </dialog>
    <script>
        (() => {
            const dialog = document.getElementById('borrowerInfoDialog');
            const fields = { name: 'borrowerInfoName', phone: 'borrowerInfoPhone', email: 'borrowerInfoEmail', address: 'borrowerInfoAddress', dob: 'borrowerInfoDob', id: 'borrowerInfoId', idNumber: 'borrowerInfoIdNumber' };
            document.querySelectorAll('.borrower-info-trigger').forEach((button) => {
                button.addEventListener('click', () => {
                    Object.entries(fields).forEach(([key, id]) => { document.getElementById(id).textContent = button.dataset[key] || ''; });
                    const idUrl = button.dataset.idUrl;
                    const fileRow = document.getElementById('borrowerInfoIdFileRow');
                    fileRow.classList.toggle('hidden', !idUrl);
                    if (idUrl) document.getElementById('borrowerInfoIdFile').href = idUrl;
                    dialog.showModal();
                });
            });
            document.getElementById('borrowerInfoClose').addEventListener('click', () => dialog.close());
            dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
        })();
    </script>
@endsection
