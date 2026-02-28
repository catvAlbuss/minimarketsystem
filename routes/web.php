<?php
use App\Http\Controllers\BuyController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BuyDetailController;
use App\Http\Controllers\CategoryController;
use App\Models\Branch;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\SaleDetailController;
use App\Models\BuyDetail;
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


    Route::resource('users', UserController::class);
    Route::resource('products', ProductsController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('sales', SaleController::class);
    Route::resource('sale_details', SaleDetailController::class);
    Route::resource('buys', BuyController::class);
    Route::resource('buy_details',BuyDetailController::class);

    // Route::post('/reservations', [ReservationController::class, 'create'])->name('reservations.create');
    Route::resource('providers', ProviderController::class);
    Route::resource('promotions', PromotionController::class);    // Route::post('/reservations', [ReservationController::class, 'create'])->name('reservations.create');
    Route::resource('branches', BranchController::class)->only(['index', 'store', 'update', 'destroy']);
    // Route::post('/reservations', [ReservationController::class, 'create'])->name('reservations.create');

    // Route::resource('providers',ProviderController::class);
    // Route::resource('promotions', PromotionController::class);    // Route::post('/reservations', [ReservationController::class, 'create'])->name('reservations.create');

});



require __DIR__ . '/settings.php';
