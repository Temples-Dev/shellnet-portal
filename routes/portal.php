<?php

use App\Http\Controllers\Portal\CustomerAuthController;
use App\Http\Controllers\Portal\PortalPaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('portal')->name('portal.')->group(function () {

    // Public
    Route::get('/', fn () => view('pages.portal.index'))->name('index');
    Route::get('/success', fn () => view('pages.portal.success'))->name('success');
    Route::get('/payment/callback', [PortalPaymentController::class, 'callback'])->name('payment.callback');

    // Guest-only auth pages
    Route::middleware('guest:customer')->group(function () {
        Route::get('/login', fn () => view('pages.portal.login'))->name('login');
        Route::post('/login', [CustomerAuthController::class, 'login'])->name('login.store');
        Route::get('/register', fn () => view('pages.portal.register'))->name('register');
        Route::post('/register', [CustomerAuthController::class, 'store'])->name('register.store');
    });

    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');

    // Authenticated customers only
    Route::middleware('auth:customer')->group(function () {
        Route::get('/account', fn () => view('pages.portal.account'))->name('account');
        Route::get('/redeem', fn () => view('pages.portal.redeem'))->name('redeem');
        Route::get('/checkout/{plan}', function (\App\Models\Plan $plan) {
            abort_if(! $plan->enabled, 404);
            return view('pages.portal.checkout', compact('plan'));
        })->name('checkout');
        Route::post('/checkout/{plan}/pay', [PortalPaymentController::class, 'pay'])->name('pay');
        Route::post('/payment/{transaction}/retry', [PortalPaymentController::class, 'retry'])->name('payment.retry');
        Route::post('/payment/{transaction}/resume', [PortalPaymentController::class, 'resume'])->name('payment.resume');
    });
});
