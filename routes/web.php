<?php

use App\Http\Controllers\ProductsController;
<<<<<<< HEAD
use App\Http\Controllers\UserController;
=======
use App\Http\Controllers\CustomerController;
>>>>>>> ae932c8097be564bb2d3d9507b665fa076550c2e
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
<<<<<<< HEAD
    Route::resource('users', UserController::class);
    Route::resource('products', ProductsController::class); 
=======
    Route::resource('products', ProductsController::class);
    Route::resource('customers', CustomerController::class);
>>>>>>> ae932c8097be564bb2d3d9507b665fa076550c2e
    // Route::post('/reservations', [ReservationController::class, 'create'])->name('reservations.create');
});






require __DIR__.'/settings.php';
