<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Customers
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::view('/', 'pages.customers.index')->name('index');
        Route::view('/map', 'pages.customers.map')->name('map');
        Route::view('/{id}', 'pages.customers.show')->name('show');
    });

    // Billing
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::view('/transactions', 'pages.billing.transactions')->name('transactions');
        Route::view('/vouchers', 'pages.billing.vouchers')->name('vouchers');
        Route::view('/coupons', 'pages.billing.coupons')->name('coupons');
        Route::view('/gateways', 'pages.billing.gateways')->name('gateways');
    });

    // Services
    Route::prefix('services')->name('services.')->group(function () {
        Route::view('/plans', 'pages.services.plans')->name('plans');
        Route::view('/bandwidth', 'pages.services.bandwidth')->name('bandwidth');
    });

    // Network
    Route::prefix('network')->name('network.')->group(function () {
        Route::view('/routers', 'pages.network.routers')->name('routers');
        Route::view('/pools', 'pages.network.pools')->name('pools');
        Route::view('/radius', 'pages.network.radius')->name('radius');
        Route::view('/odp', 'pages.network.odp')->name('odp');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::view('/overview', 'pages.reports.overview')->name('overview');
        Route::view('/export', 'pages.reports.export')->name('export');
        Route::view('/logs', 'pages.reports.logs')->name('logs');
    });

    // Admin
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::view('/staff', 'pages.admin.staff')->name('staff');
        Route::view('/messages', 'pages.admin.messages')->name('messages');
    });

    // Settings
    Route::view('/settings/general', 'pages.settings.general')->name('settings.general');
});

require __DIR__.'/settings.php';
