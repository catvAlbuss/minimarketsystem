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
        $validate = $request->validate([
            'item' => ['required','array'],
            'name_promotion' => ['required','string'],
            'price' => ['required','numeric'],
            'state' => ['required','in:active,inactive']       
        ]);

        foreach($validate['item'] as $item){
            Promotion::create([
                'id_products' => $item['id'],
                'name_promotion' => $validate['name_promotion'],
                'price' => $validate['price'],
                'state' => $validate['state']
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
    public function update(Request $request, $id_promotion)
    {
        //
        $promotion = Promotion::findOrFail($id_promotion);
        $oldName = $promotion->getOriginal('name_promotion');

        $validate = $request->validate([
            'item' => ['required','array'],
            'name_promotion' => ['required','string'],
            'price' => ['required','numeric'],
            'state' => ['required','in:active,inactive']       
        ]);

        Promotion::where('name_promotion',$oldName)->delete();

        foreach ($validate['item'] as $prod) {
            Promotion::create([
                'id_products' => $prod['id'],
                'name_promotion'=> $validate['name_promotion'],
                'price' => $validate['price'],
                'state' => $validate['state']
            ]);
        }

        return to_route('promotions.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id_promotion)
    {
        $promotion = Promotion::findOrFail($id_promotion);
        $nameDelete = $promotion->name_promotion;

        Promotion::where('name_promotion', $nameDelete)->delete();

        return to_route('promotions.index');
    }
}
