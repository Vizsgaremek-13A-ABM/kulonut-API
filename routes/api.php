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
});

Route::middleware('auth:sanctum')->controller(AuthController::class)->group(function () {
    Route::post('/auth/logout', 'logout');
    Route::get('/user', 'me');
});

Route::controller(ProjectController::class)->group(function () {
    Route::get('/projects/map', 'mapView');
    Route::get('/projects/{project}/polygons', 'polygons');
});

Route::controller(PolygonController::class)->group(function () {
    Route::post('/polygons/bulk', 'bulkStore');
    Route::put('/polygons/bulk', 'bulkUpdate');
    Route::delete('/polygons/bulk', 'bulkDestroy');
});

Route::apiResource('projects', ProjectController::class);
Route::apiResource('polygons', PolygonController::class);
Route::apiResource('users', UserController::class);

Route::apiResource('coords', CoordController::class);
Route::apiResource('roles', RoleController::class);

Route::apiResource('geodesies', GeodesyController::class);
Route::get('/geodesies/{geodesy}/projects', [GeodesyController::class, 'getProjects']);

Route::apiResource('clients', ClientController::class);
Route::get('/clients/{client}/projects', [ClientController::class, 'getProjects']);

Route::apiResource('general-designers', GeneralDesignerController::class);
Route::get('/general-designers/{generalDesigner}/projects', [GeneralDesignerController::class, 'getProjects']);


Route::apiResource('designers', DesignerController::class);
Route::get('/designers/{designer}/projects', [DesignerController::class, 'getProjects']);
