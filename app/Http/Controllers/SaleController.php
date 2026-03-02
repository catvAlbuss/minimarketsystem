<?php

namespace App\Http\Controllers;

use App\Concerns\HasBranchScope;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Products;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaleController extends Controller
{
    use HasBranchScope;

    public function index(Request $request)
    {
        $search     = $request->string('search')->trim();
        $branchId   = $request->integer('branch_id') ?: null;
        $categoryId = $request->integer('category_id') ?: null;
        $state      = $request->query('state');   // 'active' | 'inactive' | null

        $sales = Sale::with(['customer', 'user', 'saleDetails.product.category.branch'])
            ->when(! $this->isGlobalUser(), function ($query) {
                // Filter sales to only those whose products belong to the user's branch
                $branchId = $this->currentBranchId();
                $query->whereHas('saleDetails.product.category', function ($q) use ($branchId) {
                    $q->where('id_branches', $branchId);
                });
            })
            ->latest()
            ->get();

        $products = Products::with('category.branch')
            ->when(! $this->isGlobalUser(), fn($q) =>$this->scopeByBranchOnCategories($q))
            ->when($search, fn($q) => $q->where(
                fn($s)=>
                $s->where('name','like',"%{$search}%")
                    ->orWhere('code','like', "%{$search}%")
                    ->orWhere('description','like', "%{$search}%")
            ))
            ->when($branchId,   fn($q) => $q->whereHas('category', fn($c) => $c->where('id_branches', $branchId)))
            ->when($categoryId, fn($q) => $q->where('id_categories', $categoryId))
            ->when($state,      fn($q) => $q->where('state', $state))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        Log:info($products);

        $customers = Customer::all(['id', 'name', 'last_name', 'dni']);
        $users = $this->isGlobalUser()
            ? User::all(['id', 'name', 'lastname'])
            : User::where('branch_id', $this->currentBranchId())->orWhereNull('branch_id')->get(['id', 'name', 'lastname']);

        return Inertia::render('sales/index', [
            'products' => $products,
            'sales' => $sales,
            'customers' => $customers,
            'users' => $users,
            'branches' => $this->availableBranches(),
        ]);
    }

    public function create() {}

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
        ]);

        return to_route('sales.index');
    }

    public function show(Sale $sale) {}
    public function edit(Sale $sale) {}

    public function update(Request $request, string $saleid)
    {
        $sales = Sale::query()->findOrFail($saleid);

        $validateData = $request->validate([
            'id_customers' => 'required|exists:customers,id',
            'id_users' => 'required|exists:users,id',
            'igv' => 'nullable|numeric|min:0|max:1',
            'total' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,yape,plin',
            'voucher' => 'required|in:ticket,invoice',
            'document' => 'nullable|string',
            'date_time' => 'nullable|date',
        ]);

        $sales->update([
            'id_customers' => $validateData['id_customers'],
            'igv' => $validateData['igv'],
            'total' => $validateData['total'],
            'payment_method' => $validateData['payment_method'],
            'voucher' => $validateData['voucher'],
            'document' => $validateData['document'] ?? null,
        ]);

        return to_route('sales.index');
    }

    public function destroy(string $saleid)
    {
        $sales = Sale::query()->findOrFail($saleid);
        $sales->delete();

        return to_route('sales.index');
    }
}
