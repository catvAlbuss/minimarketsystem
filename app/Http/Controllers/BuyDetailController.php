<?php

namespace App\Http\Controllers;

use App\Models\BuyDetail;
use App\Http\Controllers\Controller;
use App\Models\Buy;
use App\Models\Products;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BuyDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $buy_details = BuyDetail::all();
        $buys = Buy::all();
        $products = Products::all();
        return Inertia::render('buy_details/index', [
            'buy_details' => $buy_details,
            'buys' => $buys,
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
            'id_buys' => 'required|exists:buys,id',
            'id_products' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'sub_total' => 'required|numeric|min:0',
        ]);

        BuyDetail::create([
            'id_buys' => $validateData['id_buys'],
            'id_products' => $validateData['id_products'],
            'quantity' => $validateData['quantity'],
            'unit_price' => $validateData['unit_price'],
            'sub_total' => $validateData['sub_total'],
        ]);

        return to_route('buy_details.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(BuyDetail $buyDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BuyDetail $buyDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BuyDetail $buyDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BuyDetail $buyDetail)
    {
        //
    }
}
