<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CoordController;
use App\Http\Controllers\DesignerController;
use App\Http\Controllers\GeneralDesignerController;
use App\Http\Controllers\GeodesyController;
use App\Http\Controllers\PolygonController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:5,1')->controller(AuthController::class)->group(function () {
    Route::post('/auth/register', 'register');
    Route::post('/auth/login', 'login');
    Route::post('/auth/forgot-password', 'forgotPassword')->name('password.email');
    Route::post('/auth/reset-password', 'resetPassword')->name('password.update');
});

Route::middleware(['auth:sanctum', 'verified'])->controller(AuthController::class)->group(function () {
    Route::post('/auth/logout', 'logout');
    Route::get('/auth/user', 'me');
    Route::post('/auth/update-password', 'updatePassword');
    Route::post('/email/verification-notification', 'sendVerificationEmail')->middleware('throttle:60,1');
});

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

Route::middleware(['auth:sanctum', 'verified', 'role.level:' . config('rbac.user_level')])->group(function () {
    Route::controller(ProjectController::class)->group(function () {
        Route::get('/projects/map', 'mapView');
        Route::get('/projects/{project}/polygons', 'polygons');
    });

    Route::apiResource('projects', ProjectController::class)->only(['index', 'show']);

    Route::apiResource('geodesies', GeodesyController::class)->only(['index', 'show']);
    Route::get('/geodesies/{geodesy}/projects', [GeodesyController::class, 'getProjects']);

    Route::apiResource('clients', ClientController::class)->only(['index', 'show']);
    Route::get('/clients/{client}/projects', [ClientController::class, 'getProjects']);

    Route::apiResource('general-designers', GeneralDesignerController::class)->only(['index', 'show']);
    Route::get('/general-designers/{generalDesigner}/projects', [GeneralDesignerController::class, 'getProjects']);

    Route::apiResource('designers', DesignerController::class)->only(['index', 'show']);
    Route::get('/designers/{designer}/projects', [DesignerController::class, 'getProjects']);

    Route::apiResource('polygons', PolygonController::class)->only(['index', 'show']);
    Route::apiResource('coords', CoordController::class)->only(['index', 'show']);

    Route::apiResource('roles', RoleController::class);
    Route::post('/users/{user}/profile-icon', [UserController::class, 'uploadProfileIcon']);
});

Route::middleware(['auth:sanctum', 'verified', 'role.level:' . config('rbac.employee_level')])->group(function () {
    Route::controller(PolygonController::class)->group(function () {
        Route::post('/polygons/bulk', 'bulkStore');
        Route::put('/polygons/bulk', 'bulkUpdate');
        Route::delete('/polygons/bulk', 'bulkDestroy');
        Route::post('/polygons/projects/bulk', 'bulkUnlink');
        Route::delete('/polygons/{polygon}/projects/{project}', 'unlink');
    });

    Route::apiResource('projects', ProjectController::class)->except(['index', 'show']);
    Route::apiResource('polygons', PolygonController::class)->except(['index', 'show']);
    Route::apiResource('coords', CoordController::class)->except(['index', 'show']);

    Route::apiResource('geodesies', GeodesyController::class)->except(['index', 'show']);
    Route::apiResource('clients', ClientController::class)->except(['index', 'show']);
    Route::apiResource('general-designers', GeneralDesignerController::class)->except(['index', 'show']);
    Route::apiResource('designers', DesignerController::class)->except(['index', 'show']);
});

Route::middleware(['auth:sanctum', 'verified', 'role.level:' . config('rbac.admin_level')])->group(function () {
    Route::apiResource('users', UserController::class);
});
