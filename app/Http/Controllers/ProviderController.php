<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Http\Controllers\Controller;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $providers = Provider::all();
        $products = Products::all();
        return Inertia::render('providers/index', [
            'providers' => $providers,
            'products' => $products
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
        Log:info($request);
        
        $validate = $request->validate([
            'id_products'=>['required','integer'],
            'ruc'=>['required','string','max:11'],
            'company_name'=>['required','string','max:250'],
            'contact_person'=>['required','string','max:250'],
            'phone'=>['required','integer','digits:9'],
            'email'=>['required', 'string','email'],
            'address'=>['required','string'],
            'category'=>['required','in:wholesaler,retailer,distributor,manufacturer'],
            'description_products'=>['required','string'],
            'status'=>['required','in:active,inactive']
        ]);

        Provider::create([
            'id_products'=>$validate['id_products'],
            'ruc'=>$validate['ruc'],
            'company_name'=>$validate['company_name'],
            'contact_person'=>$validate['contact_person'],
            'phone'=>$validate['phone'],
            'email'=>$validate['email'],
            'address'=>$validate['address'],
            'category'=>$validate['category'],
            'description_products'=>$validate['description_products'],
            'status'=>$validate['status']
        ]);
        return to_route('providers.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Provider $provider)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Provider $provider)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $provider)
    {
        $prov = Provider::query()->findOrFail($provider);

        $validate = $request->validate([
            'id_products'=>['required','integer'],
            'ruc'=>['required','string','max:11'],
            'company_name'=>['required','string','max:250'],
            'contact_person'=>['required','string','max:250'],
            'phone'=>['required','integer','digits:9'],
            'email'=>['required', 'string','email'],
            'address'=>['required','string'],
            'category'=>['required','in:wholesaler,retailer,distributor,manufacturer'],
            'description_products'=>['required','string'],
            'status'=>['required','in:active,inactive']
        ]);

        $payload = [
            'id_products'=>$validate['id_products'],
            'ruc'=>$validate['ruc'],
            'company_name'=>$validate['company_name'],
            'contact_person'=>$validate['contact_person'],
            'phone'=>$validate['phone'],
            'email'=>$validate['email'],
            'address'=>$validate['address'],
            'category'=>$validate['category'],
            'description_products'=>$validate['description_products'],
            'status'=>$validate['status']
        ];

        $prov -> update($payload);
        return to_route('providers.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $providerid)
    {
        //
        $prov = Provider::query()->findOrFail($providerid);
        $prov ->delete();
        return to_route('providers.index');
    }
}
