<?php

namespace App\Http\Controllers;

use App\Concerns\HasBranchScope;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    use HasBranchScope;

    public function index(): Response
    {
        $users = User::with('branch')
            ->when(! $this->isGlobalUser(), function ($query) {
                $query->where('branch_id', $this->currentBranchId());
            })
            ->get();

        return Inertia::render('users/index', [
            'users' => $users,
            'branches' => $this->availableBranches(),
        ]);
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'name' => ['required', 'string', 'max:250'],
            'lastname' => ['required', 'string', 'max:250'],
            'dni' => ['required', 'integer', 'digits:8'],
            'phone' => ['required', 'integer', 'digits:9'],
            'address' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'children' => ['required', 'integer'],
            'affiliate' => ['required', 'in:ONP,AFP'],
            'insured' => ['required', 'in:EsSalud,SIS'],
            'work_modality' => ['required', 'in:fullTime,partTime'],
            'entry_date' => ['required'],
            'retention' => ['required', 'in:yes,no'],
            'entry_to_payroll' => ['required', 'in:yes,no'],
            'role' => ['required', 'in:root,gerencia,administracion_general,administracion_zonal,cajero,asistente,cliente'],
            'state' => ['required', 'in:active,inactive'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        // Non-global users can only create users for their branch
        $branchId = $this->isGlobalUser()
            ? ($validate['branch_id'] ?? null)
            : $this->currentBranchId();

        User::create([
            'branch_id' => $branchId,
            'name' => $validate['name'],
            'lastname' => $validate['lastname'],
            'dni' => $validate['dni'],
            'phone' => $validate['phone'],
            'address' => $validate['address'],
            'children' => $validate['children'],
            'email' => $validate['email'],
            'password' => $validate['password'],
            'affiliate' => $validate['affiliate'],
            'insured' => $validate['insured'],
            'work_modality' => $validate['work_modality'],
            'entry_date' => $validate['entry_date'],
            'retention' => $validate['retention'],
            'entry_to_payroll' => $validate['entry_to_payroll'],
            'role' => $validate['role'],
            'state' => $validate['state'],
        ]);

        return to_route('users.index');
    }

    public function update(Request $request, string $userId)
    {
        Log::info($request->all());
        $user = User::query()->findOrFail($userId);

        // Non-global can only edit their own branch's users
        if (! $this->isGlobalUser()) {
            abort_if(
                $user->branch_id !== $this->currentBranchId(),
                403,
                'No tienes acceso a este usuario.'
            );
        }

        $validate = $request->validate([
            'name' => ['required', 'string', 'max:250'],
            'lastname' => ['required', 'string', 'max:250'],
            'dni' => ['required', 'integer'],
            'phone' => ['required', 'integer'],
            'address' => ['required', 'string'],
            'email' => ['required', 'string', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'children' => ['required', 'integer'],
            'affiliate' => ['required', 'in:ONP,AFP'],
            'insured' => ['required', 'in:EsSalud,SIS'],
            'work_modality' => ['required', 'in:fullTime,partTime'],
            'entry_date' => ['required'],
            'retention' => ['required', 'in:yes,no'],
            'entry_to_payroll' => ['required', 'in:yes,no'],
            'role' => ['required', 'in:root,gerencia,administracion_general,administracion_zonal,cajero,asistente,cliente'],
            'state' => ['required', 'in:active,inactive'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        $payload = [
            'name' => $validate['name'],
            'lastname' => $validate['lastname'],
            'dni' => $validate['dni'],
            'phone' => $validate['phone'],
            'address' => $validate['address'],
            'email' => $validate['email'],
            'children' => $validate['children'],
            'affiliate' => $validate['affiliate'],
            'insured' => $validate['insured'],
            'work_modality' => $validate['work_modality'],
            'entry_date' => $validate['entry_date'],
            'retention' => $validate['retention'],
            'entry_to_payroll' => $validate['entry_to_payroll'],
            'role' => $validate['role'],
            'state' => $validate['state'],
        ];

        // Handle branch assignment
        if ($this->isGlobalUser()) {
            $payload['branch_id'] = $validate['branch_id'] ?? null;
        }

        if (! empty($validate['password'])) {
            $payload['password'] = $validate['password'];
        }

        $user->update($payload);

        return to_route('users.index');
    }

    public function destroy(Request $request, string $userId)
    {
        $user = User::query()->findOrFail($userId);

        if ($request->user()?->id === $user->id) {
            return back()->withErrors([
                'delete' => 'No puedes eliminar tu propio usuario.',
            ]);
        }

        // Non-global can only delete their own branch's users
        if (! $this->isGlobalUser()) {
            abort_if(
                $user->branch_id !== $this->currentBranchId(),
                403,
                'No tienes acceso a este usuario.'
            );
        }

        $user->delete();

        return to_route('users.index');
    }
}
