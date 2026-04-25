<flux:main>
    <div class="mb-6">
        <flux:heading size="xl">Payment Gateways</flux:heading>
        <flux:subheading>Configure payment integrations</flux:subheading>
    </div>

    {{-- Paystack Card --}}
    <div class="max-w-2xl space-y-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-[#00C3F7]/10">
                        <span class="text-sm font-bold text-[#00C3F7]">PS</span>
                    </div>
                    <div>
                        <flux:heading size="sm">Paystack</flux:heading>
                        <flux:text class="text-xs text-zinc-400">Accept card & mobile money payments</flux:text>
                    </div>
                </div>
                <flux:checkbox wire:model="paystackEnabled" label="Enabled" />
            </div>

            @if(session('paystack_saved'))
                <div class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                    Settings saved successfully.
                </div>
            @endif

            <form wire:submit.prevent="savePaystack" class="space-y-4">
                <flux:field>
                    <flux:label>Public Key <flux:error name="paystackPublicKey" /></flux:label>
                    <flux:input wire:model="paystackPublicKey" placeholder="pk_live_…" />
                </flux:field>
                <flux:field>
                    <flux:label>Secret Key <flux:error name="paystackSecretKey" /></flux:label>
                    <flux:input wire:model="paystackSecretKey" type="password" placeholder="sk_live_…" />
                </flux:field>
                <div class="rounded-lg bg-zinc-50 p-3 text-xs text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                    Webhook URL (add to your Paystack dashboard):
                    <code class="ml-1 font-mono text-blue-600 dark:text-blue-400">{{ route('payment.paystack.webhook') }}</code>
                </div>
                <flux:button type="submit" variant="primary">Save Paystack Settings</flux:button>
            </form>
        </div>

        {{-- Generate Payment Link --}}
        @if($paystackEnabled && $paystackSecretKey)
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="sm" class="mb-4">Generate Payment Link</flux:heading>
            <form wire:submit.prevent="generateLink" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Customer <flux:error name="linkCustomerId" /></flux:label>
                        <flux:select wire:model="linkCustomerId">
                            <flux:option value="">Select customer…</flux:option>
                            @foreach($this->customers as $c)
                                <flux:option :value="$c->id">{{ $c->fullname }}</flux:option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                    <flux:field>
                        <flux:label>Plan <flux:error name="linkPlanId" /></flux:label>
                        <flux:select wire:model="linkPlanId">
                            <flux:option value="">Select plan…</flux:option>
                            @foreach($this->plans as $p)
                                <flux:option :value="$p->id">{{ $p->name }} — ${{ number_format($p->price, 2) }}</flux:option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>
                <flux:button type="submit" variant="outline" icon="link">Generate Link</flux:button>
            </form>

            @if($generatedUrl)
                <div class="mt-4 rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800">
                    <flux:text class="mb-2 text-xs text-zinc-400">Share this link with the customer:</flux:text>
                    <div class="flex items-center gap-2">
                        <code class="flex-1 truncate rounded bg-zinc-100 px-3 py-2 text-xs text-blue-600 dark:bg-zinc-700 dark:text-blue-400">
                            {{ $generatedUrl }}
                        </code>
                        <flux:button
                            size="sm"
                            variant="ghost"
                            icon="clipboard"
                            x-on:click="navigator.clipboard.writeText('{{ $generatedUrl }}')"
                            title="Copy"
                        />
                    </div>
                </div>
            @endif
        </div>
        @endif
    </div>
</flux:main>
