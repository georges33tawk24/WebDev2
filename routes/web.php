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

    // Admin Routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/offices', [\App\Http\Controllers\Admin\OfficeController::class, 'index'])->name('offices.index');
        Route::get('/offices/create', [\App\Http\Controllers\Admin\OfficeController::class, 'create'])->name('offices.create');
        Route::post('/offices', [\App\Http\Controllers\Admin\OfficeController::class, 'store'])->name('offices.store');
        Route::get('/offices/{office}/edit', [\App\Http\Controllers\Admin\OfficeController::class, 'edit'])->name('offices.edit');
        Route::put('/offices/{office}', [\App\Http\Controllers\Admin\OfficeController::class, 'update'])->name('offices.update');
        Route::delete('/offices/{office}', [\App\Http\Controllers\Admin\OfficeController::class, 'destroy'])->name('offices.destroy');

        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/citizens', [\App\Http\Controllers\Admin\UserController::class, 'citizens'])->name('citizens.index');
        Route::patch('/users/{user}/toggle', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle');
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
        Route::resource('services', \App\Http\Controllers\Admin\ServiceController::class);

        Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    });
        // Staff Routes
 Route::middleware('role:office_staff')->prefix('staff')->name('staff.')->group(function () {
    Route::get('/requests', [\App\Http\Controllers\Staff\RequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/{serviceRequest}', [\App\Http\Controllers\Staff\RequestController::class, 'show'])->name('requests.show');
    Route::patch('/requests/{serviceRequest}/status', [\App\Http\Controllers\Staff\RequestController::class, 'updateStatus'])->name('requests.updateStatus');
    Route::post('/requests/{serviceRequest}/document', [\App\Http\Controllers\Staff\RequestController::class, 'uploadDocument'])->name('requests.uploadDocument');
});
 // Citizen Routes
Route::middleware('role:citizen')->prefix('citizen')->name('citizen.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Citizen\CitizenController::class, 'dashboard'])->name('dashboard');

    Route::get('/services', [\App\Http\Controllers\Citizen\CitizenController::class, 'services'])->name('services');
    Route::get('/services/{service}', [\App\Http\Controllers\Citizen\CitizenController::class, 'showService'])->name('services.show');

    Route::get('/requests', [\App\Http\Controllers\Citizen\CitizenController::class, 'requests'])->name('requests');
    Route::get('/requests/create/{service}', [\App\Http\Controllers\Citizen\CitizenController::class, 'createRequest'])->name('requests.create');
    Route::post('/requests/store', [\App\Http\Controllers\Citizen\CitizenController::class, 'storeRequest'])->name('requests.store');

    Route::get('/payments', [\App\Http\Controllers\Citizen\CitizenController::class, 'payments'])->name('payments');
    Route::get('/payments/{serviceRequest}', [\App\Http\Controllers\Citizen\CitizenController::class, 'paymentPage'])->name('payments.show');
    Route::post('/payments/{serviceRequest}', [\App\Http\Controllers\Citizen\CitizenController::class, 'processPayment'])->name('payments.process');

    Route::get('/maps', [\App\Http\Controllers\Citizen\CitizenController::class, 'maps'])->name('maps');

    Route::get('/appointments', [\App\Http\Controllers\Citizen\CitizenController::class, 'appointments'])->name('appointments');
    Route::get('/appointments/create/{office}', [\App\Http\Controllers\Citizen\CitizenController::class, 'createAppointment'])->name('appointments.create');
    Route::post('/appointments/store', [\App\Http\Controllers\Citizen\CitizenController::class, 'storeAppointment'])->name('appointments.store');

    Route::get('/history', [\App\Http\Controllers\Citizen\CitizenController::class, 'history'])->name('history');
 });
 });

    

