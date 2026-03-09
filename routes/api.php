<?php

use App\Http\Controllers\CoordController;
use App\Http\Controllers\DesignerController;
use App\Http\Controllers\GeneralDesignerController;
use App\Http\Controllers\PolygonController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('coords', CoordController::class)->except(['create', 'edit']);
Route::apiResource('polygons', PolygonController::class)->except(['create', 'edit']);
Route::apiResource('designers', DesignerController::class)->except(['create', 'edit']);
Route::apiResource('general-designers', GeneralDesignerController::class)->except(['create', 'edit']);
Route::apiResource('roles', RoleController::class)->except(['create', 'edit']);
Route::apiResource('projects', ProjectController::class)->except(['create', 'edit']);

