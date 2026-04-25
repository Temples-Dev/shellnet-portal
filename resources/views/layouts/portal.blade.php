<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased">
    <div class="flex min-h-screen flex-col">
        <header class="border-b border-zinc-800 bg-zinc-900">
            <div class="mx-auto flex max-w-4xl items-center justify-between px-4 py-4">
                <a href="{{ route('portal.index') }}" class="flex items-center gap-2">
                    <x-app-logo />
                    <span class="font-semibold text-white">{{ config('app.name') }}</span>
                </a>
                <div class="flex items-center gap-2">
                    @auth('customer')
                        <a href="{{ route('portal.account') }}" class="text-sm text-zinc-400 hover:text-white">My Account</a>
                        <form method="POST" action="{{ route('portal.logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-zinc-400 hover:text-white">Sign Out</button>
                        </form>
                    @else
                        <a href="{{ route('portal.login') }}" class="text-sm text-zinc-400 hover:text-white">Sign In</a>
                    @endauth
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-4xl flex-1 px-4 py-8">
            {{ $slot }}
        </main>

        <footer class="border-t border-zinc-800 py-4 text-center text-xs text-zinc-500">
            &copy; {{ date('Y') }} {{ config('app.name') }} &middot; Powered by ShellNet
        </footer>
    </div>

    @livewireScripts
    @fluxScripts
</body>
</html>
