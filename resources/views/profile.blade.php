@extends('websnippet')
@section('title', 'Admin Profile')

@php
    $input = 'h-11 w-full rounded-md border border-[#dbe5de] bg-white px-3.5 text-sm placeholder:text-[#9db0a5] focus:border-[#1d7a4f] focus:outline-none focus:ring-2 focus:ring-[#1d7a4f]/25';
@endphp

@section('content')
    <h1 class="mb-6 text-2xl font-semibold">Admin Profile</h1>

    <div class="grid max-w-5xl gap-6 lg:grid-cols-3">
        {{-- Identity card --}}
        <section class="rounded-xl border border-[#e3ebe5] bg-white p-6 text-center">
            <span class="mx-auto grid h-20 w-20 place-items-center rounded-full bg-[#17744a] text-2xl font-semibold text-white">{{ auth()->user()->initials() }}</span>
            <h2 class="mt-4 text-lg font-semibold">{{ auth()->user()->name }}</h2>
            <p class="text-sm text-[#5b7567]">Administrator</p>
            <p class="mt-4 text-xs text-[#7a9186]">Last sign in: {{ now()->subHours(3)->format('M j, Y g:i A') }}</p>
        </section>

        <div class="space-y-6 lg:col-span-2">
            {{-- Details --}}
            <form method="POST" action="{{ route('profile.update') }}" class="rounded-xl border border-[#e3ebe5] bg-white p-6">
                @csrf
                @method('PUT')
                <h2 class="font-semibold">Personal Information</h2>
                @if (session('profile_status')) <p class="mt-2 text-sm text-green-700">{{ session('profile_status') }}</p> @endif
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="name" class="mb-1.5 block text-sm font-medium">Display name</label>
                        <input id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required maxlength="255" class="{{ $input }}">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium">Email address</label>
                        <input id="email" type="email" value="{{ auth()->user()->email }}" readonly class="{{ $input }} bg-[#f6f9f6] text-[#5b7567]">
                    </div>
                    <div>
                        <label for="role" class="mb-1.5 block text-sm font-medium">Role</label>
                        <input id="role" value="Administrator" readonly class="{{ $input }} bg-[#f6f9f6] text-[#5b7567]">
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="rounded-md bg-[#17744a] px-5 py-2.5 text-sm font-medium text-white hover:bg-[#0f5f3b]">Save changes</button>
                </div>
            </form>

            {{-- Password --}}
            <form id="password" method="POST" action="{{ route('profile.password') }}" class="rounded-xl border border-[#e3ebe5] bg-white p-6">
                @csrf
                @method('PUT')
                <h2 class="font-semibold">Change Password</h2>
                @if (session('status')) <p class="mt-2 text-sm text-green-700">{{ session('status') }}</p> @endif
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="current_password" class="mb-1.5 block text-sm font-medium">Current password</label>
                        <input id="current_password" type="password" name="current_password" autocomplete="current-password" class="{{ $input }}">
                    </div>
                    <div>
                        <label for="new_password" class="mb-1.5 block text-sm font-medium">New password</label>
                        <input id="new_password" type="password" name="password" minlength="8" autocomplete="new-password" class="{{ $input }}">
                        <p class="mt-1 text-xs text-[#5b7567]">At least 8 characters, including 1 uppercase letter and 1 number.</p>
                        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="confirm_password" class="mb-1.5 block text-sm font-medium">Confirm new password</label>
                        <input id="confirm_password" type="password" name="password_confirmation" autocomplete="new-password" class="{{ $input }}">
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="rounded-md bg-[#17744a] px-5 py-2.5 text-sm font-medium text-white hover:bg-[#0f5f3b]">Update password</button>
                </div>
            </form>
        </div>
    </div>
@endsection
