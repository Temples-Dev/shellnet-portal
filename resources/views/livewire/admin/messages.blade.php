<flux:main>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">Messages</flux:heading>
            <flux:subheading>
                {{ $this->messages->total() }} messages
                @if($this->unreadCount > 0)
                    · <span class="text-amber-600 dark:text-amber-400">{{ $this->unreadCount }} unread</span>
                @endif
            </flux:subheading>
        </div>
        <flux:button wire:click="openCompose" icon="pencil-square" variant="primary">Compose</flux:button>
    </div>

    <div class="mb-4 flex flex-wrap gap-3">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search subject or customer…" icon="magnifying-glass" class="flex-1 min-w-48" />
        <flux:checkbox wire:model.live="unreadOnly" label="Unread only" />
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 dark:border-zinc-700/60">
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-400">Customer</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-400">Subject</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-400">From</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-400">Sent</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-400"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700/60">
                    @forelse($this->messages as $msg)
                        <tr
                            class="cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/40 {{ $msg->read_at ? '' : 'bg-blue-50/50 dark:bg-blue-900/10' }}"
                            wire:key="msg-{{ $msg->id }}"
                        >
                            <td class="px-5 py-3">
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $msg->customer?->fullname ?? '—' }}</p>
                                <p class="text-xs text-zinc-400">{{ $msg->customer?->username }}</p>
                            </td>
                            <td class="px-5 py-3 text-zinc-700 dark:text-zinc-300">
                                {{ $msg->subject }}
                                @if(! $msg->read_at)
                                    <flux:badge size="sm" color="blue" class="ml-1">New</flux:badge>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <flux:badge size="sm" :color="$msg->from === 'admin' ? 'purple' : 'zinc'">{{ ucfirst($msg->from ?? 'admin') }}</flux:badge>
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap text-zinc-400">
                                {{ $msg->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-1">
                                    <flux:button size="sm" variant="ghost" icon="eye" wire:click="view({{ $msg->id }})" title="View" />
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $msg->id }})" title="Delete" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-zinc-400">No messages found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-zinc-100 px-5 py-3 dark:border-zinc-700/60">
            {{ $this->messages->links() }}
        </div>
    </div>

    {{-- Compose Modal --}}
    <flux:modal name="message-compose" class="w-full max-w-lg">
        <flux:heading size="lg" class="mb-1">New Message</flux:heading>
        <flux:subheading class="mb-6">Send a message to a customer</flux:subheading>
        <form wire:submit.prevent="send" class="space-y-4">
            <flux:field>
                <flux:label>Customer <flux:error name="composeCustomerId" /></flux:label>
                <flux:select wire:model="composeCustomerId">
                    <flux:select.option value="">Select customer…</flux:select.option>
                    @foreach($this->customers as $c)
                        <flux:select.option :value="$c->id">{{ $c->fullname }} ({{ $c->username }})</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
            <flux:field>
                <flux:label>Subject <flux:error name="composeSubject" /></flux:label>
                <flux:input wire:model="composeSubject" placeholder="Your subscription is expiring…" />
            </flux:field>
            <flux:field>
                <flux:label>Message <flux:error name="composeBody" /></flux:label>
                <flux:textarea wire:model="composeBody" rows="5" placeholder="Write your message here…" />
            </flux:field>
            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary" icon="paper-airplane">Send</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- View Modal --}}
    <flux:modal name="message-view" class="w-full max-w-lg">
        @if($this->viewingMessage)
            <div class="space-y-4">
                <div>
                    <flux:heading size="lg">{{ $this->viewingMessage->subject }}</flux:heading>
                    <div class="mt-1 flex items-center gap-2 text-sm text-zinc-400">
                        <span>To: {{ $this->viewingMessage->customer?->fullname }}</span>
                        <span>·</span>
                        <span>{{ $this->viewingMessage->created_at->format('d M Y H:i') }}</span>
                    </div>
                </div>
                <div class="rounded-lg bg-zinc-50 p-4 text-sm text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 whitespace-pre-wrap">{{ $this->viewingMessage->body }}</div>
                <div class="flex justify-end">
                    <flux:modal.close><flux:button variant="ghost">Close</flux:button></flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>
</flux:main>
