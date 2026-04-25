<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('landing');
})->name('home');

// Paystack payment routes (no auth required for webhook; callback needs auth)
Route::post('/payment/paystack/webhook', \App\Http\Controllers\PaystackWebhookController::class)
    ->name('payment.paystack.webhook');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/payment/paystack/callback', \App\Http\Controllers\PaystackCallbackController::class)
        ->name('payment.paystack.callback');

    Route::view('dashboard', 'dashboard')->name('dashboard');

    // -----------------------------------------------------------------------
    // Customers — all authenticated staff
    // -----------------------------------------------------------------------
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::view('/', 'pages.customers.index')->name('index');
        Route::view('/map', 'pages.customers.map')->name('map');
        Route::view('/{id}', 'pages.customers.show')->name('show');
    });

    // -----------------------------------------------------------------------
    // Billing — SuperAdmin, Admin, Agent
    // -----------------------------------------------------------------------
    Route::middleware('role:Admin,Agent')->prefix('billing')->name('billing.')->group(function () {
        Route::view('/transactions', 'pages.billing.transactions')->name('transactions');
        Route::view('/vouchers', 'pages.billing.vouchers')->name('vouchers');
        Route::view('/coupons', 'pages.billing.coupons')->name('coupons');
        Route::view('/gateways', 'pages.billing.gateways')->name('gateways');
    });

    // -----------------------------------------------------------------------
    // Services — SuperAdmin, Admin
    // -----------------------------------------------------------------------
    Route::middleware('role:Admin')->prefix('services')->name('services.')->group(function () {
        Route::view('/plans', 'pages.services.plans')->name('plans');
        Route::view('/bandwidth', 'pages.services.bandwidth')->name('bandwidth');
    });

    // -----------------------------------------------------------------------
    // Network — SuperAdmin, Admin
    // -----------------------------------------------------------------------
    Route::middleware('role:Admin')->prefix('network')->name('network.')->group(function () {
        Route::view('/routers', 'pages.network.routers')->name('routers');
        Route::view('/pools', 'pages.network.pools')->name('pools');
        Route::view('/radius', 'pages.network.radius')->name('radius');
        Route::view('/odp', 'pages.network.odp')->name('odp');
    });

    // -----------------------------------------------------------------------
    // Reports — SuperAdmin, Admin, Report
    // -----------------------------------------------------------------------
    Route::middleware('role:Admin,Report')->prefix('reports')->name('reports.')->group(function () {
        Route::view('/overview', 'pages.reports.overview')->name('overview');
        Route::view('/export', 'pages.reports.export')->name('export');
        Route::view('/logs', 'pages.reports.logs')->name('logs');
    });

    // -----------------------------------------------------------------------
    // Admin — SuperAdmin only
    // -----------------------------------------------------------------------
    Route::middleware('role:SuperAdmin')->prefix('admin')->name('admin.')->group(function () {
        Route::view('/staff', 'pages.admin.staff')->name('staff');
        Route::view('/messages', 'pages.admin.messages')->name('messages');
    });

    // -----------------------------------------------------------------------
    // Settings — SuperAdmin only
    // -----------------------------------------------------------------------
    Route::middleware('role:SuperAdmin')->group(function () {
        Route::view('/settings/general', 'pages.settings.general')->name('settings.general');
    });
});

require __DIR__.'/settings.php';
