<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class General extends Component
{
    // Company
    public string $companyName = '';

    public string $companyEmail = '';

    public string $companyPhone = '';

    public string $companyAddress = '';

    public string $currency = 'USD';

    public string $currencySymbol = '$';

    // SMTP
    public string $mailHost = '';

    public string $mailPort = '587';

    public string $mailUsername = '';

    public string $mailPassword = '';

    public string $mailFromAddress = '';

    public string $mailFromName = '';

    public string $mailEncryption = 'tls';

    public function mount(): void
    {
        $s = Setting::all_settings();

        $this->companyName = $s['company_name'] ?? '';
        $this->companyEmail = $s['company_email'] ?? '';
        $this->companyPhone = $s['company_phone'] ?? '';
        $this->companyAddress = $s['company_address'] ?? '';
        $this->currency = $s['currency'] ?? 'USD';
        $this->currencySymbol = $s['currency_symbol'] ?? '$';

        $this->mailHost = $s['mail_host'] ?? '';
        $this->mailPort = $s['mail_port'] ?? '587';
        $this->mailUsername = $s['mail_username'] ?? '';
        $this->mailPassword = '';
        $this->mailFromAddress = $s['mail_from_address'] ?? '';
        $this->mailFromName = $s['mail_from_name'] ?? '';
        $this->mailEncryption = $s['mail_encryption'] ?? 'tls';
    }

    public function saveCompany(): void
    {
        $this->validate([
            'companyName' => 'required|string|max:100',
            'companyEmail' => 'nullable|email|max:100',
            'companyPhone' => 'nullable|string|max:30',
            'companyAddress' => 'nullable|string|max:255',
            'currency' => 'required|string|max:10',
            'currencySymbol' => 'required|string|max:5',
        ]);

        Setting::set('company_name', $this->companyName);
        Setting::set('company_email', $this->companyEmail);
        Setting::set('company_phone', $this->companyPhone);
        Setting::set('company_address', $this->companyAddress);
        Setting::set('currency', $this->currency);
        Setting::set('currency_symbol', $this->currencySymbol);

        session()->flash('company_saved', true);
    }

    public function saveMail(): void
    {
        $this->validate([
            'mailHost' => 'nullable|string|max:100',
            'mailPort' => 'nullable|integer|min:1|max:65535',
            'mailUsername' => 'nullable|string|max:100',
            'mailFromAddress' => 'nullable|email|max:100',
            'mailFromName' => 'nullable|string|max:100',
            'mailEncryption' => 'nullable|in:tls,ssl,starttls',
        ]);

        Setting::set('mail_host', $this->mailHost);
        Setting::set('mail_port', $this->mailPort);
        Setting::set('mail_username', $this->mailUsername);
        Setting::set('mail_from_address', $this->mailFromAddress);
        Setting::set('mail_from_name', $this->mailFromName);
        Setting::set('mail_encryption', $this->mailEncryption);

        if ($this->mailPassword !== '') {
            Setting::set('mail_password', $this->mailPassword);
        }

        config([
            'mail.mailers.smtp.host' => $this->mailHost,
            'mail.mailers.smtp.port' => (int) $this->mailPort,
            'mail.mailers.smtp.username' => $this->mailUsername,
            'mail.mailers.smtp.encryption' => $this->mailEncryption,
            'mail.from.address' => $this->mailFromAddress,
            'mail.from.name' => $this->mailFromName,
        ]);

        $this->mailPassword = '';
        session()->flash('mail_saved', true);
    }

    public function render(): View
    {
        return view('livewire.settings.general');
    }
}
