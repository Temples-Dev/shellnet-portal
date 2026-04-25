<flux:main>
    <div class="mb-6">
        <flux:heading size="xl">General Settings</flux:heading>
        <flux:subheading>Configure company info, billing, and email</flux:subheading>
    </div>

    <div class="max-w-2xl space-y-6">

        {{-- Company Info --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="sm" class="mb-5">Company Information</flux:heading>

            @if(session('company_saved'))
                <div class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                    Company settings saved.
                </div>
            @endif

            <form wire:submit.prevent="saveCompany" class="space-y-4">
                <flux:field>
                    <flux:label>Company Name <flux:error name="companyName" /></flux:label>
                    <flux:input wire:model="companyName" placeholder="ShellNet ISP" />
                </flux:field>
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Support Email <flux:error name="companyEmail" /></flux:label>
                        <flux:input wire:model="companyEmail" type="email" placeholder="support@example.com" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Phone <flux:error name="companyPhone" /></flux:label>
                        <flux:input wire:model="companyPhone" placeholder="+1 555 000 0000" />
                    </flux:field>
                </div>
                <flux:field>
                    <flux:label>Address <flux:error name="companyAddress" /></flux:label>
                    <flux:textarea wire:model="companyAddress" rows="2" placeholder="123 Main St, City" />
                </flux:field>
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Currency Code <flux:error name="currency" /></flux:label>
                        <flux:input wire:model="currency" placeholder="USD" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Currency Symbol <flux:error name="currencySymbol" /></flux:label>
                        <flux:input wire:model="currencySymbol" placeholder="$" />
                    </flux:field>
                </div>
                <flux:button type="submit" variant="primary">Save Company Settings</flux:button>
            </form>
        </div>

        {{-- SMTP / Mail --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="sm" class="mb-5">Email (SMTP)</flux:heading>

            @if(session('mail_saved'))
                <div class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                    Mail settings saved.
                </div>
            @endif

            <form wire:submit.prevent="saveMail" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>SMTP Host <flux:error name="mailHost" /></flux:label>
                        <flux:input wire:model="mailHost" placeholder="smtp.mailgun.org" />
                    </flux:field>
                    <flux:field>
                        <flux:label>SMTP Port <flux:error name="mailPort" /></flux:label>
                        <flux:input wire:model="mailPort" placeholder="587" />
                    </flux:field>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Username <flux:error name="mailUsername" /></flux:label>
                        <flux:input wire:model="mailUsername" placeholder="user@mailgun.org" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Password (leave blank to keep) <flux:error name="mailPassword" /></flux:label>
                        <flux:input wire:model="mailPassword" type="password" placeholder="••••••••" />
                    </flux:field>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>From Address <flux:error name="mailFromAddress" /></flux:label>
                        <flux:input wire:model="mailFromAddress" type="email" placeholder="noreply@example.com" />
                    </flux:field>
                    <flux:field>
                        <flux:label>From Name <flux:error name="mailFromName" /></flux:label>
                        <flux:input wire:model="mailFromName" placeholder="ShellNet Portal" />
                    </flux:field>
                </div>
                <flux:field>
                    <flux:label>Encryption</flux:label>
                    <flux:select wire:model="mailEncryption">
                        <flux:select.option value="tls">TLS</flux:select.option>
                        <flux:select.option value="ssl">SSL</flux:select.option>
                        <flux:select.option value="starttls">STARTTLS</flux:select.option>
                    </flux:select>
                </flux:field>
                <flux:button type="submit" variant="primary">Save Mail Settings</flux:button>
            </form>
        </div>

    </div>
</flux:main>
