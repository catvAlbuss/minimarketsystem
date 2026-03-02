<?php

namespace App\Http\Controllers;

use App\Concerns\HasBranchScope;
use App\Models\Buy;
use App\Models\Provider;
use App\Models\User;
use App\Models\Products;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BuyController extends Controller
{
    use HasBranchScope;

    public function index()
    {
        $buys = Buy::with(['provider', 'user'])
            ->withCount('buyDetails')
            ->when(! $this->isGlobalUser(), function ($query) {
                // Filter buys by the user who belongs to this branch
                $branchId = $this->currentBranchId();
                $query->whereHas('user', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            })
            ->latest()
            ->get();

        $providers = Provider::all();
        $users = $this->isGlobalUser()
            ? User::all(['id', 'name', 'lastname'])
            : User::where('branch_id', $this->currentBranchId())->orWhereNull('branch_id')->get(['id', 'name', 'lastname']);

        // Products with low stock (<=5) to suggest reorders. Status: 0 -> 'red', 1-5 -> 'yellow'
        $lowStockProducts = Products::where('stock', '<=', 5)
            ->orderBy('stock', 'asc')
            ->get()
            ->map(function ($p) {
                $p->reorder_level = $p->stock === 0 ? 'red' : 'yellow';
                return $p;
            });

        return Inertia::render('buys/index', [
            'buys' => $buys,
            'providers' => $providers,
            'users' => $users,
            'branches' => $this->availableBranches(),
            'low_stock_products' => $lowStockProducts,
        ]);
    }

    public function create() {}

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
        ]);

        return to_route('buys.index');
    }

    public function show(Buy $buy) {}
    public function edit(Buy $buy) {}

    public function update(Request $request, string $buyid)
    {
        $buys = Buy::query()->findOrFail($buyid);

        $validateData = $request->validate([
            'id_provider' => 'required|exists:providers,id',
            'id_users' => 'required|exists:users,id',
            'voucher_number' => 'nullable|string|max:255|unique:buys,voucher_number,' . $buys->id,
            'total' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,yape,plin',
            'payment_status' => 'required|in:cancelled,pending,delivered',
            'date_time' => 'nullable|date',
        ]);

        $buys->update([
            'id_provider' => $validateData['id_provider'],
            'total' => $validateData['total'],
            'payment_method' => $validateData['payment_method'],
            'payment_status' => $validateData['payment_status'],
        ]);

        return to_route('buys.index');
    }

    public function destroy(Buy $buy)
    {
        $buy->delete();

        return to_route('buys.index');
    }

    /**
     * Create a buy order and corresponding buy details in a single request.
     */
    public function order(Request $request)
    {
        $data = $request->validate([
            'id_provider' => 'required|exists:providers,id',
            'items' => 'required|array|min:1',
            'items.*.id_products' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'nullable|in:cash,card,yape,plin',
        ]);

        $userId = Auth::id() ?? $request->input('id_users');

        $voucher = 'ORDER-' . time();

        DB::transaction(function () use ($data, $userId, $voucher) {
            $total = 0;

            $buy = Buy::create([
                'id_provider' => $data['id_provider'],
                'id_users' => $userId,
                'voucher_number' => $voucher,
                'total' => 0, // temporary, will update
                'payment_method' => $data['payment_method'] ?? 'cash',
                'payment_status' => 'pending',
            ]);

            foreach ($data['items'] as $item) {
                $product = Products::findOrFail($item['id_products']);
                $subTotal = ($product->unit_price ?? 0) * $item['quantity'];
                $total += $subTotal;

                $buy->buyDetails()->create([
                    'id_products' => $item['id_products'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->unit_price ?? 0,
                    'sub_total' => $subTotal,
                ]);
            }

            $buy->update(['total' => $total]);

        });

        return to_route('buys.index')->with('success', 'Pedido creado correctamente.');
    }
}
