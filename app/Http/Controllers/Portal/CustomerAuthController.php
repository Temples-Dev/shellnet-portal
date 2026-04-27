<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerAuthController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'phone'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('customer')->attempt(['phone' => $credentials['phone'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('portal.account'));
        }

        return back()->withErrors(['phone' => 'Phone number or password is incorrect.'])->onlyInput('phone');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'fullname' => ['required', 'string', 'max:100'],
            'phone'    => ['required', 'string', 'max:20', 'unique:customers,phone'],
            'email'    => ['nullable', 'email', 'max:100', 'unique:customers,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $username = $this->generateUsername($data['fullname']);
        $plain    = $data['password'];

        $customer = Customer::create([
            'fullname'       => $data['fullname'],
            'username'       => $username,
            'phone'          => $data['phone'],
            'email'          => $data['email'] ?? null,
            'password'       => Hash::make($plain),
            'pppoe_password' => $plain,
            'status'         => 'Active',
            'service_type'   => 'Hotspot',
            'account_type'   => 'Personal',
        ]);

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->intended(route('portal.account'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('portal.index');
    }

    private function generateUsername(string $fullname): string
    {
        $slug = strtolower(trim($fullname));
        $slug = preg_replace('/\s+/', '.', $slug);
        $slug = preg_replace('/[^a-z0-9.]/', '', $slug);
        $slug = trim($slug, '.');

        if (empty($slug)) {
            $slug = 'user' . substr(md5(uniqid()), 0, 6);
        }

        $username = $slug . '@shellnet';
        $i = 1;

        while (Customer::where('username', $username)->exists()) {
            $username = $slug . $i . '@shellnet';
            $i++;
        }

        return $username;
    }
}
