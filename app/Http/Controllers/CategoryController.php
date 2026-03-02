<?php

namespace App\Http\Controllers;

use App\Concerns\HasBranchScope;
use App\Models\Branch;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    use HasBranchScope;

    public function index()
    {
        $categories = Category::with('branch')
            ->when(! $this->isGlobalUser(), function ($query) {
                $this->scopeByBranchOnCategories($query);
            })
            ->orderBy('name')
            ->get();

        return Inertia::render('categories/index', [
            'categories' => $categories,
            'branches' => $this->availableBranches(),
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_branches' => ['nullable', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['required', 'string', 'max:500'],
        ]);

        // Non-global users are forced to their own branch
        $branchId = $this->isGlobalUser()
            ? ($validatedData['id_branches'] ?? $this->defaultBranchId())
            : $this->currentBranchId();

        Category::create([
            'id_branches' => $branchId,
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
        ]);

        return to_route('categories.index');
    }

    public function update(Request $request, string $categoryid)
    {
        $category = Category::query()->findOrFail($categoryid);

        // Non-global users can only edit categories of their branch
        if (! $this->isGlobalUser()) {
            abort_if(
                (int) $category->id_branches !== $this->currentBranchId(),
                403,
                'No tienes acceso a esta categoría.'
            );
        }

        $validatedData = $request->validate([
            'id_branches' => ['nullable', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:500'],
        ]);

        $branchId = $this->isGlobalUser()
            ? ($validatedData['id_branches'] ?? $category->id_branches ?? $this->defaultBranchId())
            : $this->currentBranchId();

        $category->update([
            'id_branches' => $branchId,
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
        ]);

        return to_route('categories.index');
    }

    public function destroy(string $categoryid)
    {
        $category = Category::query()->findOrFail($categoryid);

        if (! $this->isGlobalUser()) {
            abort_if(
                (int) $category->id_branches !== $this->currentBranchId(),
                403,
                'No tienes acceso a esta categoría.'
            );
        }

        $category->delete();

        return to_route('categories.index');
    }

    private function defaultBranchId(): int
    {
        $branchId = Branch::query()->orderBy('id')->value('id');
        abort_if(! $branchId, 422, 'No branch available to assign category.');

        return (int) $branchId;
    }
}
