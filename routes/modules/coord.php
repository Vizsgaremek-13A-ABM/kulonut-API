<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CoordController;

Route::apiResource('coords', CoordController::class)->except(['create', 'edit']);