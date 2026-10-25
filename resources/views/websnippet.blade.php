<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - SALUAG</title>
    <link rel="icon" href="{{ asset('images/logo.jpg') }}" type="image/jpeg">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    $nav = [
        ['Dashboard', 'dashboard',        'dashboard', 'dashboard'],
        ['Borrowers', 'borrowers.index',  'users',     'borrowers.*'],
        ['Loans',     'loans.index',      'loans',     'loans.*'],
        ['Payments',  'payments.index',   'payments',  'payments.*'],
        ['Contracts', 'contracts.index',  'contracts', 'contracts.*'],
        ['Reports',   'reports.index',    'reports',   'reports.*'],
        ['Settings',  'settings',         'settings',  'settings'],
    ];
@endphp

<body class="min-h-screen bg-[#f6f9f6] text-[#0f3d2b] antialiased">

    {{-- Sidebar --}}
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-60 -translate-x-full flex-col bg-[#0f4a33] text-white transition-transform lg:translate-x-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-5 py-6">
            <img src="{{ asset('images/logo.jpg') }}" alt="SALUAG logo" class="h-10 w-10 rounded-full bg-white object-contain p-0.5">
            <span class="text-sm font-semibold leading-tight">San Luis Agri-Ventures Cooperative</span>
        </a>

        <nav class="flex-1 space-y-1 px-3" aria-label="Main">
            @foreach ($nav as [$label, $route, $icon, $pattern])
                @php
                    $active = request()->routeIs($pattern);
                    if ($route === 'loans.index' && request()->routeIs('loans.contract')) $active = false;
                    if ($route === 'contracts.index' && request()->routeIs('loans.contract')) $active = true;
                @endphp
                <a href="{{ route($route) }}" @if($active) aria-current="page" @endif
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition {{ $active ? 'bg-white/15 font-medium text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }}">
                    <x-icon :name="$icon" class="h-[18px] w-[18px]" />
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="m-3">
        <a id="sidebarAdmin" href="{{ route('profile') }}" class="flex items-center gap-3 rounded-lg px-3 py-3 hover:bg-white/10 {{ request()->routeIs('profile') ? 'bg-white/15' : '' }}">
            <span class="grid h-9 w-9 place-items-center rounded-full bg-[#6fcf97] text-xs font-semibold text-[#0f4a33]">{{ auth()->user()->initials() }}</span>
            <span class="min-w-0 leading-tight">
                <span class="block truncate text-sm font-medium">{{ auth()->user()->name }}</span>
                <span class="block text-xs text-white/60">Administrator</span>
            </span>
        </a>
        <button type="button" data-confirm-action="logout" class="password-confirm-trigger mt-1 flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-white/75 hover:bg-white/10 hover:text-white">
            <x-icon name="logout" class="h-[18px] w-[18px]" /> Log out
        </button>
        </div>
    </aside>

    <div id="backdrop" class="fixed inset-0 z-30 hidden bg-black/40 lg:hidden"></div>

    <div class="lg:pl-60">
        {{-- Top bar --}}
        <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-[#e3ebe5] bg-white/90 px-4 backdrop-blur sm:px-8">
            <button id="menuBtn" class="rounded-md p-2 text-[#2d6a4f] hover:bg-[#eef5f0] lg:hidden" aria-label="Open menu">
                <x-icon name="menu" />
            </button>

            <form id="globalSearchForm" method="GET" action="{{ route('search.index') }}" class="relative max-w-lg flex-1">
                <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#7a9186]" />
                <label for="globalSearch" class="sr-only">Search borrowers, loans, or names</label>
                <input id="globalSearch" name="q" type="search" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="globalSearchSuggestions" value="{{ request()->routeIs('search.index') ? request('q') : '' }}" placeholder="Search borrowers, loan ID, or name..."
                       class="h-10 w-full rounded-md border border-[#e3ebe5] bg-[#f6f9f6] pl-10 pr-4 text-sm placeholder:text-[#9db0a5] focus:border-[#1d7a4f] focus:outline-none focus:ring-2 focus:ring-[#1d7a4f]/25">
                <div id="globalSearchSuggestions" role="listbox" class="absolute left-0 right-0 top-full z-50 mt-1 hidden max-h-80 overflow-y-auto rounded-lg border border-[#d4ddd6] bg-white py-1 shadow-xl"></div>
            </form>

            <div class="ml-auto flex items-center gap-2">
                <button class="relative rounded-md p-2 text-[#2d6a4f] hover:bg-[#eef5f0]" aria-label="Notifications">
                    <x-icon name="bell" />
                </button>
                <a id="topAdmin" href="{{ route('profile') }}" class="flex max-w-56 items-center gap-2 rounded-md p-1.5 hover:bg-[#eef5f0]" title="Right-click for logout">
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-[#17744a] text-xs font-semibold text-white">{{ auth()->user()->initials() }}</span>
                    <span class="hidden truncate text-sm font-medium sm:block">{{ auth()->user()->name }}</span>
                </a>
                <button type="button" data-confirm-action="logout" class="password-confirm-trigger rounded-md p-2 text-[#2d6a4f] hover:bg-[#eef5f0]" aria-label="Log out" title="Log out">
                    <x-icon name="logout" />
                </button>
            </div>
        </header>

        <div id="adminContextMenu" class="fixed z-50 hidden min-w-36 rounded-md border border-[#e3ebe5] bg-white p-1 shadow-lg">
            <button type="button" data-confirm-action="logout" class="password-confirm-trigger w-full rounded px-3 py-2 text-left text-sm text-red-700 hover:bg-red-50">Log out</button>
        </div>

        <main class="px-4 py-8 sm:px-8">
            @yield('content')
        </main>
    </div>

    <dialog id="passwordConfirmDialog" class="fixed left-1/2 top-1/2 m-0 w-[min(92vw,28rem)] -translate-x-1/2 -translate-y-1/2 rounded-xl border border-[#d4ddd6] p-0 shadow-[0_24px_70px_rgba(15,61,43,0.3)] backdrop:bg-black/40">
        <form id="passwordConfirmForm" method="POST" action="{{ route('logout') }}" data-logout-url="{{ route('logout') }}" data-print-url="{{ route('print.confirm') }}">
            @csrf
            <input type="hidden" name="confirm_action" id="passwordConfirmAction" value="">
            <div class="flex items-start justify-between border-b border-[#eef3ef] px-5 py-4">
                <div><p class="text-xs font-medium uppercase tracking-wide text-[#17744a]">Security check</p><h2 id="passwordConfirmTitle" class="mt-1 text-lg font-semibold text-[#0f3d2b]">Confirm your password</h2></div>
                <button type="button" id="passwordConfirmClose" aria-label="Close" class="rounded-md px-2 py-1 text-xl leading-none text-[#5b7567] hover:bg-[#f3f7f4]">&times;</button>
            </div>
            <div class="px-5 py-5">
                <p id="passwordConfirmMessage" class="mb-4 text-sm text-[#5b7567]">Enter your password to continue.</p>
                <label for="passwordConfirmInput" class="mb-1 block text-sm font-medium text-[#0f3d2b]">Password</label>
                <input id="passwordConfirmInput" name="current_password" type="password" autocomplete="current-password" required class="w-full rounded-md border border-[#b8c9bf] px-3 py-2.5 focus:border-[#17744a] focus:outline-none focus:ring-2 focus:ring-[#17744a]/20">
                @if ($errors->has('current_password') && in_array(old('confirm_action'), ['print', 'logout'], true))
                    <p class="mt-2 text-sm text-red-600">{{ $errors->first('current_password') }}</p>
                @endif
                <button type="submit" id="passwordConfirmSubmit" class="mt-5 w-full rounded-md bg-[#17744a] px-4 py-2.5 text-sm font-medium text-white hover:bg-[#0f5f3b]">Continue</button>
            </div>
        </form>
    </dialog>

    @php
        $shouldRetryPasswordConfirm = in_array(old('confirm_action'), ['print', 'logout'], true);
    @endphp
    <script>
        (() => {
            const dialog = document.getElementById('passwordConfirmDialog');
            const form = document.getElementById('passwordConfirmForm');
            const actionInput = document.getElementById('passwordConfirmAction');
            const passwordInput = document.getElementById('passwordConfirmInput');
            const title = document.getElementById('passwordConfirmTitle');
            const message = document.getElementById('passwordConfirmMessage');
            const submit = document.getElementById('passwordConfirmSubmit');

            function openConfirmation(action) {
                actionInput.value = action;
                const isPrint = action === 'print';
                form.action = isPrint ? form.dataset.printUrl : form.dataset.logoutUrl;
                title.textContent = isPrint ? 'Confirm before printing' : 'Confirm before logging out';
                message.textContent = isPrint ? 'Enter your password to print this document.' : 'Enter your password to log out of SALUAG.';
                submit.textContent = isPrint ? 'Confirm and print' : 'Confirm and log out';
                passwordInput.value = '';
                dialog.showModal();
                passwordInput.focus();
            }

            document.querySelectorAll('.password-confirm-trigger').forEach(button => {
                button.addEventListener('click', () => openConfirmation(button.dataset.confirmAction));
            });
            document.getElementById('passwordConfirmClose').addEventListener('click', () => dialog.close());
            dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
            document.addEventListener('keydown', event => {
                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'p' && !dialog.open) {
                    event.preventDefault();
                    openConfirmation('print');
                }
            });

            const retryAction = @json($shouldRetryPasswordConfirm);
            if (retryAction) {
                actionInput.value = @json(old('confirm_action'));
                form.action = actionInput.value === 'print' ? form.dataset.printUrl : form.dataset.logoutUrl;
                title.textContent = actionInput.value === 'print' ? 'Confirm before printing' : 'Confirm before logging out';
                message.textContent = actionInput.value === 'print' ? 'Enter your password to print this document.' : 'Enter your password to log out of SALUAG.';
                submit.textContent = actionInput.value === 'print' ? 'Confirm and print' : 'Confirm and log out';
                dialog.showModal();
                passwordInput.focus();
            }
        })();
    </script>

    @if (session('print_after_password_confirmation'))
        <script>window.addEventListener('load', () => window.print());</script>
    @endif

    @if (session('welcome_modal'))
        <dialog id="loginWelcomeDialog" class="fixed left-1/2 top-1/2 m-0 w-[min(92vw,28rem)] -translate-x-1/2 -translate-y-1/2 rounded-xl border border-[#d4ddd6] p-0 shadow-[0_24px_70px_rgba(15,61,43,0.3)] backdrop:bg-black/40">
            <div class="flex items-start justify-between border-b border-[#eef3ef] px-5 py-4">
                <div><p class="text-xs font-medium uppercase tracking-wide text-[#17744a]">SALUAG · Loan Management</p><h2 class="mt-1 text-xl font-semibold text-[#0f3d2b]">Welcome, {{ auth()->user()->name }}!</h2></div>
                <button type="button" id="loginWelcomeClose" aria-label="Close welcome message" class="rounded-md px-2 py-1 text-xl leading-none text-[#5b7567] hover:bg-[#f3f7f4]">&times;</button>
            </div>
            <div class="px-5 py-5">
                <p class="text-sm leading-6 text-[#5b7567]">Welcome to San Luis Agri-Ventures Cooperative. You are signed in and ready to manage borrower records, loans, and payments.</p>
            </div>
        </dialog>
        <script>
            (() => {
                const dialog = document.getElementById('loginWelcomeDialog');
                dialog.showModal();
                document.getElementById('loginWelcomeClose').addEventListener('click', () => dialog.close());
                dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
            })();
        </script>
    @endif

    <script>
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('backdrop');
        function toggleMenu(open) {
            sidebar.classList.toggle('-translate-x-full', !open);
            backdrop.classList.toggle('hidden', !open);
        }
        document.getElementById('menuBtn').addEventListener('click', () => toggleMenu(true));
        backdrop.addEventListener('click', () => toggleMenu(false));

        const globalSearchForm = document.getElementById('globalSearchForm');
        const globalSearch = document.getElementById('globalSearch');
        const globalSearchSuggestions = document.getElementById('globalSearchSuggestions');
        let searchDebounce;
        let searchController;
        let activeSuggestion = -1;

        function closeSearchSuggestions() {
            globalSearchSuggestions.classList.add('hidden');
            globalSearch.setAttribute('aria-expanded', 'false');
            activeSuggestion = -1;
        }

        function openSearchSuggestions() {
            globalSearchSuggestions.classList.remove('hidden');
            globalSearch.setAttribute('aria-expanded', 'true');
        }

        function selectSearchSuggestion(index) {
            const options = globalSearchSuggestions.querySelectorAll('[role="option"]');
            options.forEach((option, optionIndex) => {
                option.setAttribute('aria-selected', optionIndex === index ? 'true' : 'false');
                option.classList.toggle('bg-[#f0f7f2]', optionIndex === index);
            });
            activeSuggestion = index;
        }

        globalSearch.addEventListener('input', () => {
            clearTimeout(searchDebounce);
            if (searchController) searchController.abort();
            const term = globalSearch.value.trim();
            if (term.length < 2) {
                globalSearchSuggestions.replaceChildren();
                closeSearchSuggestions();
                return;
            }

            searchDebounce = setTimeout(async () => {
                searchController = new AbortController();
                try {
                    const response = await fetch(`{{ route('search.suggestions') }}?q=${encodeURIComponent(term)}`, {
                        headers: { 'Accept': 'application/json' },
                        signal: searchController.signal,
                    });
                    if (!response.ok) throw new Error('Search suggestions unavailable');
                    const suggestions = await response.json();
                    globalSearchSuggestions.replaceChildren();

                    if (suggestions.length === 0) {
                        const empty = document.createElement('p');
                        empty.className = 'px-4 py-3 text-sm text-[#5b7567]';
                        empty.textContent = 'No matching borrowers or loans';
                        globalSearchSuggestions.append(empty);
                    } else {
                        suggestions.forEach((suggestion, index) => {
                            const option = document.createElement('button');
                            option.type = 'button';
                            option.role = 'option';
                            option.setAttribute('aria-selected', 'false');
                            option.className = 'flex w-full items-center justify-between gap-4 px-4 py-2.5 text-left hover:bg-[#f0f7f2]';
                            const text = document.createElement('span');
                            const label = document.createElement('span');
                            label.className = 'block text-sm font-medium text-[#0f3d2b]';
                            label.textContent = suggestion.label;
                            const description = document.createElement('span');
                            description.className = 'mt-0.5 block truncate text-xs text-[#5b7567]';
                            description.textContent = suggestion.description;
                            text.append(label, description);
                            const type = document.createElement('span');
                            type.className = 'shrink-0 rounded-full bg-[#e7f3eb] px-2 py-1 text-[10px] font-medium uppercase tracking-wide text-[#17744a]';
                            type.textContent = suggestion.type;
                            option.append(text, type);
                            option.addEventListener('click', () => { window.location.assign(suggestion.url); });
                            option.addEventListener('mousemove', () => selectSearchSuggestion(index));
                            globalSearchSuggestions.append(option);
                        });
                    }
                    openSearchSuggestions();
                } catch (error) {
                    if (error.name !== 'AbortError') closeSearchSuggestions();
                }
            }, 180);
        });

        globalSearch.addEventListener('keydown', (event) => {
            const optionCount = globalSearchSuggestions.querySelectorAll('[role="option"]').length;
            if (event.key === 'ArrowDown' && optionCount) {
                event.preventDefault();
                openSearchSuggestions();
                selectSearchSuggestion((activeSuggestion + 1) % optionCount);
            } else if (event.key === 'ArrowUp' && optionCount) {
                event.preventDefault();
                openSearchSuggestions();
                selectSearchSuggestion((activeSuggestion - 1 + optionCount) % optionCount);
            } else if (event.key === 'Enter' && activeSuggestion >= 0) {
                event.preventDefault();
                globalSearchSuggestions.querySelectorAll('[role="option"]')[activeSuggestion]?.click();
            } else if (event.key === 'Escape') {
                closeSearchSuggestions();
            }
        });

        globalSearch.addEventListener('focus', () => {
            if (globalSearchSuggestions.childElementCount && globalSearch.value.trim().length >= 2) openSearchSuggestions();
        });

        document.addEventListener('click', (event) => {
            if (!globalSearchForm.contains(event.target)) closeSearchSuggestions();
        });

        const adminContextMenu = document.getElementById('adminContextMenu');
        function closeAdminContextMenu() {
            adminContextMenu.classList.add('hidden');
        }
        function showAdminContextMenu(event) {
            event.preventDefault();
            adminContextMenu.classList.remove('hidden');
            const bounds = adminContextMenu.getBoundingClientRect();
            adminContextMenu.style.left = `${Math.min(event.clientX, window.innerWidth - bounds.width - 8)}px`;
            adminContextMenu.style.top = `${Math.min(event.clientY, window.innerHeight - bounds.height - 8)}px`;
        }
        document.getElementById('topAdmin').addEventListener('contextmenu', showAdminContextMenu);
        document.getElementById('sidebarAdmin').addEventListener('contextmenu', showAdminContextMenu);
        document.addEventListener('click', closeAdminContextMenu);
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeAdminContextMenu();
        });
    </script>
</body>
</html>
