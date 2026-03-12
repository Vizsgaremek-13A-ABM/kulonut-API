<?php

use App\Http\Controllers\CoordController;
use App\Http\Controllers\DesignerController;
use App\Http\Controllers\GeneralDesignerController;
use App\Http\Controllers\GeodesyController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PolygonController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('coords', CoordController::class);
Route::apiResource('polygons', PolygonController::class);
Route::apiResource('designers', DesignerController::class);
Route::apiResource('general-designers', GeneralDesignerController::class);
Route::apiResource('roles', RoleController::class);
Route::apiResource('projects', ProjectController::class);
Route::apiResource('geodesies', GeodesyController::class);
Route::apiResource('clients', ClientController::class);

