<?php

namespace App\Http\Controllers;

use App\Concerns\HasBranchScope;
use App\Models\Products;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PromotionController extends Controller
{
    use HasBranchScope;

    public function index()
    {
        $promotions = Promotion::with('product.category.branch')
            ->when(! $this->isGlobalUser(), function ($query) {
                $branchId = $this->currentBranchId();
                $query->whereHas('product.category', function ($q) use ($branchId) {
                    $q->where('id_branches', $branchId);
                });
            })
            ->get();

        $products = Products::select('id', 'name', 'unit_price', 'promotion_discount', 'image')
            ->when(! $this->isGlobalUser(), fn($q) => $this->scopeByBranchOnProducts($q))
            ->get();

        return Inertia::render('promotions/index', [
            'promotions' => $promotions,
            'products' => $products,
            'branches' => $this->availableBranches(),
        ]);
    }

    public function create() {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item' => ['required', 'array', 'min:1'],
            'item.*.id' => ['required', 'exists:products,id'],
            'name_promotion' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'],
            'price' => ['required', 'numeric', 'min:0'],
            'state' => ['required', 'in:active,inactive'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('promotions', 'public');
        }

        foreach ($validated['item'] as $item) {
            Promotion::create([
                'id_products' => $item['id'],
                'name_promotion' => $validated['name_promotion'],
                'image' => $imagePath,
                'price' => $validated['price'],
                'state' => $validated['state'],
            ]);
        }

        return to_route('promotions.index');
    }

    public function show(Promotion $promotion) {}
    public function edit(Promotion $promotion) {}

    public function update(Request $request, string $id_promotion)
    {
        $promotion = Promotion::query()->findOrFail($id_promotion);
        $oldName = $promotion->getOriginal('name_promotion');

        $validated = $request->validate([
            'item' => ['required', 'array', 'min:1'],
            'item.*.id' => ['required', 'exists:products,id'],
            'name_promotion' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'],
            'price' => ['required', 'numeric', 'min:0'],
            'state' => ['required', 'in:active,inactive'],
        ]);

        // Resolve image
        $imagePath = Promotion::where('name_promotion', $oldName)->value('image');
        if ($request->hasFile('image')) {
            $oldPath = $this->promotionImagePathForDeletion($imagePath);
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
            $imagePath = $request->file('image')->store('promotions', 'public');
        }

        Promotion::where('name_promotion', $oldName)->delete();

        foreach ($validated['item'] as $prod) {
            Promotion::create([
                'id_products' => $prod['id'],
                'name_promotion' => $validated['name_promotion'],
                'image' => $imagePath,
                'price' => $validated['price'],
                'state' => $validated['state'],
            ]);
        }

        return to_route('promotions.index');
    }

    public function destroy(string $id_promotion)
    {
        $promotion = Promotion::query()->findOrFail($id_promotion);
        $nameDelete = $promotion->name_promotion;

        // Delete image
        $imagePath = Promotion::where('name_promotion', $nameDelete)->value('image');
        $oldPath = $this->promotionImagePathForDeletion($imagePath);
        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        Promotion::where('name_promotion', $nameDelete)->delete();

        return to_route('promotions.index');
    }

    private function promotionImagePathForDeletion(?string $imagePath): ?string
    {
        if (! $imagePath) {
            return null;
        }

        $normalized = str_replace('\\', '/', $imagePath);

        if (str_starts_with($normalized, 'http://') || str_starts_with($normalized, 'https://')) {
            $path = parse_url($normalized, PHP_URL_PATH) ?? '';
            return $path ? ltrim(str_replace('/storage/', '', $path), '/') : null;
        }

        if (str_starts_with($normalized, '/storage/')) {
            return ltrim(str_replace('/storage/', '', $normalized), '/');
        }

        if (str_starts_with($normalized, 'storage/')) {
            return ltrim(substr($normalized, 8), '/');
        }

        if (str_starts_with($normalized, 'public/')) {
            return ltrim(substr($normalized, 7), '/');
        }

        return ltrim($normalized, '/');
    }
}
