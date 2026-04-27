<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-zinc-950 antialiased">

<flux:sidebar sticky collapsible="mobile"
    class="border-e border-zinc-800 bg-zinc-900 w-60">

    {{-- Brand --}}
    <flux:sidebar.header class="border-b border-zinc-800 px-4 py-4">
        <a href="{{ route('portal.index') }}" class="flex items-center gap-3" wire:navigate>
            <div class="flex size-8 items-center justify-center rounded-lg bg-blue-600">
                <x-app-logo-icon class="size-5 fill-current text-white" />
            </div>
            <div class="leading-tight">
                <p class="text-sm font-bold text-white tracking-tight">ShellNet</p>
                <p class="text-[10px] text-zinc-500 uppercase tracking-widest">Customer Portal</p>
            </div>
        </a>
        <flux:sidebar.collapse class="lg:hidden ml-auto text-zinc-400" />
    </flux:sidebar.header>

    {{-- Nav --}}
    <flux:sidebar.nav class="px-2 py-3 flex-1">
        <flux:sidebar.item
            icon="signal"
            :href="route('portal.index')"
            :current="request()->routeIs('portal.index')"
            wire:navigate
            class="text-zinc-300"
        >
            Browse Plans
        </flux:sidebar.item>

        @auth('customer')
            <flux:sidebar.item
                icon="user-circle"
                :href="route('portal.account')"
                :current="request()->routeIs('portal.account')"
                wire:navigate
                class="text-zinc-300"
            >
                My Account
            </flux:sidebar.item>
            <flux:sidebar.item
                icon="ticket"
                :href="route('portal.redeem')"
                :current="request()->routeIs('portal.redeem')"
                wire:navigate
                class="text-zinc-300"
            >
                Redeem Voucher
            </flux:sidebar.item>
        @endauth
    </flux:sidebar.nav>

    {{-- Footer --}}
    <div class="mt-auto border-t border-zinc-800 px-4 py-4">
        @auth('customer')
            @php
                $sidebarSub = auth('customer')->user()
                    ->subscriptions()
                    ->where('status', 'on')
                    ->latest()
                    ->first();
            @endphp
            <a href="{{ route('portal.account') }}" wire:navigate
                class="mb-3 flex items-center gap-3 rounded-lg px-1 py-1 hover:bg-zinc-800/60 transition-colors group">
                <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-blue-500/15 text-sm font-bold text-blue-400">
                    {{ strtoupper(substr(auth('customer')->user()->fullname, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-white group-hover:text-blue-300 transition-colors">{{ auth('customer')->user()->fullname }}</p>
                    <p class="truncate font-mono text-xs text-zinc-500">{{ auth('customer')->user()->username }}</p>
                    @if($sidebarSub)
                        <p class="mt-0.5 truncate text-xs font-medium text-green-400">{{ $sidebarSub->plan_name }}</p>
                    @else
                        <p class="mt-0.5 text-xs text-zinc-600">No active plan</p>
                    @endif
                </div>
            </a>
            <form method="POST" action="{{ route('portal.logout') }}">
                @csrf
                <button type="submit"
                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Sign Out
                </button>
            </form>
        @else
            <a href="{{ route('portal.login') }}" wire:navigate
                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Sign In
            </a>
        @endauth

        <p class="mt-4 text-center text-[10px] text-zinc-700">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
    </div>
</flux:sidebar>

{{-- Mobile top bar --}}
<flux:header class="lg:hidden border-b border-zinc-800 bg-zinc-900/90 backdrop-blur">
    <flux:sidebar.toggle class="lg:hidden text-zinc-400" icon="bars-3" inset="left" />
    <div class="flex items-center gap-2 ml-3">
        <div class="flex size-6 items-center justify-center rounded bg-blue-600">
            <x-app-logo-icon class="size-3.5 fill-current text-white" />
        </div>
        <span class="text-sm font-semibold text-white">ShellNet</span>
    </div>
    <flux:spacer />
    @auth('customer')
        <a href="{{ route('portal.account') }}" wire:navigate
            class="text-sm text-zinc-400 hover:text-white transition-colors">
            My Account
        </a>
    @else
        <a href="{{ route('portal.login') }}" wire:navigate
            class="text-sm text-zinc-400 hover:text-white transition-colors">
            Sign In
        </a>
    @endauth
</flux:header>

{{-- Main content --}}
<flux:main class="bg-zinc-950 min-h-screen">
    <div class="mx-auto w-full max-w-4xl px-4 py-8 sm:px-6">
        {{ $slot }}
    </div>
</flux:main>

@fluxScripts
</body>
</html>
