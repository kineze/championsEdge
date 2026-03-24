<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BankDetailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberSubscriptionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\AgeGroupController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\FacilityExtraItemController;
use App\Http\Controllers\MemberRegistrationController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationPriceController;
use App\Http\Controllers\SubscriptionPricingController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\TrainingSessionController;
use App\Http\Controllers\TrainingSessionPurchaseController;
use App\Http\Controllers\WorkingHourController;
use App\Http\Controllers\OllamaChatController;

Route::middleware(['auth:sanctum', 'role:Admin'])->group(function () {

    Route::get('/roles', [RolePermissionController::class, 'index']);
    Route::post('/roles', [RolePermissionController::class, 'store']);
    Route::get('/roles/{role}', [RolePermissionController::class, 'show']);
    Route::put('/roles/{role}', [RolePermissionController::class, 'update']);
    Route::delete('/roles/{role}', [RolePermissionController::class, 'destroy']);
    Route::get('/permissions', [RolePermissionController::class, 'permissions']);
    Route::post('/roles/{role}/sync', [RolePermissionController::class, 'syncPermissions']);

});

Route::middleware(['auth:sanctum', 'role:Admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    Route::post('/users/{user}/role', [UserController::class, 'assignRole']);
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
});

Route::middleware(['auth:sanctum', 'role:Admin'])->group(function () {
    Route::get('/facilities', [FacilityController::class, 'index']);
    Route::get('/facilities/{facility}', [FacilityController::class, 'apiShow']);
    Route::post('/facilities', [FacilityController::class, 'store']);
    Route::put('/facilities/{facility}', [FacilityController::class, 'update']);
    Route::delete('/facilities/{facility}', [FacilityController::class, 'destroy']);
    Route::get('/facilities/{facility}/images', [FacilityController::class, 'images']);
    Route::post('/facilities/{facility}/images', [FacilityController::class, 'uploadImages']);
    Route::delete('/facilities/{facility}/images/{image}', [FacilityController::class, 'deleteImage']);
    Route::post('/facilities/{facility}/images/primary', [FacilityController::class, 'setPrimaryImage']);
    Route::get('/facilities/{facility}/subscription-pricings', [SubscriptionPricingController::class, 'index']);
    Route::get('/facilities/{facility}/subscription-pricings/{subscriptionPricing}', [SubscriptionPricingController::class, 'show']);
    Route::post('/facilities/{facility}/subscription-pricings', [SubscriptionPricingController::class, 'store']);
    Route::put('/facilities/{facility}/subscription-pricings/{subscriptionPricing}', [SubscriptionPricingController::class, 'update']);
    Route::get('/facilities/{facility}/reservation-prices', [ReservationPriceController::class, 'index']);
    Route::get('/facilities/{facility}/reservation-prices/{reservationPrice}', [ReservationPriceController::class, 'show']);
    Route::post('/facilities/{facility}/reservation-prices', [ReservationPriceController::class, 'store']);
    Route::put('/facilities/{facility}/reservation-prices/{reservationPrice}', [ReservationPriceController::class, 'update']);
    Route::get('/facilities/{facility}/training-sessions', [TrainingSessionController::class, 'index']);
    Route::get('/facilities/{facility}/training-sessions/{trainingSession}', [TrainingSessionController::class, 'show']);
    Route::post('/facilities/{facility}/training-sessions', [TrainingSessionController::class, 'store']);
    Route::put('/facilities/{facility}/training-sessions/{trainingSession}', [TrainingSessionController::class, 'update']);
    Route::delete('/facilities/{facility}/training-sessions/{trainingSession}', [TrainingSessionController::class, 'destroy']);
    Route::get('/facilities/{facility}/extra-items', [FacilityExtraItemController::class, 'index']);
    Route::get('/facilities/{facility}/extra-items/{facilityExtraItem}', [FacilityExtraItemController::class, 'show']);
    Route::post('/facilities/{facility}/extra-items', [FacilityExtraItemController::class, 'store']);
    Route::put('/facilities/{facility}/extra-items/{facilityExtraItem}', [FacilityExtraItemController::class, 'update']);
    Route::delete('/facilities/{facility}/extra-items/{facilityExtraItem}', [FacilityExtraItemController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'role:Admin'])->group(function () {
    Route::get('/age-groups', [AgeGroupController::class, 'index']);
    Route::post('/age-groups', [AgeGroupController::class, 'store']);
    Route::put('/age-groups/{ageGroup}', [AgeGroupController::class, 'update']);
    Route::delete('/age-groups/{ageGroup}', [AgeGroupController::class, 'destroy']);

    Route::get('/working-hours', [WorkingHourController::class, 'index']);
    Route::put('/working-hours/bulk', [WorkingHourController::class, 'bulkUpsert']);

    Route::get('/bank-details', [BankDetailController::class, 'index']);
    Route::post('/bank-details', [BankDetailController::class, 'store']);
    Route::put('/bank-details/{bankDetail}', [BankDetailController::class, 'update']);
    Route::delete('/bank-details/{bankDetail}', [BankDetailController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'role:Admin'])->group(function () {
    Route::get('/subscriptions', [SubscriptionController::class, 'index']);
    Route::get('/subscriptions/meta', [SubscriptionController::class, 'meta']);
    Route::post('/subscriptions', [SubscriptionController::class, 'store']);
    Route::patch('/subscriptions/{subscription}/toggle-blocked', [SubscriptionController::class, 'toggleBlocked']);
});

Route::middleware(['auth:sanctum', 'role:Admin'])->group(function () {
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::get('/reservations/approved', [ReservationController::class, 'approvedReservations']);
    Route::get('/admin/reservations/calendar-events', [ReservationController::class, 'adminCalendarEvents']);
    Route::get('/admin/dashboard/analytics', [DashboardController::class, 'analytics']);
    Route::patch('/reservations/{reservation}/status', [ReservationController::class, 'updateStatus']);
    Route::post('/reservations/{reservation}/payments', [ReservationController::class, 'addPayment']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/member/subscription/summary', [MemberSubscriptionController::class, 'summary']);
    Route::get('/member/subscription/purchase-meta', [MemberSubscriptionController::class, 'purchaseMeta']);
    Route::post('/member/subscription/initiate-purchase', [MemberSubscriptionController::class, 'initiatePurchase']);
    Route::post('/member/subscription/{subscription}/renew', [MemberSubscriptionController::class, 'renew']);
    Route::post('/member/subscription/{subscription}/cancel', [MemberSubscriptionController::class, 'cancel']);
    Route::post('/member/subscription/{subscription}/reactivate-payment', [MemberSubscriptionController::class, 'initiateReactivationPayment']);
    Route::get('/member/training-sessions/{trainingSession}/purchase-meta', [TrainingSessionPurchaseController::class, 'meta']);
    Route::post('/member/training-sessions/{trainingSession}/initiate-payment', [TrainingSessionPurchaseController::class, 'initiatePayment']);
    Route::post('/member/training-sessions/{trainingSession}/renew-payment', [TrainingSessionPurchaseController::class, 'initiateRenewalPayment']);
});

Route::get('/public/facilities', [FacilityController::class, 'publicIndex']);
Route::get('/public/facilities/{facility}', [FacilityController::class, 'publicShow']);
Route::get('/public/reservations/meta', [ReservationController::class, 'publicMeta']);
Route::get('/public/reservations/calendar-events', [ReservationController::class, 'publicCalendarEvents']);
Route::post('/public/reservations/availability', [ReservationController::class, 'checkAvailability']);
Route::post('/public/reservations/initiate-payment', [ReservationController::class, 'initiatePublicPayment']);
Route::post('/public/reservations', [ReservationController::class, 'publicStore']);
Route::get('/public/member-registration/meta', [MemberRegistrationController::class, 'meta']);
Route::post('/public/member-registration/initiate-payment', [MemberRegistrationController::class, 'initiatePayment']);
Route::get('/public/training-sessions', [TrainingSessionController::class, 'publicIndex']);
Route::get('/public/training-sessions/{trainingSession}', [TrainingSessionController::class, 'publicShow']);
Route::post('/public/assistant/chat', [OllamaChatController::class, 'publicChat']);
