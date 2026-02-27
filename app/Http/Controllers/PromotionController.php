<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Http\Controllers\Controller;
use App\Models\Products;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $promotions = Promotion::all();
        $products = Products::select('id','name','unit_price','promotion_discount')->get();
        return Inertia::render('promotions/index',[
            'promotions' =>$promotions,
            'products' => $products,
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
        //
        foreach($request->item as $item){
            Promotion::create([
                'id_products' => $item['id'],
                'name_promotion' => $request['name_promotion'],
                'price' => $request['price'],
                'state' => $request['state']
            ]);
        };

        return to_route('promotions.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Promotion $promotion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Promotion $promotion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Promotion $promotion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Promotion $promotion)
    {
        //
    }
}
