<?php

use App\Http\Controllers\Portal\CustomerAuthController;
use App\Http\Controllers\Portal\PortalPaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('portal')->name('portal.')->group(function () {

    Route::get('/', fn () => view('pages.portal.index'))->name('index');

    Route::get('/checkout/{plan}', function (\App\Models\Plan $plan) {
        abort_if(! $plan->enabled, 404);
        return view('pages.portal.checkout', compact('plan'));
    })->name('checkout');

    Route::post('/checkout/{plan}/pay', [PortalPaymentController::class, 'pay'])
        ->name('pay');

    Route::get('/payment/callback', [PortalPaymentController::class, 'callback'])
        ->name('payment.callback');

    Route::get('/success', fn () => view('pages.portal.success'))->name('success');

    Route::get('/login', fn () => view('pages.portal.login'))
        ->middleware('guest:customer')
        ->name('login');

    Route::post('/login', [CustomerAuthController::class, 'login'])->name('login.store');
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:customer')->group(function () {
        Route::get('/account', fn () => view('pages.portal.account'))->name('account');
    });
});
