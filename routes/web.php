<?php

use App\Http\Controllers\BuyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BuyDetailController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleDetailController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// ─── PUBLIC STOREFRONT ────────────────────────────────────────────────────────
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Public JSON API used by Welcome.vue (no auth required)
Route::prefix('api/storefront')->name('storefront.')->group(function () {
    Route::get('branches',    [StorefrontController::class, 'branches'])->name('branches');
    Route::get('categories',  [StorefrontController::class, 'categories'])->name('categories');
    Route::get('products',    [StorefrontController::class, 'products'])->name('products');
    Route::get('promotions',  [StorefrontController::class, 'promotions'])->name('promotions');
    Route::post('checkout',   [StorefrontController::class, 'checkout'])->name('checkout');
});

// ─── AUTHENTICATED DASHBOARD ──────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function (): void {

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', UserController::class);
    Route::resource('products', ProductsController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('sales', SaleController::class);
    Route::resource('sale_details', SaleDetailController::class);
    Route::resource('buys', BuyController::class);
    Route::resource('buy_details', BuyDetailController::class);
    Route::resource('providers', ProviderController::class);
    Route::resource('promotions', PromotionController::class);
    Route::resource('branches', BranchController::class)->only(['index', 'store', 'update', 'destroy']);
});

require __DIR__ . '/settings.php';
