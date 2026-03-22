<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\GenaralController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReservationController;

Route::controller(GenaralController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/index', 'index')->name('indexAlias');
    Route::get('/about', 'about')->name('about');
    Route::get('/about-us', 'about')->name('aboutUs');
    Route::get('/contact', 'contact')->name('contact');
    Route::get('/facilities', 'facilities')->name('facilities');
    Route::get('/facilities/{facility}', 'facilityShowPublic')->name('facilityPublicShow');
    Route::get('/home', 'home')->name('home');
    Route::get('/redirect-dashboard', 'dashboardRedirect')->name('dashboardRedirect');
    Route::get('/setdashboard', 'setDashboard')->name('setDashboard');
    Route::get('/dashboard', 'setDashboard')->name('dashboard');
    Route::get('/blocked', 'blocked')->name('blocked');
    Route::get('/privacy-policy', 'privacyPolicy')->name('privacyPolicy');
});

Route::controller(ReservationController::class)->group(function () {
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
    });

});
