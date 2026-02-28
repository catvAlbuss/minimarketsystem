<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
class CategoryController extends Controller
{
    public function index()
    {

        $categories = Category::orderBy('name')->get();
        $category = Category::all();
        return Inertia::render('categories/index', [
            'categories' => $category,
        ]);
        //
        // return Inertia::render('categories/index', [
        //     'categories' => $categories,
        // ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:500',
        ]);
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
        ]);

        Category::create([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
        ]);

        return to_route('categories.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $categoryid)
    {
        $category = Category::query()->findOrFail($categoryid);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $payload =[
            'name'=>$validatedData['name'],
            'description'=>$validatedData['description'],
        ];

        $category->update($payload);
        return to_route('categories.index');
        //
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(Category $category, String $categoryid)
    {
        $categories = Category::query()->findOrFail($categoryid);
            
        $categories->delete();
        return to_route('customers.index');
    }
}