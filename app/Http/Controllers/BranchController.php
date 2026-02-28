<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    /**
     * Lista las sucursales.
     */
    public function index(): Response
    {
        // Cargamos también el usuario encargado para mostrarlo si es necesario
        $branches = Branch::with('user')->get(); 
        
        return Inertia::render('branches/index', [
            'branches' => $branches,
            // Enviamos los usuarios para el selector al crear/editar
            'users' => User::all(['id', 'name']) 
        ]);
    }

    /**
     * Guarda una nueva sucursal.
     */
    public function store(Request $request)
    {
       $validated = $request->validate([
            'id_users'     => ['required','exists:users,id'],
            'name'         => ['required','string','max:255'],
            'address'      => ['required','string'],
            'opening_time' => ['required'],
            'closing_time' => ['required'],
            'state'        => ['required','in:active,inactive'],
        ]);


        Branch::create([
            'id_users'     => $validated['id_users'],
            'name'         => $validated['name'],
            'address'      => $validated['address'],
            'opening_time' => $validated['opening_time'],
            'closing_time' => $validated['closing_time'],
            'state'        => $validated['state'],
        ]);

        return to_route('branches.index');
    }

    /**
     * Actualiza una sucursal existente.
     */
    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'id_users'     => 'required|exists:users,id',
            'name'         => 'required|string|max:255',
            'address'      => 'required|string',
            'opening_time' => 'required',
            'closing_time' => 'required',
            'state'        => 'required|in:active,inactive',
        ]);

        $branch->update($validated);

        return redirect()->back()->with('message', 'Sucursal actualizada');
    }

    /**
     * Elimina una sucursal.
     */
    public function destroy(Branch $branch)
    {
        $branch->delete();
        return redirect()->back()->with('message', 'Sucursal eliminada');
    }
}