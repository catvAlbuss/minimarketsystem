<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Branch;
use App\Models\Products;
use Illuminate\Database\Seeder;

class ProductsTableSeeder extends Seeder
{
    /**
     * Product categories mapping for better organization
     */
    private const PRODUCT_CATEGORIES = [
        'beverages' => 'Bebidas',
        'food' => 'Alimentos',
        'cleaning' => 'Limpieza',
        'personal_care' => 'Cuidado Personal',
        'canned' => 'Conservas',
        'dairy' => 'Lácteos',
        'bakery' => 'Panadería',
        'frozen' => 'Congelados',
    ];

    public const PRODUCTS = [
        // Bebidas
        'coca_cola_1.5l'     => ['code' => 'BEB001', 'name' => 'Coca Cola 1.5L', 'description' => 'Gaseosa Coca Cola 1.5 litros', 'unit_price' => 8.50, 'higher_price' => 9.50, 'stock' => 50, 'expiration_days' => 365, 'promotion_discount' => 0, 'category' => 'beverages'],
        'inca_kola_1.5l'     => ['code' => 'BEB002', 'name' => 'Inca Kola 1.5L', 'description' => 'Gaseosa Inca Kola 1.5 litros', 'unit_price' => 8.50, 'higher_price' => 9.50, 'stock' => 45, 'expiration_days' => 365, 'promotion_discount' => 5, 'category' => 'beverages'],
        'agua_san_mateo'     => ['code' => 'BEB003', 'name' => 'Agua San Mateo 2.5L', 'description' => 'Agua mineral San Mateo 2.5 litros', 'unit_price' => 5.50, 'higher_price' => 6.50, 'stock' => 30, 'expiration_days' => 730, 'promotion_discount' => 0, 'category' => 'beverages'],
        
        // Lácteos
        'leche_gloria'       => ['code' => 'LAC001', 'name' => 'Leche Gloria 1L', 'description' => 'Leche evaporada Gloria 1 litro', 'unit_price' => 4.20, 'higher_price' => 5.00, 'stock' => 100, 'expiration_days' => 180, 'promotion_discount' => 10, 'category' => 'dairy'],
        'yogurt_laive'       => ['code' => 'LAC002', 'name' => 'Yogurt Laive Frutado', 'description' => 'Yogurt Laive sabor fresa 1kg', 'unit_price' => 7.50, 'higher_price' => 8.50, 'stock' => 40, 'expiration_days' => 45, 'promotion_discount' => 0, 'category' => 'dairy'],
        'queso_mantecoso'    => ['code' => 'LAC003', 'name' => 'Queso Mantecoso', 'description' => 'Queso mantecoso x kg', 'unit_price' => 25.00, 'higher_price' => 28.00, 'stock' => 20, 'expiration_days' => 60, 'promotion_discount' => 0, 'category' => 'dairy'],
        
        // Alimentos
        'arroz_costeno'      => ['code' => 'ALI001', 'name' => 'Arroz Costeño 5kg', 'description' => 'Arroz extra Costeño 5kg', 'unit_price' => 18.50, 'higher_price' => 20.50, 'stock' => 80, 'expiration_days' => 365, 'promotion_discount' => 15, 'category' => 'food'],
        'fideos_don_italo'   => ['code' => 'ALI002', 'name' => 'Fideos Don Italo Spaghetti', 'description' => 'Fideos Don Italo spaghetti 500g', 'unit_price' => 3.20, 'higher_price' => 3.80, 'stock' => 120, 'expiration_days' => 540, 'promotion_discount' => 0, 'category' => 'food'],
        'aceite_primor'      => ['code' => 'ALI003', 'name' => 'Aceite Primor 1L', 'description' => 'Aceite vegetal Primor 1 litro', 'unit_price' => 7.80, 'higher_price' => 8.80, 'stock' => 60, 'expiration_days' => 730, 'promotion_discount' => 5, 'category' => 'food'],
        
        // Limpieza
        'lavavajillas_ayudin' => ['code' => 'LIM001', 'name' => 'Lavavajillas Ayudín', 'description' => 'Lavavajillas Ayudín 500ml', 'unit_price' => 6.50, 'higher_price' => 7.20, 'stock' => 40, 'expiration_days' => 730, 'promotion_discount' => 0, 'category' => 'cleaning'],
        'lejia_clorox'       => ['code' => 'LIM002', 'name' => 'Lejía Clorox 1L', 'description' => 'Lejía Clorox 1 litro', 'unit_price' => 4.80, 'higher_price' => 5.50, 'stock' => 55, 'expiration_days' => 730, 'promotion_discount' => 0, 'category' => 'cleaning'],
        'detergente_ace'     => ['code' => 'LIM003', 'name' => 'Detergente Ace 1kg', 'description' => 'Detergente Ace en polvo 1kg', 'unit_price' => 9.90, 'higher_price' => 11.00, 'stock' => 70, 'expiration_days' => 1095, 'promotion_discount' => 10, 'category' => 'cleaning'],
        
        // Cuidado Personal
        'shampoo_sedal'      => ['code' => 'CUI001', 'name' => 'Shampoo Sedal', 'description' => 'Shampoo Sedal Ceramidas 400ml', 'unit_price' => 15.50, 'higher_price' => 17.00, 'stock' => 35, 'expiration_days' => 730, 'promotion_discount' => 0, 'category' => 'personal_care'],
        'pasta_colgate'      => ['code' => 'CUI002', 'name' => 'Pasta Colgate', 'description' => 'Pasta dental Colgate Triple Acción 90ml', 'unit_price' => 5.20, 'higher_price' => 6.00, 'stock' => 90, 'expiration_days' => 730, 'promotion_discount' => 0, 'category' => 'personal_care'],
        'jabon_protex'       => ['code' => 'CUI003', 'name' => 'Jabón Protex', 'description' => 'Jabón Protex antibacterial x 3', 'unit_price' => 4.50, 'higher_price' => 5.20, 'stock' => 100, 'expiration_days' => 730, 'promotion_discount' => 0, 'category' => 'personal_care'],
    ];

    public function run(): void
    {
        // Obtener la primera sucursal disponible (similar al ejemplo de usuarios)
        $firstBranch = Branch::first();
        
        if (!$firstBranch) {
            $this->command->error('No hay sucursales disponibles. Ejecuta primero el seeder de branches.');
            return;
        }

        // Crear categorías con id_branches
        $categories = [];
        $allCategories = array_merge(
            self::PRODUCT_CATEGORIES,
            ['canned' => 'Conservas', 'bakery' => 'Panadería', 'frozen' => 'Congelados']
        );

        foreach ($allCategories as $key => $name) {
            $category = Category::firstOrCreate(
                ['name' => $name, 'id_branches' => $firstBranch->id],
                [
                    'description' => "Categoría de {$name}",
                    'id_branches' => $firstBranch->id
                ]
            );
            $categories[$key] = $category->id;
        }

        // Seed products
        $allProducts = array_merge(self::PRODUCTS, [
            // Conservas
            ['code' => 'CON001', 'name' => 'Atún Florida', 'description' => 'Atún Florida en aceite 170g', 'unit_price' => 6.50, 'higher_price' => 7.20, 'stock' => 45, 'expiration_days' => 1095, 'promotion_discount' => 0, 'category' => 'canned'],
            ['code' => 'CON002', 'name' => 'Pepinillos Fanny', 'description' => 'Pepinillos Fanny en vinagre 350g', 'unit_price' => 8.90, 'higher_price' => 9.80, 'stock' => 30, 'expiration_days' => 730, 'promotion_discount' => 0, 'category' => 'canned'],
            ['code' => 'PAN001', 'name' => 'Pan de Molde Bimbo', 'description' => 'Pan de molde Bimbo blanco 600g', 'unit_price' => 7.50, 'higher_price' => 8.20, 'stock' => 25, 'expiration_days' => 15, 'promotion_discount' => 20, 'category' => 'bakery'],
            ['code' => 'CONG001', 'name' => 'Verduras Congeladas', 'description' => 'Mezcla de verduras congeladas 500g', 'unit_price' => 6.80, 'higher_price' => 7.50, 'stock' => 30, 'expiration_days' => 180, 'promotion_discount' => 0, 'category' => 'frozen'],
        ]);

        foreach ($allProducts as $data) {
            if (!isset($categories[$data['category']])) {
                continue;
            }

            $expirationDate = now()->addDays($data['expiration_days']);

            Products::firstOrCreate(
                ['code' => $data['code']],
                [
                    'id_categories' => $categories[$data['category']],
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'unit_price' => $data['unit_price'],
                    'higher_price' => $data['higher_price'],
                    'stock' => $data['stock'],
                    'expiration_date' => $expirationDate,
                    'promotion_discount' => $data['promotion_discount'],
                    'state' => 'active',
                ]
            );
        }
    }
}