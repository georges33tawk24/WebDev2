<?php

use App\Http\Controllers\Api\ChatMessageController;
use App\Http\Controllers\Api\IdDocumentController;
use App\Http\Controllers\Api\LiveUpdateController;
use App\Http\Controllers\Api\NotificationController as ApiNotificationController;
use App\Http\Controllers\Api\PushSubscriptionController;
use App\Http\Controllers\Citizen\PaymentController;
use App\Http\Controllers\Webhooks\NowPaymentsWebhookController;
use App\Http\Controllers\Webhooks\StripeWebhookController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\TrackController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])
    ->whereIn('locale', ['en', 'ar'])
    ->name('locale.switch');

Route::get('/track/{token}', [TrackController::class, 'show'])->name('track.show');

Route::get('/', function () {
    return redirect()->route(AuthController::homeRouteFor(Auth::user()));
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
    Route::post('/register/id-preview', [IdDocumentController::class, 'parse'])
        ->middleware('throttle:10,1')
        ->name('register.id-preview');
    Route::get('/auth/{provider}/redirect', [AuthController::class, 'socialLogin'])
        ->whereIn('provider', ['google', 'facebook'])
        ->name('oauth.redirect');
    Route::get('/auth/{provider}/callback', [AuthController::class, 'socialCallback'])
        ->whereIn('provider', ['google', 'facebook'])
        ->name('oauth.callback');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/2fa/email', [AuthController::class, 'showCollectEmailForTwoFactor'])->name('2fa.collect-email');
    Route::post('/2fa/email', [AuthController::class, 'storeCollectEmailForTwoFactor'])->name('2fa.collect-email.store');
    Route::get('/2fa/phone', [AuthController::class, 'showCollectPhoneForTwoFactor'])->name('2fa.collect-phone');
    Route::post('/2fa/phone', [AuthController::class, 'storeCollectPhoneForTwoFactor'])->name('2fa.collect-phone.store');
    Route::get('/2fa', [AuthController::class, 'showTwoFactor'])->name('2fa.verify');
    Route::post('/2fa/channel', [AuthController::class, 'chooseTwoFactorChannel'])->name('2fa.channel');
    Route::post('/2fa/change-method', [AuthController::class, 'changeTwoFactorChannel'])->name('2fa.change-method');
    Route::post('/2fa', [AuthController::class, 'verifyTwoFactor'])->name('2fa.verify.submit');
    Route::post('/2fa/resend', [AuthController::class, 'resendTwoFactor'])->name('2fa.resend');
    Route::get('/account-protected', [AuthController::class, 'showAccountProtected'])->name('account.protected');
});

Route::post('/webhooks/stripe', StripeWebhookController::class)->name('webhooks.stripe');
Route::post('/webhooks/nowpayments', NowPaymentsWebhookController::class)->name('webhooks.nowpayments');

Route::middleware(['auth', '2fa', 'citizen.id'])->group(function (): void {
    Route::post('/api/id-document/parse', [IdDocumentController::class, 'parse'])
        ->middleware('throttle:30,1')
        ->name('api.id-document.parse');

    Route::get('/api/live/stream', [LiveUpdateController::class, 'stream'])->name('api.live.stream');
    Route::get('/api/live/snapshot', [LiveUpdateController::class, 'snapshot'])->name('api.live.snapshot');

    Route::get('/api/notifications', [ApiNotificationController::class, 'index'])->name('api.notifications.index');
    Route::post('/api/notifications/read-all', [ApiNotificationController::class, 'markAllRead'])->name('api.notifications.read-all');
    Route::post('/api/notifications/{notification}/read', [ApiNotificationController::class, 'markRead'])->name('api.notifications.read');

    Route::get('/api/push/vapid-public-key', [PushSubscriptionController::class, 'publicKey'])->name('api.push.public-key');
    Route::post('/api/push/subscribe', [PushSubscriptionController::class, 'store'])->name('api.push.subscribe');
    Route::post('/api/push/unsubscribe', [PushSubscriptionController::class, 'destroy'])->name('api.push.unsubscribe');

    Route::scopeBindings()->group(function (): void {
        Route::get('/api/chat/requests/{serviceRequest}/messages', [ChatMessageController::class, 'index'])
            ->name('api.chat.messages.index');
        Route::post('/api/chat/requests/{serviceRequest}/messages', [ChatMessageController::class, 'store'])
            ->name('api.chat.messages.store');
    });

    Route::get('/id-upload', [AuthController::class, 'showIdUpload'])->name('id-upload');
    Route::post('/id-upload', [AuthController::class, 'storeIdUpload'])
        ->middleware('throttle:10,1')
        ->name('id-upload.store');

    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])
        ->middleware('role:admin')
        ->name('dashboard.admin');
    Route::get('/dashboard/staff', [DashboardController::class, 'staff'])
        ->middleware('role:office_staff')
        ->name('dashboard.staff');
    Route::get('/dashboard/citizen', fn () => redirect()->route('citizen.dashboard'))
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

        Route::get('/requests', [\App\Http\Controllers\Admin\RequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/{serviceRequest}', [\App\Http\Controllers\Admin\RequestController::class, 'show'])->name('requests.show');

        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/staff/create', [\App\Http\Controllers\Admin\UserController::class, 'createStaff'])->name('users.staff.create');
        Route::post('/users/staff', [\App\Http\Controllers\Admin\UserController::class, 'storeStaff'])->name('users.staff.store');
        Route::get('/citizens', [\App\Http\Controllers\Admin\UserController::class, 'citizens'])->name('citizens.index');
        Route::get('/users/citizens/create', [\App\Http\Controllers\Admin\UserController::class, 'createCitizen'])->name('users.citizens.create');
        Route::post('/users/citizens', [\App\Http\Controllers\Admin\UserController::class, 'storeCitizen'])->name('users.citizens.store');
        Route::patch('/users/{user}/toggle', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle');
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
        Route::resource('services', \App\Http\Controllers\Admin\ServiceController::class);

        Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    });

    // Staff Routes
    Route::middleware('role:office_staff')->prefix('staff')->name('staff.')->group(function () {
        Route::get('/requests', [\App\Http\Controllers\Staff\RequestController::class, 'index'])->name('requests.index');

        Route::scopeBindings()->group(function () {
            Route::get('/requests/{serviceRequest}', [\App\Http\Controllers\Staff\RequestController::class, 'show'])->name('requests.show');
            Route::patch('/requests/{serviceRequest}/status', [\App\Http\Controllers\Staff\RequestController::class, 'updateStatus'])->name('requests.updateStatus');
            Route::post('/requests/{serviceRequest}/document', [\App\Http\Controllers\Staff\RequestController::class, 'uploadDocument'])->name('requests.uploadDocument');
            Route::get('/requests/{serviceRequest}/documents/{document}/download', [\App\Http\Controllers\Staff\RequestController::class, 'downloadDocument'])->name('requests.documents.download');
        });

        Route::get('/chats', [\App\Http\Controllers\Staff\ChatController::class, 'index'])->name('chats.index');
        Route::scopeBindings()->group(function () {
            Route::get('/chats/{serviceRequest}', [\App\Http\Controllers\Staff\ChatController::class, 'show'])->name('chats.show');
            Route::post('/chats/{serviceRequest}', [\App\Http\Controllers\Staff\ChatController::class, 'sendMessage'])->name('chats.send');
        });

        Route::get('/appointments', [\App\Http\Controllers\Staff\AppointmentController::class, 'index'])->name('appointments.index');
        Route::patch('/appointments/{appointment}/status', [\App\Http\Controllers\Staff\AppointmentController::class, 'updateStatus'])->name('appointments.updateStatus');

        Route::get('/office', [\App\Http\Controllers\Staff\OfficeProfileController::class, 'edit'])->name('office.edit');
        Route::put('/office', [\App\Http\Controllers\Staff\OfficeProfileController::class, 'update'])->name('office.update');
        Route::get('/feedback', [\App\Http\Controllers\Staff\FeedbackController::class, 'index'])->name('feedback.index');
        Route::post('/feedback/{feedback}/reply', [\App\Http\Controllers\Staff\FeedbackController::class, 'reply'])->name('feedback.reply');
        Route::resource('categories', \App\Http\Controllers\Staff\CategoryController::class);
        Route::resource('services', \App\Http\Controllers\Staff\ServiceController::class);
    });

    // Citizen portal
    Route::middleware('role:citizen')->prefix('citizen')->name('citizen.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Citizen\CitizenController::class, 'dashboard'])->name('dashboard');

        Route::get('/services', [\App\Http\Controllers\Citizen\CitizenController::class, 'services'])->name('services');
        Route::get('/services/{service}', [\App\Http\Controllers\Citizen\CitizenController::class, 'showService'])->name('services.show');

        Route::get('/requests', [\App\Http\Controllers\Citizen\CitizenController::class, 'requests'])->name('requests');
        Route::get('/requests/create/{service}', [\App\Http\Controllers\Citizen\CitizenController::class, 'createRequest'])->name('requests.create');
        Route::post('/requests/store', [\App\Http\Controllers\Citizen\CitizenController::class, 'storeRequest'])->name('requests.store');
        Route::get('/requests/{serviceRequest}/qr', [\App\Http\Controllers\Citizen\CitizenController::class, 'requestQr'])->name('requests.qr');
        Route::get('/requests/{serviceRequest}/feedback', [\App\Http\Controllers\Citizen\CitizenController::class, 'createFeedback'])->name('feedback.create');
        Route::post('/requests/{serviceRequest}/feedback', [\App\Http\Controllers\Citizen\CitizenController::class, 'storeFeedback'])->name('feedback.store');

        Route::get('/chats', [\App\Http\Controllers\Citizen\CitizenController::class, 'chatsIndex'])->name('chats.index');
        Route::get('/requests/{serviceRequest}/chat', [\App\Http\Controllers\Citizen\CitizenController::class, 'chat'])->name('chat');
        Route::post('/requests/{serviceRequest}/chat', [\App\Http\Controllers\Citizen\CitizenController::class, 'sendMessage'])->name('chat.send');

        Route::get('/payments', [\App\Http\Controllers\Citizen\CitizenController::class, 'payments'])->name('payments');
        Route::get('/payments/{serviceRequest}', [PaymentController::class, 'show'])->name('payments.show');
        Route::get('/payments/{serviceRequest}/checkout', [PaymentController::class, 'checkout'])->name('payments.checkout');
        Route::get('/payments/{serviceRequest}/crypto/checkout', [PaymentController::class, 'cryptoCheckout'])->name('payments.crypto.checkout');
        Route::get('/payments/{serviceRequest}/crypto/pay/{payment}', [PaymentController::class, 'cryptoPay'])->name('payments.crypto.pay');
        Route::get('/payments/{serviceRequest}/crypto/pay/{payment}/status', [PaymentController::class, 'cryptoStatus'])->name('payments.crypto.status');
        Route::get('/payments/{serviceRequest}/crypto/success', [PaymentController::class, 'cryptoSuccess'])->name('payments.crypto.success');
        Route::get('/payments/{serviceRequest}/crypto/cancel', [PaymentController::class, 'cryptoCancel'])->name('payments.crypto.cancel');
        Route::get('/payments/{serviceRequest}/success', [PaymentController::class, 'success'])->name('payments.success');
        Route::get('/payments/{serviceRequest}/cancel', [PaymentController::class, 'cancel'])->name('payments.cancel');

        Route::get('/maps', [\App\Http\Controllers\Citizen\CitizenController::class, 'maps'])->name('maps');

        Route::get('/appointments', [\App\Http\Controllers\Citizen\CitizenController::class, 'appointments'])->name('appointments');
        Route::get('/appointments/create/{office}', [\App\Http\Controllers\Citizen\CitizenController::class, 'createAppointment'])->name('appointments.create');
        Route::post('/appointments/store', [\App\Http\Controllers\Citizen\CitizenController::class, 'storeAppointment'])->name('appointments.store');

        Route::get('/history', [\App\Http\Controllers\Citizen\CitizenController::class, 'history'])->name('history');
        Route::get('/history/{serviceRequest}/receipt', [\App\Http\Controllers\Citizen\CitizenController::class, 'downloadReceipt'])->name('history.receipt');
        Route::get('/history/{serviceRequest}/document', [\App\Http\Controllers\Citizen\CitizenController::class, 'downloadDocument'])->name('history.document');
    });
});
