<?php

namespace App\Concerns;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Builder;

trait HasBranchScope
{
    /**
     * Get the branch ID the authenticated user is scoped to.
     * Returns null for global roles (root, managment, administrator_general).
     */
    protected function currentBranchId(): ?int
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        return $user->scopedBranchId();
    }

    /**
     * Returns true if the current user has global (cross-branch) access.
     */
    protected function isGlobalUser(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->isGlobalRole();
    }

    /**
     * Apply branch scope to a query on the `categories` table via `id_branches`.
     */
    protected function scopeByBranchOnCategories(Builder $query): Builder
    {
        $branchId = $this->currentBranchId();

        if ($branchId !== null) {
            $query->where('id_branches', $branchId);
        }

        return $query;
    }

    /**
     * Apply branch scope to a query on products (through category → branch).
     */
    protected function scopeByBranchOnProducts(Builder $query): Builder
    {
        $branchId = $this->currentBranchId();

        if ($branchId !== null) {
            $query->whereHas('category', function (Builder $q) use ($branchId) {
                $q->where('id_branches', $branchId);
            });
        }

        return $query;
    }

    /**
     * Get all branches for selects.
     * Global users get all branches; scoped users get only their branch.
     */
    protected function availableBranches()
    {
        if ($this->isGlobalUser()) {
            return Branch::orderBy('name')->get(['id', 'name']);
        }

        $branchId = $this->currentBranchId();

        if ($branchId) {
            return Branch::where('id', $branchId)->get(['id', 'name']);
        }

        return collect();
    }
}
