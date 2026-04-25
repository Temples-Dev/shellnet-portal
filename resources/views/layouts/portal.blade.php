<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-zinc-950 antialiased">

    <header class="sticky top-0 z-10 border-b border-zinc-800 bg-zinc-950/90 backdrop-blur">
        <div class="mx-auto flex h-14 max-w-5xl items-center justify-between px-4 sm:px-6">
            <a href="{{ route('portal.index') }}">
                <x-app-logo />
            </a>
            <nav class="flex items-center gap-1">
                @auth('customer')
                    <a href="{{ route('portal.index') }}" class="rounded-lg px-3 py-1.5 text-sm text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors">Plans</a>
                    <a href="{{ route('portal.account') }}" class="rounded-lg px-3 py-1.5 text-sm text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors">My Account</a>
                    <form method="POST" action="{{ route('portal.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="rounded-lg px-3 py-1.5 text-sm text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors">Sign Out</button>
                    </form>
                @else
                    <a href="{{ route('portal.login') }}" class="rounded-lg px-3 py-1.5 text-sm text-zinc-400 hover:bg-zinc-800 hover:text-white transition-colors">Sign In</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto w-full max-w-5xl px-4 py-8 sm:px-6">
        {{ $slot }}
    </main>

    <footer class="mt-8 border-t border-zinc-800/60 py-5">
        <p class="text-center text-xs text-zinc-600">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </footer>

    @fluxScripts
</body>
</html>
