<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Http\Controllers\Controller;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

// use Illuminate\Http\Request;


class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $products = Products::all();
        $categories = Category::all();
        return Inertia::render('products/index', [
            'products' => $products,
            'categories' => $categories,
        ]);

        //

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validateData = $request->validate([
            'id_categories' => 'required|exists:categories,id',
            'code' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'unit_price' => 'required|numeric|min:0',
            'higher_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'expiration_date' => 'required|date',
            'promotion_discount' => 'required|integer|min:0|max:100',
            'state' => 'required|in:active,inactive',
        ]);

        Products::create([
            'id_categories' => $validateData['id_categories'],
            'code' => $validateData['code'],
            'name' => $validateData['name'],
            'description' => $validateData['description'],
            'unit_price' => $validateData['unit_price'],
            'higher_price' => $validateData['higher_price'],
            'stock' => $validateData['stock'],
            'expiration_date' => $validateData['expiration_date'],
            'promotion_discount' => $validateData['promotion_discount'],
            'state' => $validateData['state'],
        ]);

        return to_route('products.index');

        //

    }

    /**
     * Display the specified resource.
     */
    public function show(Products $products)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Products $products)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, String $productsid)
    {
        $products = Products::query()->findOrFail($productsid);

        $validateData = $request->validate([
            'id_categories' => 'required|exists:categories,id',
            // 'code' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'unit_price' => 'required|numeric|min:0',
            'higher_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'expiration_date' => 'required|date',
            'promotion_discount' => 'required|integer|min:0|max:100',
            'state' => 'required|in:active,inactive',
        ]);

        $payload = [
            'id_categories' => $validateData['id_categories'],
            // 'code' => $validateData['code'],
            'name' => $validateData['name'],
            'description' => $validateData['description'],
            'unit_price' => $validateData['unit_price'],
            'higher_price' => $validateData['higher_price'],
            'stock' => $validateData['stock'],
            'expiration_date' => $validateData['expiration_date'],
            'promotion_discount' => $validateData['promotion_discount'],
            'state' => $validateData['state'],
        ];

        $products->update($payload);
        return to_route('products.index');

        // public function update(Request $request, Products $products)
        // {
        //

    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(String $productsid)
    {
        $products = Products::query()->findOrFail($productsid);

        $products->delete();
        return to_route('products.index');

        // public function destroy(Products $products)
        // {
        //     //

    }
}
