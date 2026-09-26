<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HospitalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', HomeController::class)->name('home');

Route::middleware('guest')->group(function () {
	Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
	Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
	Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
	Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
});

Route::get('/hospitals/{hospital}/guides', [HospitalController::class, 'guides'])->name('hospitals.guides');

Route::middleware(['auth', 'active'])->group(function () {
	Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
	Route::get('/dashboard', DashboardController::class)->name('dashboard');
});

Route::middleware(['auth', 'active', 'role:patient'])->group(function () {
	Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
	Route::delete('/bookings/{booking}', [BookingController::class, 'cancel'])->name('bookings.cancel');
});

Route::middleware(['auth', 'active', 'role:guide'])->prefix('guide')->name('guide.')->group(function () {
	Route::put('/profile', [GuideController::class, 'updateProfile'])->name('profile.update');
	Route::patch('/bookings/{booking}', [GuideController::class, 'respond'])->name('bookings.respond');
});

Route::middleware(['auth', 'active', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
	Route::post('/hospitals', [AdminController::class, 'storeHospital'])->name('hospitals.store');
	Route::patch('/hospitals/{hospital}', [AdminController::class, 'toggleHospital'])->name('hospitals.toggle');
	Route::put('/hospitals/{hospital}', [AdminController::class, 'updateHospital'])->name('hospitals.update');
	Route::delete('/hospitals/{hospital}', [AdminController::class, 'deleteHospital'])->name('hospitals.delete');
	Route::patch('/guides/{guideProfile}', [AdminController::class, 'verifyGuide'])->name('guides.verify');
	Route::post('/guides/{guideProfile}/documents', [AdminController::class, 'uploadGuideDocument'])->name('guides.documents.upload');
	Route::get('/guides/{guideProfile}/documents', [AdminController::class, 'viewGuideDocument'])->name('guides.documents.view');
	Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
	Route::patch('/users/{user}/block', [AdminController::class, 'toggleUserBlock'])->name('users.block');
	Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
	Route::post('/services', [AdminController::class, 'storeService'])->name('services.store');
	Route::put('/services/{service}', [AdminController::class, 'updateService'])->name('services.update');
	Route::delete('/services/{service}', [AdminController::class, 'deleteService'])->name('services.delete');
	Route::patch('/bookings/{booking}', [AdminController::class, 'updateBookingStatus'])->name('bookings.status');
	Route::patch('/reviews/{review}', [AdminController::class, 'toggleReviewVisibility'])->name('reviews.visibility');
	Route::delete('/reviews/{review}', [AdminController::class, 'deleteReview'])->name('reviews.delete');
	Route::put('/commission', [AdminController::class, 'updateCommission'])->name('commission.update');
});
