@extends('websnippet')
@section('title', 'Settings')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-[#0f3d2b]">Settings</h1>
        <p class="mt-1 text-sm text-[#5b7567]">Manage your account and review SALUAG website preferences.</p>
    </div>

    <section class="overflow-hidden rounded-xl border border-[#e3ebe5] bg-white">
        <div class="border-b border-[#e3ebe5] px-6 py-4">
            <h2 class="font-semibold text-[#0f3d2b]">Administrator account</h2>
            <p class="mt-1 text-sm text-[#5b7567]">Your account details and sign-in security.</p>
        </div>
        <div class="divide-y divide-[#eef3ef]">
            <a href="{{ route('profile') }}" class="flex flex-col items-start gap-3 px-4 py-5 hover:bg-[#f8faf8] sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div class="flex min-w-0 items-start gap-3 sm:items-center sm:gap-4">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-[#eef5f0] text-[#17744a]"><x-icon name="users" class="h-5 w-5" /></span>
                    <span class="min-w-0"><span class="block text-sm font-medium text-slate-900">Profile information</span><span class="mt-1 block break-words text-sm text-slate-500">{{ auth()->user()->name }} · {{ auth()->user()->email }}</span></span>
                </div>
                <span class="shrink-0 text-sm font-medium text-[#17744a]">Manage profile <span aria-hidden="true">→</span></span>
            </a>
            <a href="{{ route('profile') }}#password" class="flex flex-col items-start gap-3 px-4 py-5 hover:bg-[#f8faf8] sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div class="flex min-w-0 items-start gap-3 sm:items-center sm:gap-4">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-[#eef5f0] text-[#17744a]"><x-icon name="settings" class="h-5 w-5" /></span>
                    <span class="min-w-0"><span class="block text-sm font-medium text-slate-900">Password &amp; security</span><span class="mt-1 block text-sm text-slate-500">Update your password from your profile page.</span></span>
                </div>
                <span class="shrink-0 text-sm font-medium text-[#17744a]">Manage security <span aria-hidden="true">→</span></span>
            </a>
        </div>
    </section>

    <section class="overflow-hidden rounded-xl border border-[#e3ebe5] bg-white">
        <div class="border-b border-[#e3ebe5] px-6 py-4">
            <h2 class="font-semibold text-[#0f3d2b]">Website preferences</h2>
            <p class="mt-1 text-sm text-[#5b7567]">Current regional and organization details used by the website.</p>
        </div>
        <dl class="divide-y divide-[#eef3ef]">
            <div class="flex flex-wrap items-center justify-between gap-2 px-6 py-4"><dt class="text-sm text-slate-600">Website name</dt><dd class="text-sm font-medium text-slate-900">SALUAG</dd></div>
            <div class="flex flex-wrap items-center justify-between gap-2 px-6 py-4"><dt class="text-sm text-slate-600">Organization</dt><dd class="text-sm font-medium text-slate-900">San Luis Agri-Ventures Agriculture Cooperative</dd></div>
            <div class="flex flex-wrap items-center justify-between gap-2 px-6 py-4"><dt class="text-sm text-slate-600">Time zone</dt><dd class="text-sm font-medium text-slate-900">Asia/Manila (UTC+08:00)</dd></div>
            <div class="flex flex-wrap items-center justify-between gap-2 px-6 py-4"><dt class="text-sm text-slate-600">Currency</dt><dd class="text-sm font-medium text-slate-900">Philippine peso (PHP)</dd></div>
        </dl>
    </section>

    <section class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-[#e3ebe5] bg-white p-6">
        <div class="flex items-center gap-4">
            <img src="{{ asset('images/logo.jpg') }}" alt="SALUAG logo" class="h-12 w-12 rounded-full object-contain">
            <div><h2 class="text-sm font-semibold text-slate-900">SALUAG branding</h2><p class="mt-1 text-sm text-slate-500">This logo appears in the website navigation and loan documents.</p></div>
        </div>
        <span class="rounded-full bg-[#eef5f0] px-3 py-1 text-xs font-medium text-[#17744a]">Active</span>
    </section>
</div>
@endsection
