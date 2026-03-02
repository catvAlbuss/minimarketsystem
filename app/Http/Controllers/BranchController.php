<?php

namespace App\Http\Controllers;

use App\Concerns\HasBranchScope;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    use HasBranchScope;

    /**
     * Lista las sucursales. Solo usuarios globales ven todas; los demás no tienen acceso.
     */
    public function index(): Response
    {
        abort_if(! $this->isGlobalUser(), 403, 'Solo los administradores globales pueden gestionar sucursales.');

        $branches = Branch::with('user')->get();

        return Inertia::render('branches/index', [
            'branches' => $branches,
            'users' => User::all(['id', 'name']),
        ]);
    }

    /**
     * Guarda una nueva sucursal. Solo usuarios globales.
     */
    public function store(Request $request)
    {
        abort_if(! $this->isGlobalUser(), 403, 'Solo los administradores globales pueden crear sucursales.');

        $validated = $request->validate([
            'id_users' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'opening_time' => ['required'],
            'closing_time' => ['required'],
            'state' => ['required', 'in:active,inactive'],
        ]);

        Branch::create($validated);

        return to_route('branches.index');
    }

    /**
     * Actualiza una sucursal existente. Solo usuarios globales.
     */
    public function update(Request $request, Branch $branch)
    {
        abort_if(! $this->isGlobalUser(), 403, 'Solo los administradores globales pueden editar sucursales.');

        $validated = $request->validate([
            'id_users' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'opening_time' => 'required',
            'closing_time' => 'required',
            'state' => 'required|in:active,inactive',
        ]);

        $branch->update($validated);

        return redirect()->back()->with('message', 'Sucursal actualizada');
    }

    /**
     * Elimina una sucursal. Solo usuarios globales.
     */
    public function destroy(Branch $branch)
    {
        abort_if(! $this->isGlobalUser(), 403, 'Solo los administradores globales pueden eliminar sucursales.');

        $branch->delete();

        return redirect()->back()->with('message', 'Sucursal eliminada');
    }
}
