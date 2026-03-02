<?php

use App\Models\Branch;
use App\Models\Category;
use App\Models\Products;
use App\Models\Promotion;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

it('has required storefront schema changes', function () {
    expect(Schema::hasColumn('categories', 'id_branches'))->toBeTrue();
    expect(Schema::hasColumn('products', 'image'))->toBeTrue();
    expect(Schema::hasColumn('promotions', 'image'))->toBeTrue();

    $info = collect(DB::select('PRAGMA table_info(categories)'));
    $branchColumn = $info->firstWhere('name', 'id_branches');

    expect((int) $branchColumn->notnull)->toBe(1);
});

it('filters products by branch and only offers', function () {
    $systemUser = User::query()->where('email', 'systemcashier@minimarket.local')->firstOrFail();
    $defaultBranch = Branch::query()->where('name', 'Sucursal General')->firstOrFail();

    $otherBranch = Branch::create([
        'id_users' => $systemUser->id,
        'name' => 'Sucursal Secundaria',
        'address' => 'Dirección 2',
        'opening_time' => '08:00:00',
        'closing_time' => '21:00:00',
        'state' => 'active',
    ]);

    $catA = Category::create([
        'id_branches' => $defaultBranch->id,
        'name' => 'Lacteos',
        'description' => 'Categoria A',
    ]);

    $catB = Category::create([
        'id_branches' => $otherBranch->id,
        'name' => 'Bebidas',
        'description' => 'Categoria B',
    ]);

    Products::create([
        'id_categories' => $catA->id,
        'code' => 'P-001',
        'name' => 'Yogurt',
        'description' => 'Natural',
        'image' => '/img/yogurt.jpg',
        'unit_price' => 5,
        'higher_price' => 4.5,
        'stock' => 10,
        'expiration_date' => '2026-12-31',
        'promotion_discount' => 20,
        'state' => 'active',
    ]);

    Products::create([
        'id_categories' => $catA->id,
        'code' => 'P-002',
        'name' => 'Leche',
        'description' => 'Entera',
        'image' => '/img/leche.jpg',
        'unit_price' => 6,
        'higher_price' => 5.5,
        'stock' => 10,
        'expiration_date' => '2026-12-31',
        'promotion_discount' => 0,
        'state' => 'active',
    ]);

    Products::create([
        'id_categories' => $catB->id,
        'code' => 'P-003',
        'name' => 'Gaseosa',
        'description' => 'Cola',
        'image' => '/img/gaseosa.jpg',
        'unit_price' => 4,
        'higher_price' => 3.8,
        'stock' => 10,
        'expiration_date' => '2026-12-31',
        'promotion_discount' => 30,
        'state' => 'active',
    ]);

    $response = $this->getJson('/api/storefront/products?branch_id='.$defaultBranch->id.'&only_offers=1');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.name', 'Yogurt');
});

it('groups promotions by name for storefront response', function () {
    $branch = Branch::query()->where('name', 'Sucursal General')->firstOrFail();

    $category = Category::create([
        'id_branches' => $branch->id,
        'name' => 'Snacks',
        'description' => 'Categoria Snacks',
    ]);

    $p1 = Products::create([
        'id_categories' => $category->id,
        'code' => 'P-010',
        'name' => 'Papas',
        'description' => 'Snack 1',
        'image' => '/img/papas.jpg',
        'unit_price' => 3,
        'higher_price' => 2.8,
        'stock' => 8,
        'expiration_date' => '2026-12-31',
        'promotion_discount' => 10,
        'state' => 'active',
    ]);

    $p2 = Products::create([
        'id_categories' => $category->id,
        'code' => 'P-011',
        'name' => 'Galletas',
        'description' => 'Snack 2',
        'image' => '/img/galletas.jpg',
        'unit_price' => 2,
        'higher_price' => 1.8,
        'stock' => 9,
        'expiration_date' => '2026-12-31',
        'promotion_discount' => 5,
        'state' => 'active',
    ]);

    Promotion::create([
        'id_products' => $p1->id,
        'name_promotion' => 'Combo Snack',
        'image' => '/img/combo.jpg',
        'price' => 4.5,
        'state' => 'active',
    ]);

    Promotion::create([
        'id_products' => $p2->id,
        'name_promotion' => 'Combo Snack',
        'image' => '/img/combo.jpg',
        'price' => 4.5,
        'state' => 'active',
    ]);

    $response = $this->getJson('/api/storefront/promotions');

    $response->assertOk();
    $response->assertJsonPath('data.0.name_promotion', 'Combo Snack');
    $response->assertJsonPath('data.0.items_count', 2);
});

it('creates sale and sale details on checkout and discounts stock', function () {
    $branch = Branch::query()->where('name', 'Sucursal General')->firstOrFail();

    $category = Category::create([
        'id_branches' => $branch->id,
        'name' => 'Panaderia',
        'description' => 'Categoria Pan',
    ]);

    $product = Products::create([
        'id_categories' => $category->id,
        'code' => 'P-020',
        'name' => 'Pan',
        'description' => 'Integral',
        'image' => '/img/pan.jpg',
        'unit_price' => 10,
        'higher_price' => 9,
        'stock' => 5,
        'expiration_date' => '2026-12-31',
        'promotion_discount' => 10,
        'state' => 'active',
    ]);

    $response = $this->postJson('/api/storefront/checkout', [
        'branch_id' => $branch->id,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 2],
        ],
        'payment_method' => 'cash',
        'voucher' => 'ticket',
    ]);

    $response->assertCreated();
    expect(Sale::query()->count())->toBe(1);
    expect(SaleDetail::query()->count())->toBe(1);
    expect($product->fresh()->stock)->toBe(3);
});

it('returns conflict when checkout has insufficient stock', function () {
    $branch = Branch::query()->where('name', 'Sucursal General')->firstOrFail();

    $category = Category::create([
        'id_branches' => $branch->id,
        'name' => 'Limpieza',
        'description' => 'Categoria Limpieza',
    ]);

    $product = Products::create([
        'id_categories' => $category->id,
        'code' => 'P-030',
        'name' => 'Detergente',
        'description' => 'Lavado',
        'image' => '/img/detergente.jpg',
        'unit_price' => 8,
        'higher_price' => 7,
        'stock' => 1,
        'expiration_date' => '2026-12-31',
        'promotion_discount' => 0,
        'state' => 'active',
    ]);

    $response = $this->postJson('/api/storefront/checkout', [
        'branch_id' => $branch->id,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 2],
        ],
    ]);

    $response->assertStatus(409);
});
