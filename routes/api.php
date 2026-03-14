<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\RolePermissionController;

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
    Route::post('/facilities', [FacilityController::class, 'store']);
    Route::put('/facilities/{facility}', [FacilityController::class, 'update']);
    Route::delete('/facilities/{facility}', [FacilityController::class, 'destroy']);
    Route::get('/facilities/{facility}/images', [FacilityController::class, 'images']);
    Route::post('/facilities/{facility}/images', [FacilityController::class, 'uploadImages']);
    Route::delete('/facilities/{facility}/images/{image}', [FacilityController::class, 'deleteImage']);
    Route::post('/facilities/{facility}/images/primary', [FacilityController::class, 'setPrimaryImage']);
});

Route::get('/public/facilities', [FacilityController::class, 'publicIndex']);
Route::get('/public/facilities/{facility}', [FacilityController::class, 'publicShow']);
