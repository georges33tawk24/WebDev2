<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/auth/{provider}/redirect', [AuthController::class, 'socialLogin'])
        ->whereIn('provider', ['google', 'facebook'])
        ->name('oauth.redirect');
    Route::get('/auth/{provider}/callback', [AuthController::class, 'socialCallback'])
        ->whereIn('provider', ['google', 'facebook'])
        ->name('oauth.callback');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/2fa', [AuthController::class, 'showTwoFactor'])->name('2fa.verify');
    Route::post('/2fa/method', [AuthController::class, 'chooseTwoFactorMethod'])->name('2fa.method');
    Route::post('/2fa/reset-method', [AuthController::class, 'resetTwoFactorMethod'])->name('2fa.reset-method');
    Route::post('/2fa/defer', [AuthController::class, 'deferTwoFactor'])->name('2fa.defer');
    Route::post('/2fa', [AuthController::class, 'verifyTwoFactor'])->name('2fa.verify.submit');
    Route::post('/2fa/resend', [AuthController::class, 'resendTwoFactor'])->name('2fa.resend');
    Route::get('/account-protected', [AuthController::class, 'showAccountProtected'])->name('account.protected');
});

Route::middleware(['auth', '2fa'])->group(function (): void {
    Route::get('/id-upload', [AuthController::class, 'showIdUpload'])->name('id-upload');
    Route::post('/id-upload', [AuthController::class, 'storeIdUpload'])->name('id-upload.store');

    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])
        ->middleware('role:admin')
        ->name('dashboard.admin');
    Route::get('/dashboard/staff', [DashboardController::class, 'staff'])
        ->middleware('role:office_staff')
        ->name('dashboard.staff');
    Route::get('/dashboard/citizen', [DashboardController::class, 'citizen'])
        ->middleware('role:citizen')
        ->name('dashboard.citizen');
});
