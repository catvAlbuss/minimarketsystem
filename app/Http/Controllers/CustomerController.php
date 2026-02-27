<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Http\Controllers\Controller;
use GuzzleHttp\Promise\Create;
use Illuminate\Container\Attributes\Log;

use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $customer = Customer::all();
        return Inertia::render('customers/index', [
            'customers' => $customer,
        ]);
        $customer = Customer::all();
        //inertia
        //json
        // return response()->json($categories);
        return Inertia::render('customers/index', [
            'customers' => $customer,
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
        // Log:
        // info($request);

        $validatedData = $request->validate([
            'dni' => 'required|regex:/^\d{8}$/|unique:customers,dni',
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birthday' => 'required|date',
            'email' => 'required|email|max:255|unique:customers,email',
            'phone' => 'required|string|max:9',
            'address' => 'required|string|max:255',
            'score' => 'required|integer|min:0',
            'state' => 'required|in:active,inactive',
        ]);

        Customer::create([
            'dni' => $validatedData['dni'],
            'name' => $validatedData['name'],
            'last_name' => $validatedData['last_name'],
            'birthday' => $validatedData['birthday'],
            'email' => $validatedData['email'],
            'phone' => $validatedData['phone'],
            'address' => $validatedData['address'],
            'score' => $validatedData['score'],
            'state' => $validatedData['state'],
        ]);

        // return response()->json([
        //     'message' => 'Cliente creado exitosamente',
        //     'data' => $customer,
        // ], 201);

        return to_route('customers.index');
        // 
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $customerid)
    {

        $customer = Customer::query()->findOrFail($customerid);

        $validatedData = $request->validate([
            // 'dni' => 'required|regex:/^\d{8}$/|unique:customers,dni',
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birthday' => 'required|date',
            // 'email' => 'required|email|max:255|unique:customers,email',
            'phone' => 'required|string|max:9',
            'address' => 'required|string|max:255',
            'score' => 'required|integer|min:0',
            'state' => 'required|in:active,inactive',
        ]);

        $payload = [
            // 'dni' => $validatedData['dni'],
            'name' => $validatedData['name'],
            'last_name' => $validatedData['last_name'],
            'birthday' => $validatedData['birthday'],
            // 'email' => $validatedData['email'],
            'phone' => $validatedData['phone'],
            'address' => $validatedData['address'],
            'score' => $validatedData['score'],
            'state' => $validatedData['state'],
        ];

        $customer->update($payload);
        return to_route('customers.index');

        // public function update(Request $request, Customer $customer)
        // {
        // 
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(Request $request, String $customerid)
    {
        $customer = Customer::query()->findOrFail($customerid);

        $customer->delete();
        return to_route('customers.index');

        // public function destroy(Customer $customer)
        // {
        //     //

    }
}
