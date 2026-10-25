@extends('websnippet')
@section('title', 'New Loan')

@section('content')
@php($draft = $draft ?? [])
@php($savedDraftId = $savedDraftId ?? null)
@php
    $requiredLoanFields = [
        'full_name', 'address', 'phone', 'date_of_birth', 'id_type', 'id_number',
        'co_borrower_name', 'co_borrower_address', 'co_borrower_phone', 'co_borrower_date_of_birth', 'co_borrower_id_type', 'co_borrower_id_number', 'co_borrower_id_file',
        'second_co_borrower_name', 'second_co_borrower_address', 'second_co_borrower_phone', 'second_co_borrower_date_of_birth', 'second_co_borrower_id_type', 'second_co_borrower_id_number', 'second_co_borrower_id_file',
        'purpose', 'purpose_other', 'amount', 'term', 'start_date', 'monthly_rate', 'calculation_type',
    ];
    if (empty($draft['id_file_path'] ?? null)) {
        $requiredLoanFields[] = 'id_file';
    }
@endphp
<style>
    #loanForm input,
    #loanForm select {
        display: block;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        min-height: 46px;
        padding: .65rem .85rem;
        border: 1px solid #b8c9bf;
        border-radius: .55rem;
        background-color: #fff;
    }

    #loanForm input::placeholder {
        color: #9aa8a0;
        opacity: 1;
    }

    #loanForm select:invalid {
        color: #9aa8a0;
    }

    #loanForm select option {
        color: #374151;
    }

    #loanForm select:invalid option:checked {
        color: #9aa8a0;
    }

    #loanForm [aria-invalid="true"] {
        border-color: #dc2626 !important;
        background-color: #fef2f2 !important;
    }

    #loanForm input[type="date"]:invalid {
        color: #9aa8a0;
    }

    #loanForm input[type="date"]:invalid::-webkit-datetime-edit {
        color: #9aa8a0;
    }

    #loanForm input[type="file"]:invalid {
        color: #9aa8a0;
    }

    #loanForm input[type="file"]::file-selector-button {
        margin-right: .65rem;
        padding: .4rem .7rem;
        border: 1px solid #d1d5db;
        border-radius: .4rem;
        background: #f3f4f6;
        color: #6b7280;
        cursor: pointer;
    }

    #loanForm input:focus,
    #loanForm select:focus {
        border-color: #16804b;
        outline: 3px solid rgb(22 128 75 / 15%);
        outline-offset: 1px;
    }

    #loanForm .space-y-6 > section {
        padding: 1.75rem;
    }

    #loanForm .mt-4.grid {
        margin-top: 1.25rem;
        gap: 1.25rem;
    }

    #loanForm,
    #loanForm > div,
    #loanForm > aside,
    #loanForm section,
    #loanForm .grid > div { min-width: 0; max-width: 100%; }
    #loanForm textarea { box-sizing: border-box; max-width: 100%; min-width: 0; width: 100%; }

    @media (max-width: 639px) {
        #loanForm .space-y-6 > section { padding: 1rem; }
    }
</style>
<div class="mx-auto max-w-7xl">
    <div class="mb-6"><h1 class="text-2xl font-semibold">{{ $savedDraftId ? 'Continue loan draft' : 'New loan' }}</h1><p class="mt-1 text-sm text-[#5b7567]">Fields marked <span class="font-semibold text-red-600">*</span> are required to generate the disclosure. Incomplete applications can still be saved as drafts.</p></div>
    @if (session('success'))<div class="mb-5 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="mb-5 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">{{ session('error') }}</div>@endif
    <form method="POST" action="{{ route('loans.store') }}" enctype="multipart/form-data" class="grid min-w-0 gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(440px,1fr)]" id="loanForm">
        @csrf
        @if ($savedDraftId)<input type="hidden" name="saved_draft_id" value="{{ $savedDraftId }}">@endif
        <div class="min-w-0 space-y-6">
            <div class="relative min-w-0">
            <nav id="loanTabs" class="flex min-w-0 overflow-x-auto border-b border-[#e3ebe5] pr-10" aria-label="New loan sections" role="tablist">
                <button type="button" id="loanTabBorrower" role="tab" aria-controls="loanPanelBorrower" data-loan-tab="borrower" class="loan-form-tab shrink-0 border-b-2 border-[#17744a] px-4 py-3 text-sm font-medium text-[#17744a]">Borrower</button>
                <button type="button" id="loanTabCoBorrower" role="tab" aria-controls="loanPanelCoBorrower" data-loan-tab="co_borrower" class="loan-form-tab shrink-0 border-b-2 border-transparent px-4 py-3 text-sm font-medium text-[#5b7567]">Co-borrower</button>
                <button type="button" id="loanTabSecondCoBorrower" role="tab" aria-controls="loanPanelSecondCoBorrower" data-loan-tab="second_co_borrower" class="loan-form-tab shrink-0 border-b-2 border-transparent px-4 py-3 text-sm font-medium text-[#5b7567]">2nd co-borrower</button>
                <button type="button" id="loanTabTerms" role="tab" aria-controls="loanPanelTerms" data-loan-tab="terms" class="loan-form-tab shrink-0 border-b-2 border-transparent px-4 py-3 text-sm font-medium text-[#5b7567]">Loan terms</button>
                <button type="button" id="loanTabCharges" role="tab" aria-controls="loanPanelCharges" data-loan-tab="charges" class="loan-form-tab shrink-0 border-b-2 border-transparent px-4 py-3 text-sm font-medium text-[#5b7567]">Charges</button>
            </nav>
            <button id="loanTabsScrollRight" type="button" class="absolute right-0 top-0 z-10 flex h-full w-10 items-center justify-center bg-gradient-to-l from-[#f6f9f6] via-[#f6f9f6] to-transparent text-[#17744a] sm:hidden" aria-label="Show more loan tabs">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </button>
            </div>

            <section id="loanPanelBorrower" role="tabpanel" aria-labelledby="loanTabBorrower" data-loan-tab-panel="borrower" class="rounded-xl border border-[#e3ebe5] bg-white p-6">
                <h2 class="font-semibold">Borrower information</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2"><label for="full_name" class="mb-1 block text-sm font-medium">Borrower full name </label><input id="full_name" name="full_name" type="text" value="{{ old('full_name', $draft['full_name'] ?? '') }}" placeholder="Enter name" autocomplete="name" maxlength="150" class="w-full rounded-lg border-gray-300 placeholder:text-gray-400 focus:border-green-600 focus:ring-green-600">@error('full_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                    <div class="sm:col-span-2"><label for="address" class="mb-1 block text-sm font-medium">Complete address </label><input id="address" name="address" type="text" value="{{ old('address', $draft['address'] ?? '') }}" placeholder="Enter complete address" autocomplete="street-address" maxlength="255" class="w-full rounded-lg border-gray-300 placeholder:text-gray-400 focus:border-green-600 focus:ring-green-600">@error('address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                    <div><label for="phone" class="mb-1 block text-sm font-medium">Phone number </label><input id="phone" name="phone" type="tel" value="{{ old('phone', $draft['phone'] ?? '') }}" placeholder="Enter phone number" autocomplete="tel" maxlength="40" class="w-full rounded-lg border-gray-300 placeholder:text-gray-400 focus:border-green-600 focus:ring-green-600">@error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                    <div><label for="email" class="mb-1 block text-sm font-medium">Email (optional)</label><input id="email" name="email" type="email" value="{{ old('email', $draft['email'] ?? '') }}" placeholder="Enter email address" autocomplete="email" class="w-full rounded-lg border-gray-300 placeholder:text-gray-400 focus:border-green-600 focus:ring-green-600">@error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                    <div><label for="date_of_birth" class="mb-1 block text-sm font-medium">Date of birth </label><input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth', $draft['date_of_birth'] ?? '') }}" class="w-full rounded-lg border-gray-300 focus:border-green-600 focus:ring-green-600">@error('date_of_birth')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                    <div><label for="id_type" class="mb-1 block text-sm font-medium">Valid ID type </label><select id="id_type" name="id_type"><option value="">Select ID type</option>@foreach (['PhilID', 'Passport', 'Driver License', 'UMID', 'PRC ID', 'Postal ID', 'Voter ID', 'Other'] as $idType)<option value="{{ $idType }}" @selected(old('id_type', $draft['id_type'] ?? '') === $idType)>{{ $idType }}</option>@endforeach</select>@error('id_type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                    <div><label for="id_number" class="mb-1 block text-sm font-medium">Valid ID number </label><input id="id_number" name="id_number" type="text" maxlength="100" value="{{ old('id_number', $draft['id_number'] ?? '') }}" placeholder="Enter ID number" class="w-full rounded-lg border-gray-300 placeholder:text-gray-400 focus:border-green-600 focus:ring-green-600">@error('id_number')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                    <div><label for="id_file" class="mb-1 block text-sm font-medium">Upload valid ID </label><input id="id_file" name="id_file" type="file" accept=".jpg,.jpeg,.png,.pdf" @if(empty($draft['id_file_path'])) @endif class="w-full rounded-lg border-gray-300 text-sm">@if(!empty($draft['id_file_path']))<p class="mt-1 text-xs text-[#5b7567]">ID file attached to draft.</p>@endif<p class="mt-1 text-xs text-[#5b7567]">JPG, PNG, or PDF; maximum 5 MB. Stored privately.</p>@error('id_file')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                    <div>
                        <label for="purpose" class="mb-1 block text-sm font-medium">Loan purpose </label>
                        <select id="purpose" name="purpose" class="w-full rounded-lg border-gray-300 text-gray-700 focus:border-green-600 focus:ring-green-600">
                            @php($selectedPurpose = old('purpose', $draft['purpose'] ?? ''))
                            <option value="" disabled @selected(!$selectedPurpose)>Select loan purpose</option>
                            @foreach (['Agriculture / farming', 'Business / livelihood', 'Education', 'Medical expenses', 'Home improvement', 'Emergency', 'Other'] as $purposeOption)
                                <option value="{{ $purposeOption }}" @selected($selectedPurpose === $purposeOption)>{{ $purposeOption }}</option>
                            @endforeach
                        </select>
                        @error('purpose')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        <div id="otherPurposeWrap" class="mt-3 {{ $selectedPurpose === 'Other' ? '' : 'hidden' }}">
                            <label for="purpose_other" class="mb-1 block text-sm font-medium">Others: type what the loan is for </label>
                            <input id="purpose_other" name="purpose_other" type="text" value="{{ old('purpose_other', $draft['purpose_other'] ?? '') }}" placeholder="Enter other loan purpose" maxlength="255" class="w-full rounded-lg border-gray-300 placeholder:text-gray-400 focus:border-green-600 focus:ring-green-600">
                            @error('purpose_other')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </section>

            @foreach ([
                    'co_borrower' => 'Co-borrower',
                    'second_co_borrower' => 'Second co-borrower',
                ] as $prefix => $participantLabel)
                    <section id="{{ $prefix === 'co_borrower' ? 'loanPanelCoBorrower' : 'loanPanelSecondCoBorrower' }}" role="tabpanel" aria-labelledby="{{ $prefix === 'co_borrower' ? 'loanTabCoBorrower' : 'loanTabSecondCoBorrower' }}" data-loan-tab-panel="{{ $prefix }}" class="rounded-xl border border-[#e3ebe5] bg-white p-6" hidden>
                        <h2 class="font-semibold">{{ $participantLabel }} information</h2>
                        <p class="mt-1 text-sm text-[#5b7567]">Enter details for this {{ strtolower($participantLabel) }}. You can leave it blank if there is no {{ strtolower($participantLabel) }}.</p>
                    <div class="mt-4 rounded-lg border border-[#eef3ef] p-4">
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div><label for="{{ $prefix }}_name" class="mb-1 block text-xs font-medium">Full name </label><input id="{{ $prefix }}_name" name="{{ $prefix }}_name" type="text" maxlength="150" value="{{ old($prefix.'_name', $draft[$prefix.'_name'] ?? '') }}" placeholder="Enter full name" class="w-full rounded-lg border-gray-300 placeholder:text-gray-400"></div>
                            <div><label for="{{ $prefix }}_address" class="mb-1 block text-xs font-medium">Complete address </label><input id="{{ $prefix }}_address" name="{{ $prefix }}_address" type="text" maxlength="255" value="{{ old($prefix.'_address', $draft[$prefix.'_address'] ?? '') }}" placeholder="Enter complete address" class="w-full rounded-lg border-gray-300 placeholder:text-gray-400"></div>
                            <div><label for="{{ $prefix }}_date_of_birth" class="mb-1 block text-xs font-medium">Date of birth </label><input id="{{ $prefix }}_date_of_birth" name="{{ $prefix }}_date_of_birth" type="date" value="{{ old($prefix.'_date_of_birth', $draft[$prefix.'_date_of_birth'] ?? '') }}" class="w-full rounded-lg border-gray-300"></div>
                            <div><label for="{{ $prefix }}_phone" class="mb-1 block text-xs font-medium">Phone number </label><input id="{{ $prefix }}_phone" name="{{ $prefix }}_phone" type="tel" maxlength="40" value="{{ old($prefix.'_phone', $draft[$prefix.'_phone'] ?? '') }}" placeholder="Enter phone number" class="w-full rounded-lg border-gray-300 placeholder:text-gray-400"></div>
                            <div><label for="{{ $prefix }}_email" class="mb-1 block text-xs font-medium">Email (optional)</label><input id="{{ $prefix }}_email" name="{{ $prefix }}_email" type="email" maxlength="150" value="{{ old($prefix.'_email', $draft[$prefix.'_email'] ?? '') }}" placeholder="Enter email address" class="w-full rounded-lg border-gray-300 placeholder:text-gray-400"></div>
                            <div><label for="{{ $prefix }}_id_type" class="mb-1 block text-xs font-medium">Valid ID type </label><select id="{{ $prefix }}_id_type" name="{{ $prefix }}_id_type"><option value="">Select ID type</option>@foreach (['PhilID', 'Passport', 'Driver License', 'UMID', 'PRC ID', 'Postal ID', 'Voter ID', 'Other'] as $idType)<option value="{{ $idType }}" @selected(old($prefix.'_id_type', $draft[$prefix.'_id_type'] ?? '') === $idType)>{{ $idType }}</option>@endforeach</select></div>
                            <div><label for="{{ $prefix }}_id_number" class="mb-1 block text-xs font-medium">Valid ID number </label><input id="{{ $prefix }}_id_number" name="{{ $prefix }}_id_number" type="text" maxlength="100" value="{{ old($prefix.'_id_number', $draft[$prefix.'_id_number'] ?? '') }}" placeholder="Enter ID number" class="w-full rounded-lg border-gray-300 placeholder:text-gray-400"></div>
                            <div><label for="{{ $prefix }}_id_file" class="mb-1 block text-xs font-medium">Upload valid ID </label><input id="{{ $prefix }}_id_file" name="{{ $prefix }}_id_file" type="file" accept=".jpg,.jpeg,.png,.pdf" @if(empty($draft[$prefix.'_id_file_path'])) @endif class="w-full rounded-lg border-gray-300 text-sm">@if(!empty($draft[$prefix.'_id_file_path']))<p class="mt-1 text-xs text-[#5b7567]">ID file attached to draft.</p>@endif<p class="mt-1 text-xs text-[#5b7567]">JPG, PNG, or PDF; maximum 5 MB.</p></div>
                        </div>
                    </div>
                    </section>
                @endforeach

            <section id="loanPanelTerms" role="tabpanel" aria-labelledby="loanTabTerms" data-loan-tab-panel="terms" class="rounded-xl border border-[#e3ebe5] bg-white p-6" hidden>
                <h2 class="font-semibold">Loan terms</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div><label for="amount" class="mb-1 block text-sm font-medium">Principal loan amount (₱)</label><input id="amount" name="amount" type="number" min="0.01" step="0.01" value="{{ old('amount', $draft['amount'] ?? '') }}" placeholder="Enter loan amount" class="w-full rounded-lg border-gray-300 text-lg placeholder:text-gray-400 focus:border-green-600 focus:ring-green-600"></div>
                    <div><label for="term" class="mb-1 block text-sm font-medium">Loan term (months)</label><input id="term" name="term" type="number" min="1" max="120" value="{{ old('term', $draft['term'] ?? '') }}" placeholder="Enter number of months" class="w-full rounded-lg border-gray-300 placeholder:text-gray-400 focus:border-green-600 focus:ring-green-600"></div>
                    <div><label for="start_date" class="mb-1 block text-sm font-medium">Loan release date</label><input id="start_date" name="start_date" type="date" value="{{ old('start_date', $draft['start_date'] ?? '') }}" class="w-full rounded-lg border-gray-300 focus:border-green-600 focus:ring-green-600"></div>
                    <div><label for="monthly_rate" class="mb-1 block text-sm font-medium">Monthly interest rate (%)</label><input id="monthly_rate" name="monthly_rate" type="number" min="0.01" max="100" step="0.01" value="{{ old('monthly_rate', $draft['monthly_rate'] ?? '') }}" placeholder="Enter percent per month" class="w-full rounded-lg border-gray-300 placeholder:text-gray-400"></div>
                    <div><label for="calculation_type" class="mb-1 block text-sm font-medium">Interest calculation</label><select id="calculation_type" name="calculation_type"><option value="" disabled @selected(old('calculation_type', $draft['calculation_type'] ?? '') === '')>Select calculation method</option><option value="flat" @selected(old('calculation_type', $draft['calculation_type'] ?? '') === 'flat')>Flat</option><option value="diminishing" @selected(old('calculation_type', $draft['calculation_type'] ?? '') === 'diminishing')>Diminishing balance</option></select></div>
                    <div class="sm:col-span-2"><label for="loan_terms" class="mb-1 block text-sm font-medium">Loan terms / special stipulations (optional)</label><textarea id="loan_terms" name="loan_terms" rows="4" maxlength="3000" placeholder="Write any additional terms agreed by the borrower and lender. These will appear in the loan agreement." class="w-full rounded-lg border-gray-300 placeholder:text-gray-400 focus:border-green-600 focus:ring-green-600">{{ old('loan_terms', $draft['loan_terms'] ?? '') }}</textarea>@error('loan_terms')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                </div>
            </section>

            <section id="loanPanelCharges" role="tabpanel" aria-labelledby="loanTabCharges" data-loan-tab-panel="charges" class="rounded-xl border border-[#e3ebe5] bg-white p-6" hidden>
                <h2 class="font-semibold">Other charges / deductions collected</h2>
                <p class="mt-1 text-sm text-[#5b7567]">Enter each charge separately. Leave a field at zero when no charge applies; every item will still appear in the disclosure.</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    @foreach ([
                        'notarial_fee' => 'Notarial fee',
                        'inspection_fee' => 'Inspection fee',
                        'registration_fee' => 'Registration fees',
                        'documentary_stamp_fee' => 'Documentary / science stamps',
                        'service_fee' => 'Service fee',
                    ] as $name => $label)
                        <div><label for="{{ $name }}" class="mb-1 block text-sm font-medium">{{ $label }} (₱)</label><input id="{{ $name }}" name="{{ $name }}" type="number" min="0" step="0.01" value="{{ old($name, $draft[$name] ?? '') }}" placeholder="Enter amount" class="charge-input w-full rounded-lg border-gray-300 placeholder:text-gray-400 focus:border-green-600 focus:ring-green-600"></div>
                    @endforeach
                </div>
            </section>
        </div>

        <aside class="self-start rounded-xl bg-green-900 p-6 text-white xl:sticky xl:top-20 xl:max-h-[calc(100vh-6rem)] xl:overflow-y-auto">
            <h2 class="mb-4 text-lg font-semibold">Disclosure preview</h2>
            <p id="paymentCaption" class="text-sm text-green-200">First monthly payment (decreases)</p><p id="outMonthly" class="mt-1 text-4xl font-semibold tabular-nums">₱0.00</p>
            <dl class="mt-5 grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-green-200">Total interest</dt><dd id="outInterest" class="text-lg font-medium tabular-nums">₱0.00</dd></div>
                <div><dt class="text-green-200">Total payments</dt><dd id="outTotal" class="text-lg font-medium tabular-nums">₱0.00</dd></div>
                <div><dt class="text-green-200">Total deductions</dt><dd id="outCharges" class="text-lg font-medium tabular-nums">₱0.00</dd></div>
                <div><dt class="text-green-200">Net proceeds</dt><dd id="outNet" class="text-lg font-medium tabular-nums">₱0.00</dd></div>
            </dl>
            <div class="mt-5 overflow-hidden rounded-lg bg-white/10"><div class="overflow-x-auto">
                <table class="w-full min-w-[690px] text-sm tabular-nums">
                    <thead class="text-left text-green-200"><tr><th class="px-3 py-2">Month / due date</th><th class="px-3 py-2 text-right">Amortization</th><th class="px-3 py-2 text-right">Interest</th><th class="px-3 py-2 text-right">Principal</th><th class="px-3 py-2 text-right">Balance</th></tr></thead>
                    <tbody id="outRows"><tr><td colspan="5" class="px-3 py-4 text-green-200">Enter loan details for the repayment schedule.</td></tr></tbody>
                </table>
            </div></div>
            <div class="mt-5 rounded-lg bg-white/10 p-4"><h3 class="text-sm font-semibold">Deductions breakdown</h3><div id="feeRows" class="mt-2 space-y-1 text-sm"></div></div>
            <button type="submit" class="mt-6 w-full rounded-lg bg-[#6fcf97] px-4 py-3 font-semibold text-[#0f3d2b] hover:bg-[#81d8a5]">Generate disclosure preview</button>
            <button type="submit" formaction="{{ route('loans.drafts.store') }}" formnovalidate class="mt-3 w-full rounded-lg border border-white/50 px-4 py-3 font-semibold text-white hover:bg-white/10">Save draft</button>
            <p class="mt-3 text-xs text-green-200">A draft is saved privately and can be continued later. It does not create an official loan or borrower record.</p>
        </aside>
    </form>
</div>

<script>
(function () {
    const $ = id => document.getElementById(id);
    const loanTabs = Array.from(document.querySelectorAll('[data-loan-tab]'));
    const loanPanels = Array.from(document.querySelectorAll('[data-loan-tab-panel]'));
    const loanTabsNav = $('loanTabs');
    const loanTabsScrollRight = $('loanTabsScrollRight');

    const requiredFields = @json($requiredLoanFields);
    requiredFields.forEach(name => {
        const label = document.querySelector(`#loanForm label[for="${name}"]`);
        if (!label || label.querySelector('.required-marker')) return;
        const marker = document.createElement('span');
        marker.className = 'required-marker ml-1 font-semibold text-red-600';
        marker.textContent = '*';
        marker.setAttribute('aria-hidden', 'true');
        label.append(marker);
    });

    function updateLoanTabsCue() {
        if (!loanTabsNav || !loanTabsScrollRight) return;
        const hasMoreTabs = loanTabsNav.scrollWidth > loanTabsNav.clientWidth + 1;
        const atEnd = loanTabsNav.scrollLeft + loanTabsNav.clientWidth >= loanTabsNav.scrollWidth - 2;
        loanTabsScrollRight.classList.toggle('hidden', !hasMoreTabs || atEnd);
    }
    loanTabsScrollRight?.addEventListener('click', () => {
        loanTabsNav.scrollBy({ left: Math.max(120, loanTabsNav.clientWidth * 0.7), behavior: 'smooth' });
    });
    loanTabsNav?.addEventListener('scroll', updateLoanTabsCue, { passive: true });
    window.addEventListener('resize', updateLoanTabsCue);
    requestAnimationFrame(updateLoanTabsCue);

    function activateLoanTab(name) {
        loanTabs.forEach(tab => {
            const active = tab.dataset.loanTab === name;
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
            tab.tabIndex = active ? 0 : -1;
            tab.classList.toggle('border-[#17744a]', active);
            tab.classList.toggle('text-[#17744a]', active);
            tab.classList.toggle('border-transparent', !active);
            tab.classList.toggle('text-[#5b7567]', !active);
        });
        loanPanels.forEach(panel => { panel.hidden = panel.dataset.loanTabPanel !== name; });
    }
    loanTabs.forEach(tab => tab.addEventListener('click', () => activateLoanTab(tab.dataset.loanTab)));
    loanTabs.forEach((tab, index) => tab.addEventListener('keydown', event => {
        if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
        event.preventDefault();
        const nextIndex = event.key === 'Home' ? 0 : event.key === 'End' ? loanTabs.length - 1 : (index + (event.key === 'ArrowRight' ? 1 : -1) + loanTabs.length) % loanTabs.length;
        loanTabs[nextIndex].focus();
        activateLoanTab(loanTabs[nextIndex].dataset.loanTab);
    }));
    const validationErrors = @json($errors->keys());
    activateLoanTab('borrower');
    if (validationErrors.length) {
        validationErrors.forEach(name => {
            const field = Array.from($('loanForm').elements).find(element => element.name === name);
            if (field) field.setAttribute('aria-invalid', 'true');
        });
        const firstError = validationErrors[0];
        const errorTab = firstError.startsWith('second_co_borrower_') ? 'second_co_borrower'
            : firstError.startsWith('co_borrower_') ? 'co_borrower'
            : ['amount', 'term', 'start_date', 'monthly_rate', 'calculation_type', 'loan_terms'].includes(firstError) ? 'terms'
            : ['notarial_fee', 'inspection_fee', 'registration_fee', 'documentary_stamp_fee', 'service_fee'].includes(firstError) ? 'charges'
            : 'borrower';
        activateLoanTab(errorTab);
    }
    const purposeSelect = $('purpose');
    const otherPurposeWrap = $('otherPurposeWrap');
    const otherPurposeInput = $('purpose_other');
    function toggleOtherPurpose() {
        const isOther = purposeSelect.value === 'Other';
        otherPurposeWrap.classList.toggle('hidden', !isOther);
        
        if (!isOther) otherPurposeInput.value = '';
    }
    purposeSelect.addEventListener('change', toggleOtherPurpose);
    toggleOtherPurpose();
    const peso = n => '₱' + n.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const round2 = n => Math.round((n + Number.EPSILON) * 100) / 100;
    const feeFields = [
        ['notarial_fee', 'Notarial fee'], ['inspection_fee', 'Inspection fee'], ['registration_fee', 'Registration fees'],
        ['documentary_stamp_fee', 'Documentary / science stamps'], ['service_fee', 'Service fee'],
    ];
    function addMonths(dateText, months) {
        const date = new Date(dateText + 'T00:00:00'), day = date.getDate();
        date.setDate(1); date.setMonth(date.getMonth() + months);
        date.setDate(Math.min(day, new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate()));
        return date.toLocaleDateString('en-PH', {year: 'numeric', month: 'short', day: 'numeric'});
    }

    function compute() {
        const amount = Math.max(0, parseFloat($('amount').value) || 0);
        const enteredTerm = parseInt($('term').value, 10) || 0;
        const term = Math.min(120, Math.max(0, enteredTerm));
        const monthlyRate = parseFloat($('monthly_rate').value) || 0;
        const calculationType = $('calculation_type').value;
        const startDate = $('start_date').value;
        const hasCompleteLoanInputs = amount > 0 && term > 0 && monthlyRate > 0 && Boolean(startDate) && Boolean(calculationType);
        const rate = monthlyRate / 100;
        const baseFees = feeFields.reduce((sum, [id]) => sum + (parseFloat($(id).value) || 0), 0);
        const feeTotal = round2(baseFees);
        const regularPrincipal = term > 0 ? round2(amount / term) : 0;
        let balance = amount, totalInterest = 0, totalPayments = 0;
        const rows = [];

        for (let month = 1; hasCompleteLoanInputs && month <= term; month++) {
            const interest = round2((calculationType === 'flat' ? amount : balance) * rate);
            const principal = month === term ? balance : Math.min(regularPrincipal, balance);
            const payment = round2(principal + interest);
            balance = round2(Math.max(0, balance - principal));
            totalInterest = round2(totalInterest + interest);
            totalPayments = round2(totalPayments + payment);
            rows.push({month, due: addMonths(startDate, month), payment, interest, principal, balance});
        }

        $('paymentCaption').textContent = calculationType === 'flat' ? 'Monthly payment (mostly level)' : 'First monthly payment (decreases)';
        $('outMonthly').textContent = peso(amount && rows.length ? rows[0].payment : 0);
        $('outInterest').textContent = peso(amount ? totalInterest : 0);
        $('outTotal').textContent = peso(amount ? totalPayments : 0);
        $('outCharges').textContent = peso(feeTotal);
        $('outNet').textContent = peso(Math.max(0, amount - feeTotal));
        $('outRows').innerHTML = rows.length ? rows.map(row => `<tr class="border-t border-white/10"><td class="px-3 py-2">${row.month} · ${row.due}</td><td class="px-3 py-2 text-right">${peso(row.payment)}</td><td class="px-3 py-2 text-right">${peso(row.interest)}</td><td class="px-3 py-2 text-right">${peso(row.principal)}</td><td class="px-3 py-2 text-right">${peso(row.balance)}</td></tr>`).join('') : '<tr><td colspan="5" class="px-3 py-4 text-green-200">Enter loan details for the repayment schedule.</td></tr>';
        const baseFeeRows = feeFields.map(([id, label]) => `<div class="flex justify-between gap-3"><span>${label}</span><span>${peso(parseFloat($(id).value) || 0)}</span></div>`).join('');
        $('feeRows').innerHTML = baseFeeRows + `<div class="mt-2 flex justify-between border-t border-white/20 pt-2 font-semibold"><span>Total charges</span><span>${peso(feeTotal)}</span></div>`;
    }

    $('loanForm').addEventListener('input', compute);
    $('loanForm').addEventListener('change', compute);
    compute();
})();
</script>
@endsection
