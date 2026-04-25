<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>

                <flux:sidebar.group :heading="__('Customers')" class="grid">
                    <flux:sidebar.item icon="users" :href="route('customers.index')" :current="request()->routeIs('customers.*')" wire:navigate>
                        {{ __('All Customers') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="map-pin" :href="route('customers.map')" :current="request()->routeIs('customers.map')" wire:navigate>
                        {{ __('Map') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Billing')" class="grid">
                    <flux:sidebar.item icon="receipt-text" :href="route('billing.transactions')" :current="request()->routeIs('billing.transactions*')" wire:navigate>
                        {{ __('Transactions') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="ticket" :href="route('billing.vouchers')" :current="request()->routeIs('billing.vouchers*')" wire:navigate>
                        {{ __('Vouchers') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="tag" :href="route('billing.coupons')" :current="request()->routeIs('billing.coupons*')" wire:navigate>
                        {{ __('Coupons') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="credit-card" :href="route('billing.gateways')" :current="request()->routeIs('billing.gateways*')" wire:navigate>
                        {{ __('Payment Gateways') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Services')" class="grid">
                    <flux:sidebar.item icon="wifi" :href="route('services.plans')" :current="request()->routeIs('services.plans*')" wire:navigate>
                        {{ __('Plans') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="gauge" :href="route('services.bandwidth')" :current="request()->routeIs('services.bandwidth*')" wire:navigate>
                        {{ __('Bandwidth') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Network')" class="grid">
                    <flux:sidebar.item icon="router" :href="route('network.routers')" :current="request()->routeIs('network.routers*')" wire:navigate>
                        {{ __('Routers') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="circle-dot" :href="route('network.pools')" :current="request()->routeIs('network.pools*')" wire:navigate>
                        {{ __('IP Pools') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="server" :href="route('network.radius')" :current="request()->routeIs('network.radius*')" wire:navigate>
                        {{ __('RADIUS') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="cable" :href="route('network.odp')" :current="request()->routeIs('network.odp*')" wire:navigate>
                        {{ __('ODPs') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Reports')" class="grid">
                    <flux:sidebar.item icon="chart-bar" :href="route('reports.overview')" :current="request()->routeIs('reports.overview*')" wire:navigate>
                        {{ __('Overview') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="arrow-down-tray" :href="route('reports.export')" :current="request()->routeIs('reports.export*')" wire:navigate>
                        {{ __('Export') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="list-bullet" :href="route('reports.logs')" :current="request()->routeIs('reports.logs*')" wire:navigate>
                        {{ __('Logs') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Admin')" class="grid">
                    <flux:sidebar.item icon="shield-check" :href="route('admin.staff')" :current="request()->routeIs('admin.staff*')" wire:navigate>
                        {{ __('Staff') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="message-square-text" :href="route('admin.messages')" :current="request()->routeIs('admin.messages*')" wire:navigate>
                        {{ __('Messages') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="cog-6-tooth" :href="route('settings.general')" :current="request()->routeIs('settings.*')" wire:navigate>
                        {{ __('Settings') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
