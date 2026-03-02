<?php

namespace App\Http\Controllers;

use App\Concerns\HasBranchScope;
use App\Models\Branch;
use App\Models\Products;
use App\Models\Sale;
use Inertia\Inertia;

class DashboardController extends Controller
{
    use HasBranchScope;

    public function index()
    {
        /** @var \App\Models\User $user */
        $user     = auth()->user();
        $isGlobal = $user->isGlobalRole();
        $branchId = $user->scopedBranchId();

        // ── Base queries scoped by branch ───────────────────────────────
        $salesQ    = Sale::query();
        $productsQ = Products::query()->where('state', 'active');

        if (! $isGlobal && $branchId) {
            $salesQ->whereHas(
                'saleDetails.product.category',
                fn($q) => $q->where('id_branches', $branchId)
            );
            $productsQ->whereHas(
                'category',
                fn($q) => $q->where('id_branches', $branchId)
            );
        }

        // ── Stats ────────────────────────────────────────────────────────
        $todaySales   = (clone $salesQ)->whereDate('created_at', today())->count();
        $todayRevenue = (clone $salesQ)->whereDate('created_at', today())->sum('total');
        $totalRevenue = (clone $salesQ)->sum('total');
        $lowStock     = (clone $productsQ)->where('stock', '<=', 5)->count();
        $totalProds   = (clone $productsQ)->count();
        $withDiscount = (clone $productsQ)->where('promotion_discount', '>', 0)->count();

        // ── Branches (global = all; scoped = only theirs) ───────────────
        $branchesQ = Branch::withCount(['categories', 'users']);
        if (! $isGlobal && $branchId) {
            $branchesQ->where('id', $branchId);
        }
        $branches = $branchesQ->get(['id', 'name', 'address', 'state', 'opening_time', 'closing_time']);

        // ── Role label ──────────────────────────────────────────────────
        $roleLabel = $user->getRoleNames()->first()
            ?? $user->role
            ?? 'sin rol';

        return Inertia::render('Dashboard', [
            'stats' => [
                'today_sales'   => $todaySales,
                'today_revenue' => round((float) $todayRevenue, 2),
                'total_revenue' => round((float) $totalRevenue, 2),
                'low_stock'     => $lowStock,
                'total_products' => $totalProds,
                'with_discount' => $withDiscount,
            ],
            'branches'   => $branches,
            'userBranch' => $user->branch
                ? ['id' => $user->branch->id, 'name' => $user->branch->name]
                : null,
            'isGlobal'   => $isGlobal,
            'roleLabel'  => $roleLabel,
        ]);
    }
}
