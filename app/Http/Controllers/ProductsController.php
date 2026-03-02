<?php

namespace App\Http\Controllers;

use App\Concerns\HasBranchScope;
use App\Models\Category;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProductsController extends Controller
{
    use HasBranchScope;

    public function index(Request $request)
    {
        $search     = $request->string('search')->trim();
        $branchId   = $request->integer('branch_id') ?: null;
        $categoryId = $request->integer('category_id') ?: null;
        $state      = $request->query('state');   // 'active' | 'inactive' | null

        $products = Products::with('category.branch')
            ->when(! $this->isGlobalUser(), fn($q) => $this->scopeByBranchOnProducts($q))
            ->when($search,     fn($q) => $q->where(
                fn($s) =>
                $s->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
            ))
            ->when($branchId,   fn($q) => $q->whereHas('category', fn($c) => $c->where('id_branches', $branchId)))
            ->when($categoryId, fn($q) => $q->where('id_categories', $categoryId))
            ->when($state,      fn($q) => $q->where('state', $state))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $categories = Category::with('branch')
            ->when(! $this->isGlobalUser(), fn($q) => $this->scopeByBranchOnCategories($q))
            ->orderBy('name')
            ->get(['id', 'name', 'id_branches']);

        return Inertia::render('products/index', [
            'products'   => $products,
            'categories' => $categories,
            'branches'   => $this->availableBranches(),
            'filters'    => [
                'search'      => $search->toString(),
                'branch_id'   => $branchId,
                'category_id' => $categoryId,
                'state'       => $state,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_categories' => ['required', 'exists:categories,id'],
            'code' => ['required', 'string', 'max:100', 'unique:products,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'higher_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'expiration_date' => ['required', 'date'],
            'promotion_discount' => ['required', 'integer', 'min:0', 'max:100'],
            'state' => ['required', 'in:active,inactive'],
        ]);

        // Enforce branch ownership for non-global users
        if (! $this->isGlobalUser()) {
            $category = Category::findOrFail($validatedData['id_categories']);
            abort_if(
                (int) $category->id_branches !== $this->currentBranchId(),
                403,
                'La categoría no pertenece a tu sucursal.'
            );
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        Products::create([
            ...$validatedData,
            'image' => $imagePath,
        ]);

        return to_route('products.index');
    }

    public function update(Request $request, string $productsid)
    {
        $products = Products::query()->findOrFail($productsid);

        // Non-global users can only edit products in their branch
        if (! $this->isGlobalUser()) {
            abort_if(
                (int) $products->category->id_branches !== $this->currentBranchId(),
                403,
                'No tienes acceso a este producto.'
            );
        }

        $validatedData = $request->validate([
            'id_categories' => ['required', 'exists:categories,id'],
            'code' => ['nullable', 'string', 'max:100', Rule::unique('products', 'code')->ignore($products->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'higher_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'expiration_date' => ['required', 'date'],
            'promotion_discount' => ['required', 'integer', 'min:0', 'max:100'],
            'state' => ['required', 'in:active,inactive'],
        ]);

        $imagePath = $products->getRawOriginal('image'); // keep existing stored path
        if ($request->hasFile('image')) {
            $oldPath = $products->imagePathForDeletion();
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $products->update([
            'id_categories' => $validatedData['id_categories'],
            'code' => $validatedData['code'] ?? $products->code,
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'image' => $imagePath,
            'unit_price' => $validatedData['unit_price'],
            'higher_price' => $validatedData['higher_price'],
            'stock' => $validatedData['stock'],
            'expiration_date' => $validatedData['expiration_date'],
            'promotion_discount' => $validatedData['promotion_discount'],
            'state' => $validatedData['state'],
        ]);

        return to_route('products.index');
    }

    public function destroy(string $productsid)
    {
        $products = Products::query()->findOrFail($productsid);

        if (! $this->isGlobalUser()) {
            abort_if(
                (int) $products->category->id_branches !== $this->currentBranchId(),
                403,
                'No tienes acceso a este producto.'
            );
        }

        $oldPath = $products->imagePathForDeletion();
        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        $products->delete();

        return to_route('products.index');
    }

    public function show(Products $products) {}
    public function edit(Products $products) {}
}
