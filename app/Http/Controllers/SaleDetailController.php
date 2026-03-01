<?php

namespace App\Http\Controllers;

use App\Models\SaleDetail;
use App\Http\Controllers\Controller;
use App\Models\Products;
use App\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaleDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sale_details = SaleDetail::all();
        $sales = Sale::all();
        $products = Products::all();
        return Inertia::render('sale_details/index', [
            'sale_details' => $sale_details,
            'sales' => $sales,
            'products' => $products,
            // 'users' => $users,
        ]);
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
            'id_sales' => 'required|exists:sales,id',
            'id_products' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'discount' => 'required|numeric|min:0',
            'sub_total' => 'required|numeric|min:0',
        ]);

        SaleDetail::create([
            'id_sales' => $validateData['id_sales'],
            'id_products' => $validateData['id_products'],
            'quantity' => $validateData['quantity'],
            'discount' => $validateData['discount'],
            'sub_total' => $validateData['sub_total'],
        ]);
        return to_route('sale_details.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(SaleDetail $saleDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SaleDetail $saleDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SaleDetail $saleDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SaleDetail $saleDetail)
    {
        //
    }
}
