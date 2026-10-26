<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>SALUAG - Loan</title>

        <link rel="icon" href="{{ asset('images/logo.jpg') }}" type="image/jpeg">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            /*
             * Put your background photo at public/images/login-bg.jpg
             * (laptop on desk). Falls back to the plain green gradient if missing.
             */
            .brand-panel {
                background-color: #0f4a33;
                background-image:
                    linear-gradient(180deg, rgba(15, 74, 51, .78) 0%, rgba(15, 74, 51, .86) 55%, rgba(8, 48, 33, .95) 100%),
                    url('/images/login-bg.jpg');
                background-size: cover;
                background-position: center;
            }

            @media (max-width: 1023px) {
                #brandPanel { box-sizing: border-box; width: 100%; min-width: 0; }
                #brandPanel .brand-header,
                #brandPanel .brand-hero,
                #brandPanel .brand-footer { min-width: 0; }
                #brandPanel .brand-hero > p,
                #brandPanel .brand-hero h1,
                #brandPanel .brand-hero > div { overflow-wrap: break-word; }
            }

            @media (max-width: 1023px) and (max-height: 700px) {
                #brandPanel { justify-content: space-between; gap: .25rem; padding-block: .75rem; }
                #brandPanel .brand-header img { width: 2.5rem; height: 2.5rem; }
                #brandPanel .brand-header .brand-name { font-size: 1.125rem; }
                #brandPanel .brand-header .brand-description { font-size: .65rem; }
                #brandPanel .brand-hero { gap: .375rem; }
                #brandPanel .brand-hero .brand-eyebrow { font-size: .55rem; letter-spacing: .08em; }
                #brandPanel .brand-hero h1 { font-size: 1.25rem; line-height: 1.05; }
                #brandPanel .brand-hero .brand-copy { font-size: .7rem; line-height: 1rem; }
                #brandPanel .brand-hero .brand-motto { padding: .35rem .6rem; }
                #brandPanel .brand-hero .brand-motto p { font-size: .58rem; line-height: .85rem; }
                #brandPanel .brand-hero .brand-features { gap: .375rem; font-size: .58rem; }
                #brandPanel .brand-hero .brand-features li { gap: .25rem; }
                #brandPanel .brand-hero .brand-features li > span:first-child { width: 1.75rem; height: 1.75rem; }
                #brandPanel .brand-hero .brand-features svg { width: .875rem; height: .875rem; }
                #brandPanel .brand-footer > div { font-size: .62rem; line-height: .9rem; }
                #brandPanel #showSignIn { min-height: 2.5rem; }
            }

            @media (max-width: 1023px) and (max-height: 480px) {
                #brandPanel { height: auto; min-height: 100dvh; overflow-y: visible; justify-content: flex-start; gap: .75rem; }
            }
        </style>
    </head>

    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    @endphp

    <body class="min-h-dvh overflow-x-hidden overflow-y-auto bg-[#f6f9f6] text-[#0f3d2b] antialiased lg:h-dvh lg:overflow-hidden">
        <div class="grid min-h-dvh lg:h-dvh lg:grid-cols-2">

            {{-- ============ LEFT: BRAND PANEL ============ --}}
            <section id="brandPanel" class="brand-panel relative flex min-h-dvh flex-col justify-evenly gap-2 overflow-x-clip px-4 py-4 text-white sm:gap-4 sm:px-8 sm:py-6 lg:h-dvh lg:min-h-0 lg:justify-between lg:gap-0 lg:overflow-y-hidden lg:px-[10%] lg:py-8 xl:px-[12%]">

                {{-- Top logo --}}
                <div class="brand-header flex items-center gap-3">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Saluag logo" class="h-12 w-12 shrink-0 rounded-full bg-white object-contain p-0 sm:h-16 sm:w-16">
                    <div class="leading-tight">
                        <div class="brand-name text-xl font-semibold leading-none sm:text-2xl">SALUAG</div>
                        <div class="brand-description text-xs text-white/70 sm:text-sm">San Luis Agri-Ventures Agriculture Cooperative</div>
                        <div class="brand-description text-xs leading-tight text-white/75 sm:text-sm">Lending Management System</div>
                    </div>
                </div>

                {{-- Hero copy --}}
                <div class="brand-hero flex flex-col gap-2 sm:gap-4 lg:gap-5">
                    <p class="brand-eyebrow text-[10px] font-medium tracking-[.14em] text-[#8fe3b0] sm:text-sm sm:tracking-[.3em]">GROWING TOGETHER, PROGRESSING TOGETHER</p>
                    <h1 class="text-2xl font-semibold leading-[1.08] sm:text-4xl xl:text-[3.1rem]">
                        Manage Loans.<br>
                        <span class="text-[#8fe3b0]">Build a Brighter Future.</span>
                    </h1>
                    <p class="brand-copy max-w-sm text-xs leading-4 text-white/90 sm:text-base sm:leading-relaxed">
                        Supporting organized loan management for a cooperative working toward the progress of its members and agricultural community.
                    </p>

                    <div class="brand-motto max-w-sm rounded-2xl border border-white/20 bg-white/10 px-3 py-2 shadow-lg shadow-black/10 backdrop-blur-sm sm:px-5 sm:py-3">
                        <p class="text-[10px] font-semibold tracking-[.04em] text-[#a8edc1] sm:text-sm sm:tracking-[.08em]">NAGKAKAISANG MIYEMBRO TUNGO SA PAG ASENSO</p>
                        <p class="mt-0.5 text-[10px] text-white/85 sm:mt-1 sm:text-sm">United Members Toward Progress.</p>
                    </div>

                    {{-- Feature highlights --}}
                    <ul class="brand-features grid grid-cols-3 gap-2 text-[10px] leading-tight sm:flex sm:items-start sm:gap-6 sm:text-sm">
                        <li class="flex min-w-0 flex-col gap-1.5 sm:gap-3 sm:pr-6">
                            <span class="grid h-9 w-9 place-items-center rounded-lg bg-white/15 sm:h-11 sm:w-11">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M12 3l8 3v6c0 4.5-3.2 8-8 9-4.8-1-8-4.5-8-9V6l8-3z"/></svg>
                            </span>
                            <span>Secure<br>and Reliable</span>
                        </li>
                        <li class="flex min-w-0 flex-col gap-1.5 border-l border-white/20 pl-2 sm:gap-3 sm:pl-6 sm:pr-6">
                            <span class="grid h-9 w-9 place-items-center rounded-lg bg-white/15 sm:h-11 sm:w-11">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 24 24" fill="currentColor"><rect x="4" y="10" width="4" height="10" rx="1"/><rect x="10" y="4" width="4" height="16" rx="1"/><rect x="16" y="13" width="4" height="7" rx="1"/></svg>
                            </span>
                            <span>Real-time<br>Reports</span>
                        </li>
                        <li class="flex min-w-0 flex-col gap-1.5 border-l border-white/20 pl-2 sm:gap-3 sm:pl-6">
                            <span class="grid h-9 w-9 place-items-center rounded-lg bg-white/15 sm:h-11 sm:w-11">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.6 3.1-6 7-6s7 2.4 7 6H2z"/><circle cx="17.5" cy="9" r="2.5"/><path d="M18 14c2.8.2 5 2 5 5h-5c0-2-.5-3.6-1.5-4.6.5-.2 1-.4 1.5-.4z"/></svg>
                            </span>
                            <span>Easy<br>Borrower Management</span>
                        </li>
                    </ul>
                </div>

                {{-- Mobile footer: keep the tagline and sign-in action together. --}}
                <div class="brand-footer flex flex-col gap-2 sm:gap-3 lg:contents">
                    <div class="border-l-[3px] border-[#6fcf97] pl-3 text-[10px] leading-4 text-white/90 sm:pl-4 sm:text-sm sm:leading-6">
                        Better records today.<br>
                        Stronger communities tomorrow.
                    </div>

                    <button id="showSignIn" type="button" class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-md bg-white px-5 text-sm font-semibold text-[#0f4a33] shadow-lg transition hover:bg-[#eaf5ee] focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-[#0f4a33] lg:hidden">
                        Sign in
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                </div>
            </section>

            {{-- ============ RIGHT: LOGIN ============ --}}
            <main id="sign-in" class="relative hidden min-h-dvh items-start justify-center overflow-y-visible px-4 py-6 sm:items-center sm:px-6 lg:flex lg:h-dvh lg:min-h-0 lg:overflow-hidden">

                {{-- Decorative leaves --}}
                <svg class="pointer-events-none absolute -bottom-16 -right-10 hidden h-[420px] w-[360px] text-[#dcebe2] lg:block" viewBox="0 0 360 420" fill="currentColor" aria-hidden="true">
                    <path d="M90 420C20 380 -10 300 40 230C100 250 140 320 90 420Z" opacity=".7"/>
                    <path d="M300 20C360 90 350 180 280 240C230 180 230 90 300 20Z"/>
                    <path d="M360 230C360 300 320 380 250 420C230 340 260 260 360 230Z" opacity=".8"/>
                </svg>

                <div class="relative w-full max-w-[502px] rounded-xl border border-[#e3ebe5] bg-white/80 p-5 shadow-[0_1px_2px_rgba(15,61,43,.06),0_12px_40px_rgba(15,61,43,.06)] sm:p-9">

                    <button id="backToBrand" type="button" class="mb-4 inline-flex items-center gap-2 text-sm font-medium text-[#17744a] hover:text-[#0f4a33] focus:outline-none focus-visible:underline lg:hidden">
                        <span aria-hidden="true">&larr;</span> Back
                    </button>

                    {{-- Card logo --}}
                    <div class="mb-5 flex items-center gap-3">
                        <img src="{{ asset('images/logo.jpg') }}" alt="Saluag logo" class="h-16 w-16 rounded-full bg-white object-contain p-0">
                        <div class="leading-tight">
                            <div class="text-3xl font-semibold leading-none text-[#0f4a33]">SALUAG</div>
                            <div class="text-xs leading-tight text-[#4a6b5a]">San Luis Agri-Ventures Agriculture Cooperative</div>
                            <div class="text-sm leading-tight text-[#4a6b5a]">Admin Portal</div>
                        </div>
                    </div>

                    <h2 class="text-2xl font-semibold text-[#0f3d2b]">{{ $greeting }},</h2>
                    <p class="mt-3 max-w-xs text-sm leading-6 text-[#5b7567]">
                        Sign in to access your dashboard and manage loan records.
                    </p>

                    <form method="POST" action="{{ route('login') }}" autocomplete="on" class="mt-6 space-y-4">
                        @csrf

                        {{-- Email --}}
                        <div>
                            <label for="email" class="mb-2 block text-sm font-medium">Email Address</label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-[#2d6a4f]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                       placeholder="Enter your email address"
                                       class="h-12 w-full rounded-md border border-[#dbe5de] bg-white pl-12 pr-4 text-sm placeholder:text-[#9db0a5] focus:border-[#1d7a4f] focus:outline-none focus:ring-2 focus:ring-[#1d7a4f]/25">
                            </div>
                            @error('email')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div>
                            <label for="password" class="mb-2 block text-sm font-medium">Password</label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-[#2d6a4f]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 018 0v3"/></svg>
                                <input id="password" name="password" type="password" required autocomplete="current-password"
                                       placeholder="Enter your password"
                                       class="h-12 w-full rounded-md border border-[#dbe5de] bg-white pl-12 pr-12 text-sm placeholder:text-[#9db0a5] focus:border-[#1d7a4f] focus:outline-none focus:ring-2 focus:ring-[#1d7a4f]/25">
                                <button type="button" id="togglePassword" aria-label="Show password"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-[#2d6a4f] hover:text-[#0f4a33] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1d7a4f]/40">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/><path id="eyeSlash" d="M4 4l16 16"/></svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Forgot password --}}
                        <div class="flex justify-end text-sm">
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-[13px] text-[#1d7a4f] hover:underline">Forgot password?</a>
                            @else
                                <a href="#" class="text-[13px] text-[#1d7a4f] hover:underline">Forgot password?</a>
                            @endif
                        </div>

                        {{-- Submit --}}
                        <button type="submit"
                                class="relative flex h-12 w-full items-center justify-center rounded-md bg-[#17744a] text-sm font-medium text-white transition hover:bg-[#0f5f3b] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#17744a] focus-visible:ring-offset-2">
                            Sign In
                            <svg class="absolute right-5 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
                        </button>
                    </form>

                    {{-- Divider --}}
                    <div class="my-6 flex items-center gap-4 text-xs text-[#5b7567]">
                        <span class="h-px flex-1 bg-[#e3ebe5]"></span>
                        or
                        <span class="h-px flex-1 bg-[#e3ebe5]"></span>
                    </div>

                    <p class="flex items-center justify-center gap-2 text-xs text-[#7a9186]">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 018 0v3"/></svg>
                        Authorized personnel only
                    </p>
                </div>
            </main>
        </div>

        <script>
            (() => {
                const brandPanel = document.getElementById('brandPanel');
                const signInPanel = document.getElementById('sign-in');
                const mobileSignIn = document.getElementById('showSignIn');
                const backToBrand = document.getElementById('backToBrand');
                const mobileLayout = window.matchMedia('(max-width: 1023px)');

                function showBrand() {
                    brandPanel.classList.remove('hidden');
                    signInPanel.classList.add('hidden');
                    signInPanel.classList.remove('flex');
                }

                function showLogin() {
                    if (!mobileLayout.matches) return;
                    brandPanel.classList.add('hidden');
                    signInPanel.classList.remove('hidden');
                    signInPanel.classList.add('flex');
                    signInPanel.scrollTop = 0;
                }

                mobileSignIn.addEventListener('click', showLogin);
                backToBrand.addEventListener('click', showBrand);
                mobileLayout.addEventListener('change', event => {
                    if (event.matches) {
                        showBrand();
                    } else {
                        brandPanel.classList.remove('hidden');
                        signInPanel.classList.remove('hidden', 'flex');
                    }
                });

                if (mobileLayout.matches) {
                    showBrand();
                } else {
                    brandPanel.classList.remove('hidden');
                    signInPanel.classList.remove('hidden', 'flex');
                }
            })();

            (function () {
                const input = document.getElementById('password');
                const btn = document.getElementById('togglePassword');
                const slash = document.getElementById('eyeSlash');
                const email = document.getElementById('email');
                const loginForm = email.closest('form');
                let visible = false;

                try {
                    const savedEmail = localStorage.getItem('saluag.remembered-email');
                    if (savedEmail && !email.value) email.value = savedEmail;
                } catch (_) {}

                loginForm.addEventListener('submit', function () {
                    try {
                        if (email.value) {
                            localStorage.setItem('saluag.remembered-email', email.value);
                        } else {
                            localStorage.removeItem('saluag.remembered-email');
                        }
                    } catch (_) {}
                });

                function render() {
                    input.type = visible ? 'text' : 'password';
                    slash.style.display = visible ? 'none' : '';
                    btn.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
                }

                btn.addEventListener('click', function () {
                    visible = !visible;
                    render();
                });
                render();
            })();
        </script>
    </body>
</html>
