<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    //
    public function index(): Response
    {
        $users = User::all();

        return Inertia::render('users/index', [
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        // Log:info($request);
        $validate = $request->validate([
            'name' => ['required', 'string', 'max:250'],
            'lastname' => ['required', 'string', 'max:250'],

            'dni' => ['required', 'integer'],
            'phone' => ['required', 'integer'],

            'dni' => ['required', 'integer', 'digits:8'],
            'phone' => ['required', 'integer', 'digits:9'],

            'address' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min: 8', 'confirmed'],
            'children' => ['required', 'integer'],
            'affiliate' => ['required', 'in:ONP,AFP'],
            'insured' => ['required', 'in:EsSalud,SIS'],
            'work_modality' => ['required', 'in:fullTime,partTime'],
            'entry_date' => ['required'],
            'retention' => ['required', 'in:yes,no'],
            'entry_to_payroll' => ['required', 'in:yes,no'],
            'role' => ['required', 'in:root,managment,administrator_general,logistic_general,administrator,logistic,cashier,asistente'],
            'state' => ['required', 'in:active,inactive']
        ]);

        // Log:info($validate);

        User::create([
            'name' => $validate['name'],
            'lastname' => $validate['lastname'],
            'dni' => $validate['dni'],
            'phone' => $validate['phone'],
            'address' => $validate['address'],
            'children' => $validate['children'],
            'email' => $validate['email'],
            'password' => $validate['password'],
            'children' => $validate['children'],
            'affiliate' => $validate['affiliate'],
            'insured' => $validate['insured'],
            'work_modality' => $validate['work_modality'],
            'entry_date' => $validate['entry_date'],
            'entry_to_payroll' => $validate['entry_to_payroll'],
            'role' => $validate['role'],
            'state' => $validate['state'],
        ]);

        return to_route('users.index');
    }

    public function update(Request $request, string $userId)
    {

        Log:
        info($request);
        $user = User::query()->findOrFail($userId);

        $validate = $request->validate([
            'name' => ['required', 'string', 'max:250'],
            'lastname' => ['required', 'string', 'max:250'],
            'dni' => ['required', 'integer'],
            'phone' => ['required', 'integer'],
            'address' => ['required', 'string'],
            'email' => ['required', 'string', 'email',  Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min: 8', 'confirmed'],
            'children' => ['required', 'integer'],
            'affiliate' => ['required', 'in:ONP,AFP'],
            'insured' => ['required', 'in:EsSalud,SIS'],
            'work_modality' => ['required', 'in:fullTime,partTime'],
            'entry_date' => ['required'],
            'retention' => ['required', 'in:yes,no'],
            'entry_to_payroll' => ['required', 'in:yes,no'],
            'role' => ['required', 'in:root,managment,administrator_general,logistic_general,administrator,logistic,cashier,asistente'],
            'state' => ['required', 'in:active,inactive']
        ]);

        $payload = [
            'name' => $validate['name'],
            'lastname' => $validate['lastname'],
            'dni' => $validate['dni'],
            'phone' => $validate['phone'],
            'address' => $validate['address'],
            'email' => $validate['email'],
            'children' => $validate['children'],
            // 'password' => $validate['password'],
            'affiliate' => $validate['affiliate'],
            'insured' => $validate['insured'],
            'work_modality' => $validate['work_modality'],
            'entry_date' => $validate['entry_date'],
            'entry_to_payroll' => $validate['entry_to_payroll'],
            'role' => $validate['role'],
            'state' => $validate['state'],
        ];


        //Si no esta vacio el password entra en la condicion
        if (! empty($validate['password'])) {
            $payload['password'] = $validate['password'];
        }

        //Actualiza los nuevos datos de payload en la variable user
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

        $user->delete();

        return to_route('users.index');
    }
}