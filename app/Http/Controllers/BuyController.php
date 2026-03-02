<?php

namespace App\Http\Controllers;

use App\Concerns\HasBranchScope;
use App\Models\Buy;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BuyController extends Controller
{
    use HasBranchScope;

    public function index()
    {
        $buys = Buy::with(['provider', 'user'])
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

        return Inertia::render('buys/index', [
            'buys' => $buys,
            'providers' => $providers,
            'users' => $users,
            'branches' => $this->availableBranches(),
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

    public function destroy(Buy $buyid)
    {
        $buys = Buy::query()->findOrFail($buyid->id);
        $buys->delete();

        return to_route('buys.index');
    }
}
