<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-950 antialiased">
        <div class="flex min-h-screen lg:grid lg:grid-cols-[1fr_480px]">

            {{-- ── Brand panel (desktop only) ───────────────────────────── --}}
            <div class="relative hidden lg:flex lg:flex-col overflow-hidden bg-zinc-950 border-r border-zinc-800/60">

                {{-- Mesh / glow background --}}
                <div class="pointer-events-none absolute inset-0">
                    {{-- Dot grid --}}
                    <svg class="absolute inset-0 h-full w-full opacity-[0.035]" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="dots" width="24" height="24" patternUnits="userSpaceOnUse">
                                <circle cx="1" cy="1" r="1" fill="white"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#dots)"/>
                    </svg>
                    {{-- Blue glow top-right --}}
                    <div class="absolute -top-32 -right-32 h-[500px] w-[500px] rounded-full bg-blue-600/10 blur-3xl"></div>
                    {{-- Violet glow bottom-left --}}
                    <div class="absolute -bottom-32 -left-32 h-[400px] w-[400px] rounded-full bg-violet-600/8 blur-3xl"></div>
                </div>

                {{-- Logo --}}
                <div class="relative z-10 flex items-center gap-3 p-10">
                    <div class="flex size-9 items-center justify-center rounded-lg bg-blue-500/15 ring-1 ring-blue-500/30">
                        <x-app-logo-icon class="size-5 fill-current text-blue-400" />
                    </div>
                    <span class="text-base font-semibold text-white tracking-tight">ShellNet Portal</span>
                </div>

                {{-- Centre copy --}}
                <div class="relative z-10 flex-1 flex flex-col justify-center px-14 pb-10">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-blue-400">ISP Management Platform</p>
                    <h1 class="text-4xl font-bold leading-tight text-white">
                        Everything your<br>network needs,<br>in one place.
                    </h1>
                    <p class="mt-5 text-sm leading-relaxed text-zinc-400 max-w-xs">
                        Manage customers, subscriptions, billing, and your MikroTik routers — without juggling multiple tools.
                    </p>

                    {{-- Feature list --}}
                    <ul class="mt-9 space-y-3.5">
                        @foreach([
                            ['icon' => 'users',          'text' => 'Customer & subscription management'],
                            ['icon' => 'credit-card',    'text' => 'Paystack billing & vouchers'],
                            ['icon' => 'server',         'text' => 'RouterOS provisioning via API'],
                            ['icon' => 'chart-bar',      'text' => 'Revenue reports & activity logs'],
                            ['icon' => 'map-pin',        'text' => 'Customer map with live locations'],
                        ] as $f)
                            <li class="flex items-center gap-3 text-sm text-zinc-300">
                                <span class="flex size-7 shrink-0 items-center justify-center rounded-md bg-zinc-800 ring-1 ring-zinc-700">
                                    <flux:icon :name="$f['icon']" class="size-3.5 text-blue-400" />
                                </span>
                                {{ $f['text'] }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Footer --}}
                <div class="relative z-10 px-10 pb-8 text-xs text-zinc-600">
                    &copy; {{ date('Y') }} ShellNet Portal. All rights reserved.
                </div>
            </div>

            {{-- ── Form panel ────────────────────────────────────────────── --}}
            <div class="flex min-h-screen flex-col items-center justify-center bg-zinc-900 px-6 py-12">

                {{-- Mobile logo --}}
                <a href="{{ route('home') }}" class="mb-8 flex items-center gap-2.5 lg:hidden" wire:navigate>
                    <div class="flex size-8 items-center justify-center rounded-lg bg-blue-500/15 ring-1 ring-blue-500/30">
                        <x-app-logo-icon class="size-4 fill-current text-blue-400" />
                    </div>
                    <span class="text-sm font-semibold text-white">ShellNet Portal</span>
                </a>

                <div class="w-full max-w-sm">
                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
