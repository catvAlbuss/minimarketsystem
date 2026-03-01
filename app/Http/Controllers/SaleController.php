<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Http\Controllers\Controller;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

// use Illuminate\Http\Request;


class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $sales = Sale::all();
        $customers = Customer::all();
        $users = User::all();
        return Inertia::render('sales/index', [
            'sales' => $sales,
            'customers' => $customers,
            'users' => $users,
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
            'id_customers' => 'required|exists:customers,id',
            'id_users' => 'required|exists:users,id',
            'voucher_number' => 'required|string|max:255|unique:sales,voucher_number',
            'igv' => 'nullable|numeric|min:0|max:1',
            'total' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,yape,plin',
            'voucher' => 'required|in:ticket,invoice',
            'document' => 'nullable|string',
            'date_time' => 'nullable|date',
        ]);

        Sale::create([
            'id_customers' => $validateData['id_customers'],
            'id_users' => $validateData['id_users'],
            'voucher_number' => $validateData['voucher_number'],
            'igv' => $validateData['igv'],
            'total' => $validateData['total'],
            'payment_method' => $validateData['payment_method'],
            'voucher' => $validateData['voucher'],
            'document' => $validateData['document'],
            // 'date_time' => $validateData['date_time'],
        ]);
        return to_route('sales.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $saleid)
    {
        $sales = Sale::query()->findOrFail($saleid);

        $validateData = $request->validate([
            'id_customers' => 'required|exists:customers,id',
            'id_users' => 'required|exists:users,id',
            // 'voucher_number' => 'required|string|max:255|unique:sales,voucher_number',
            'igv' => 'nullable|numeric|min:0|max:1',
            'total' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,yape,plin',
            'voucher' => 'required|in:ticket,invoice',
            'document' => 'nullable|string',
            'date_time' => 'nullable|date',
        ]);

        $payload = [
            'id_customers' => $validateData['id_customers'],
            // 'id_users'=>$validateData['id_users'],
            // 'voucher_number'=>$validateData['voucher_number'],
            'igv' => $validateData['igv'],
            'total' => $validateData['total'],
            'payment_method' => $validateData['payment_method'],
            'voucher' => $validateData['voucher'],
            'document' => $validateData['document'] ?? null,
            // 'date_time'=>$validateData['date_time'],
        ];

        $sales->update($payload);
        return to_route('sales.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $saleid)
    {
        $sales = Sale::query()->findOrFail($saleid);

        $sales->delete();
        return to_route('sales.index');
    }
}
