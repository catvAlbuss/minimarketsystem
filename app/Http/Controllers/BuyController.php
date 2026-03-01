<?php

namespace App\Http\Controllers;

use App\Models\Buy;
use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use phpDocumentor\Reflection\Types\String_;

class BuyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $buys = Buy::all();
        $providers = Provider::all();
        $users = User::all();
        return Inertia::render('buys/index', [
            'buys' => $buys,
            'providers' => $providers,
            'users' => $users,
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
            'id_provider' => 'required|exists:providers,id',
            'id_users' => 'required|exists:users,id',
            'voucher_number' => 'required|string|max:255|unique:buys,voucher_number',
            'total' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,yape,plin',
            'payment_status' => 'required|in:cancelled,pending,delivered',
            'date_time' => 'nullable|date',
        ]);

        Buy::create([
            'id_provider' => $validateData['id_provider'],
            'id_users' => $validateData['id_users'],
            'voucher_number' => $validateData['voucher_number'],
            'total' => $validateData['total'],
            'payment_method' => $validateData['payment_method'],
            'payment_status' => $validateData['payment_status'],
            // 'date_time'=>$validateData['date_time'],
        ]);
        return to_route('buys.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Buy $buy)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Buy $buy)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $buyid)
    {
        $buys = Buy::query()->findOrFail($buyid);

        $validateData = $request->validate([
            'id_provider' => 'required|exists:provider,id',
            'id_users' => 'required|exists:users,id',
            'voucher_number' => 'required|string|max:255|unique:buys,voucher_number',
            'total' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,yape,plin',
            'payment_status' => 'required|in:cancelled,pending,delivered',
            'date_time' => 'nullable|date',
        ]);

        $payload = [
            'id_provider' => $validateData['id_provider'],
            // 'id_users' => $validateData['id_users'],
            // 'voucher_number' => $validateData['voucher_number'],
            'total' => $validateData['total'],
            'payment_method' => $validateData['payment_method'],
            'payment_status' => $validateData['payment_status'],
            // 'date_time'=>$validateData['date_time'],
        ];

        $buys->update($payload);
        return to_route('buys.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Buy $buyid)
    {
        $buys = Buy::query()->findOrFail($buyid);

        $buys->delete();
        return to_route('buys.index');
    }
}
