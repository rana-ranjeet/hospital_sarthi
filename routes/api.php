<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\GuideApiController;
use App\Http\Controllers\HospitalApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/hospitals', HospitalApiController::class)->name('api.hospitals.index');
Route::get('/guides', GuideApiController::class)->name('api.guides.index');
Route::middleware('auth:sanctum')->post('/bookings', [BookingController::class, 'store'])->name('api.bookings.store');
