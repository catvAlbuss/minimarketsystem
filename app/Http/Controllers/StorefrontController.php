<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Products;
use App\Models\Promotion;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class StorefrontController extends Controller
{
    /**
     * Return all active branches for the storefront branch selector.
     */
    public function branches()
    {
        $branches = Branch::where('state', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'address', 'opening_time', 'closing_time']);

        return response()->json([
            'data' => $branches->map(fn(Branch $branch) => [
                'id'           => $branch->id,
                'name'         => $branch->name,
                'address'      => $branch->address,
                'opening_time' => $branch->opening_time,
                'closing_time' => $branch->closing_time,
            ])->values(),
        ]);
    }

    public function categories(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        $categories = Category::query()
            ->with(['branch:id,name'])
            ->whereHas('products', function ($query) {
                $query->where('state', 'active')
                    ->where('stock', '>', 0);
            })
            ->when(isset($validated['branch_id']), function ($query) use ($validated) {
                $query->where('id_branches', $validated['branch_id']);
            })
            ->orderBy('name')
            ->get(['id', 'id_branches', 'name', 'description']);

        return response()->json([
            'data' => $categories->map(fn(Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'branch' => [
                    'id' => $category->branch?->id,
                    'name' => $category->branch?->name,
                ],
            ])->values(),
        ]);
    }

    public function products(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'search' => ['nullable', 'string', 'max:255'],
            'only_offers' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = $validated['per_page'] ?? 12;

        $products = Products::query()
            ->with(['category:id,id_branches,name', 'category.branch:id,name'])
            ->where('state', 'active')
            ->where('stock', '>', 0)
            ->when(isset($validated['branch_id']), function ($query) use ($validated) {
                $query->whereHas('category', function ($categoryQuery) use ($validated) {
                    $categoryQuery->where('id_branches', $validated['branch_id']);
                });
            })
            ->when(isset($validated['category_id']), function ($query) use ($validated) {
                $query->where('id_categories', $validated['category_id']);
            })
            ->when(! empty($validated['search']), function ($query) use ($validated) {
                $query->where(function ($nestedQuery) use ($validated) {
                    $nestedQuery->where('name', 'like', '%' . $validated['search'] . '%')
                        ->orWhere('description', 'like', '%' . $validated['search'] . '%')
                        ->orWhere('code', 'like', '%' . $validated['search'] . '%');
                });
            })
            ->when(($validated['only_offers'] ?? false) === true, function ($query) {
                $query->where('promotion_discount', '>', 0);
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'data' => $products->getCollection()->map(fn(Products $product) => $this->productPayload($product))->values(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function promotions(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        $promotions = Promotion::query()
            ->with([
                'product:id,id_categories,name,image,state,stock',
                'product.category:id,id_branches,name',
            ])
            ->where('state', 'active')
            ->when(isset($validated['branch_id']), function ($query) use ($validated) {
                $query->whereHas('product.category', function ($categoryQuery) use ($validated) {
                    $categoryQuery->where('id_branches', $validated['branch_id']);
                });
            })
            ->get();

        $grouped = $promotions
            ->groupBy('name_promotion')
            ->map(function ($group, $namePromotion) {
                $first = $group->first();

                $items = $group
                    ->filter(fn(Promotion $promotion) => $promotion->product && $promotion->product->state === 'active' && $promotion->product->stock > 0)
                    ->map(fn(Promotion $promotion) => [
                        'id' => $promotion->product->id,
                        'name' => $promotion->product->name,
                        'image' => $promotion->product->image,
                    ])
                    ->values();

                return [
                    'name_promotion' => $namePromotion,
                    'price' => (float) $first->price,
                    'image' => $group->firstWhere('image', '!=', null)?->image,
                    'items_count' => $items->count(),
                    'items' => $items,
                ];
            })
            ->values();

        return response()->json([
            'data' => $grouped,
        ]);
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'payment_method' => ['nullable', 'in:cash,card,yape,plin'],
            'voucher' => ['nullable', 'in:ticket,invoice'],
            'document' => ['nullable', 'string', 'max:255'],
            'customer' => ['nullable', 'array'],
            'customer.id' => ['nullable', 'integer', 'exists:customers,id'],
            'customer.dni' => ['nullable', 'regex:/^\d{8}$/'],
            'customer.name' => ['nullable', 'string', 'max:255'],
            'customer.last_name' => ['nullable', 'string', 'max:255'],
            'customer.email' => ['nullable', 'email', 'max:255'],
            'customer.phone' => ['nullable', 'string', 'max:9'],
            'customer.address' => ['nullable', 'string', 'max:255'],
            'customer.birthday' => ['nullable', 'date'],
        ]);

        if (isset($validated['customer']) && ! isset($validated['customer']['id'])) {
            Validator::make($validated['customer'], [
                'dni' => ['required', 'regex:/^\d{8}$/'],
                'name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:9'],
                'address' => ['nullable', 'string', 'max:255'],
                'birthday' => ['nullable', 'date'],
            ])->validate();
        }

        $checkout = DB::transaction(function () use ($validated) {
            $customer = $this->resolveCustomer($validated['customer'] ?? null);
            $systemUser = $this->systemCashier();

            $itemCollection = collect($validated['items']);
            $productIds = $itemCollection->pluck('product_id')->unique()->values();
            $products = Products::query()
                ->with('category')
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $saleRows = [];
            $total = 0.0;

            foreach ($itemCollection as $line) {
                /** @var Products|null $product */
                $product = $products->get($line['product_id']);
                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => ['One or more products no longer exist.'],
                    ]);
                }

                if ($product->state !== 'active') {
                    throw ValidationException::withMessages([
                        'items' => ["Product {$product->id} is inactive."],
                    ]);
                }

                if ((int) $product->stock < (int) $line['quantity']) {
                    throw new ConflictHttpException("Insufficient stock for product {$product->id}.");
                }

                if (isset($validated['branch_id']) && (int) $product->category->id_branches !== (int) $validated['branch_id']) {
                    throw ValidationException::withMessages([
                        'items' => ["Product {$product->id} does not belong to branch {$validated['branch_id']}."],
                    ]);
                }

                $finalUnitPrice = $this->discountedUnitPrice((float) $product->unit_price, (int) $product->promotion_discount);
                $subTotal = round($finalUnitPrice * (int) $line['quantity'], 2);
                $discountValue = round((float) $product->unit_price - $finalUnitPrice, 2);

                $saleRows[] = [
                    'product' => $product,
                    'quantity' => (int) $line['quantity'],
                    'unit_price' => $finalUnitPrice,
                    'discount' => $discountValue,
                    'sub_total' => $subTotal,
                ];

                $total += $subTotal;
            }

            $sale = Sale::create([
                'id_customers' => $customer->id,
                'id_users' => $systemUser->id,
                'voucher_number' => $this->generateVoucherNumber(),
                'igv' => 0.18,
                'total' => round($total, 2),
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'voucher' => $validated['voucher'] ?? 'ticket',
                'document' => $validated['document'] ?? null,
            ]);

            foreach ($saleRows as $row) {
                SaleDetail::create([
                    'id_sales' => $sale->id,
                    'id_products' => $row['product']->id,
                    'quantity' => $row['quantity'],
                    'discount' => $row['discount'],
                    'sub_total' => $row['sub_total'],
                ]);

                $row['product']->decrement('stock', $row['quantity']);
            }

            return [
                'sale' => $sale->fresh(),
                'items' => collect($saleRows)->map(fn(array $row) => [
                    'product_id' => $row['product']->id,
                    'name' => $row['product']->name,
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'discount' => $row['discount'],
                    'sub_total' => $row['sub_total'],
                ])->values(),
            ];
        });

        return response()->json([
            'message' => 'Checkout completed successfully.',
            'data' => [
                'sale_id' => $checkout['sale']->id,
                'voucher_number' => $checkout['sale']->voucher_number,
                'total' => (float) $checkout['sale']->total,
                'payment_method' => $checkout['sale']->payment_method,
                'voucher' => $checkout['sale']->voucher,
                'items' => $checkout['items'],
            ],
        ], 201);
    }

    private function productPayload(Products $product): array
    {
        $finalPrice = $this->discountedUnitPrice((float) $product->unit_price, (int) $product->promotion_discount);

        return [
            'id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'description' => $product->description,
            'unit_price' => (float) $product->unit_price,
            'promotion_discount' => (int) $product->promotion_discount,
            'final_price' => $finalPrice,
            'stock' => (int) $product->stock,
            'image' => $product->image,
            'category' => [
                'id' => $product->category?->id,
                'name' => $product->category?->name,
            ],
            'branch' => [
                'id' => $product->category?->branch?->id,
                'name' => $product->category?->branch?->name,
            ],
        ];
    }

    private function discountedUnitPrice(float $unitPrice, int $discount): float
    {
        $discount = min(max($discount, 0), 100);
        return round($unitPrice * (1 - ($discount / 100)), 2);
    }

    private function systemCashier(): User
    {
        $user = User::query()->where('email', 'systemcashier@minimarket.local')->first();
        if (! $user) {
            throw ValidationException::withMessages([
                'system' => ['System cashier user was not found. Run seeders.'],
            ]);
        }

        return $user;
    }

    private function resolveCustomer(?array $customerData): Customer
    {
        if ($customerData && isset($customerData['id'])) {
            return Customer::query()->findOrFail((int) $customerData['id']);
        }

        if ($customerData) {
            return Customer::query()->firstOrCreate(
                ['email' => $customerData['email']],
                [
                    'dni' => $customerData['dni'],
                    'name' => $customerData['name'],
                    'last_name' => $customerData['last_name'],
                    'birthday' => $customerData['birthday'] ?? '2000-01-01',
                    'phone' => $customerData['phone'] ?? '900000001',
                    'address' => $customerData['address'] ?? 'Sin dirección',
                    'score' => 0,
                    'state' => 'active',
                ]
            );
        }

        $guest = Customer::query()->where('email', 'guest@minimarket.local')->first();

        if (! $guest) {
            throw ValidationException::withMessages([
                'system' => ['Guest customer was not found. Run seeders.'],
            ]);
        }

        return $guest;
    }

    private function generateVoucherNumber(): string
    {
        do {
            $voucherNumber = 'WS-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6));
        } while (Sale::query()->where('voucher_number', $voucherNumber)->exists());

        return $voucherNumber;
    }
}
