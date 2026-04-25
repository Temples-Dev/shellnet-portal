<x-layouts::auth.split :title="__('Welcome')">
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                {{ __('Welcome to ShellNet') }}
            </h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('ISP management platform. Staff access only.') }}
            </p>
        </div>

        <flux:button variant="primary" :href="route('login')" class="w-full">
            {{ __('Sign In') }}
        </flux:button>
    </div>
</x-layouts::auth.split>
