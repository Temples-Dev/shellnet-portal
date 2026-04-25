<x-layouts.app>
    <flux:main>
        <flux:heading size="xl">{{ __('Messages') }}</flux:heading>
        <flux:subheading>{{ __('Manage messages') }}</flux:subheading>
        <div class="mt-6">
            <flux:card class="p-8 text-center">
                <flux:icon name="message-square-text" class="mx-auto mb-3 size-10 text-zinc-400" />
                <flux:text class="text-zinc-500">{{ __('Messages coming soon.') }}</flux:text>
            </flux:card>
        </div>
    </flux:main>
</x-layouts.app>
