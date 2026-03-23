<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\GenaralController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberDashboardController;
use App\Http\Controllers\MemberRegistrationController;
use App\Http\Controllers\MemberRegistrationPaymentController;
use App\Http\Controllers\MemberSubscriptionPaymentController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\TrainingSessionPurchaseController;

Route::controller(GenaralController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/index', 'index')->name('indexAlias');
    Route::get('/about', 'about')->name('about');
    Route::get('/about-us', 'about')->name('aboutUs');
    Route::get('/contact', 'contact')->name('contact');
    Route::get('/facilities', 'facilities')->name('facilities');
    Route::get('/facilities/{facility}', 'facilityShowPublic')->name('facilityPublicShow');
    Route::get('/training-sessions', 'trainingSessions')->name('trainingSessions');
    Route::get('/training-sessions/{trainingSession}', 'trainingSessionShow')->name('trainingSessionShow');
    Route::get('/home', 'home')->name('home');
    Route::get('/redirect-dashboard', 'dashboardRedirect')->name('dashboardRedirect');
    Route::get('/setdashboard', 'setDashboard')->name('setDashboard');
    Route::get('/dashboard', 'setDashboard')->name('dashboard');
    Route::get('/blocked', 'blocked')->name('blocked');
    Route::get('/privacy-policy', 'privacyPolicy')->name('privacyPolicy');
});

Route::view('/member/login', 'auth.member-login')
    ->middleware('guest')
    ->name('member.login');

Route::get('/member/register', [MemberRegistrationController::class, 'page'])
    ->middleware('guest')
    ->name('member.register');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/member/dashboard', [MemberDashboardController::class, 'index'])->name('memberDashboard');
});

Route::get('/member/payments/seylan/checkout/{memberRegistrationPayment}', [MemberRegistrationPaymentController::class, 'checkoutPage'])
    ->name('member.payment.seylan.checkout');
Route::get('/member/payments/seylan/return', [MemberRegistrationPaymentController::class, 'return'])
    ->name('member.payment.seylan.return');
Route::get('/member/payments/success/{memberRegistrationPayment}', [MemberRegistrationPaymentController::class, 'success'])
    ->name('member.payment.success');
Route::get('/member/subscriptions/payments/seylan/checkout/{memberSubscriptionPayment}', [MemberSubscriptionPaymentController::class, 'checkoutPage'])
    ->name('member.subscription.payment.seylan.checkout');
Route::get('/member/subscriptions/payments/seylan/return', [MemberSubscriptionPaymentController::class, 'return'])
    ->name('member.subscription.payment.seylan.return');
Route::get('/member/subscriptions/payments/success/{memberSubscriptionPayment}', [MemberSubscriptionPaymentController::class, 'success'])
    ->name('member.subscription.payment.success');
Route::get('/training-sessions/payments/seylan/checkout/{trainingSessionPayment}', [TrainingSessionPurchaseController::class, 'checkoutPage'])
    ->middleware('auth:sanctum')
    ->name('training.session.payment.seylan.checkout');
Route::get('/training-sessions/payments/seylan/return', [TrainingSessionPurchaseController::class, 'return'])
    ->name('training.session.payment.seylan.return');
Route::get('/training-sessions/payments/success/{trainingSessionPayment}', [TrainingSessionPurchaseController::class, 'success'])
    ->name('training.session.payment.success');
Route::get('/training-sessions/{trainingSession}/purchase', [TrainingSessionPurchaseController::class, 'page'])
    ->name('trainingSessionPurchasePage');

Route::controller(ReservationController::class)->group(function () {
    Route::get('/booking', 'publicPage')->name('publicBookingPage');
    Route::get('/reservations', 'publicPage')->name('publicReservationsPage');
});

Route::prefix('admin')->middleware(['auth:sanctum', 'permission:Access Admin Dashboard', config('jetstream.auth_session'), 'verified',])->group(function () {
    
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'getAdminDashboard')->name('adminDashboard');
    });
 
});


Route::middleware(['permission:Manage Settings', config('jetstream.auth_session'), 'verified',])->group(function () {

    Route::controller(UserController::class)->group(function () {
        Route::get('/system-users', 'sysUsers')->name('sysUsers');
    });

    Route::controller(RoleController::class)->group(function () {
        Route::get('/roles-and-permission', 'roleManagement')->name('roleManagement');
    });

    Route::controller(FacilityController::class)->group(function () {
        Route::get('/admin/facilities', 'facilityManagement')->name('facilityManagement');
        Route::get('/admin/facilities/{facility}', 'show')->name('facilityShow');
    });

    Route::view('/admin/general-configurations', 'dashboards.admin.settings.generalConfigurations')
        ->name('generalConfigurations');

    Route::view('/admin/subscriptions', 'dashboards.admin.settings.subscriptions')
        ->name('subscriptionsPage');

    Route::controller(ReservationController::class)->group(function () {
        Route::get('/admin/reservations', 'reservationManagement')->name('reservationManagement');
        Route::get('/admin/reservations/approved', 'approvedReservationManagement')->name('approvedReservationManagement');
    });

});
