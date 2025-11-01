<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        // Carga roles y construye un campo 'role' a partir de la relación pivot
        $users = User::with('roles:id,name')
            ->select('id','name','email','created_at')
            ->orderBy('id','desc')
            ->get()
            ->map(function ($u) {
                $u->role = optional($u->roles->first())->name; // 'admin' | 'consultor' | null
                unset($u->roles); // opcional, para simplificar la respuesta
                return $u;
            });

        return $users;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:100'],
            'email'    => ['required','email','max:150','unique:users,email'],
            'password' => ['required','string','min:6'],
            'role'     => ['required', Rule::in(['admin','consultor'])],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Asignar rol por nombre usando la pivot
        $roleId = Role::where('name', $data['role'])->value('id');
        if ($roleId) {
            $user->roles()->sync([$roleId]); // deja un solo rol
        }

        // Respuesta consistente con index()
        $user->load('roles:id,name');
        $user->role = optional($user->roles->first())->name;
        unset($user->roles);

        return response()->json($user, 201);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:100'],
            'email'    => ['required','email','max:150', Rule::unique('users','email')->ignore($user->id)],
            'password' => ['nullable','string','min:6'],
            'role'     => ['required', Rule::in(['admin','consultor'])],
        ]);

        $user->name  = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        // Actualiza rol en pivot
        $roleId = Role::where('name', $data['role'])->value('id');
        if ($roleId) {
            $user->roles()->sync([$roleId]);
        } else {
            $user->roles()->detach(); // si no hay coincidencia (caso raro)
        }

        // Respuesta consistente
        $user->load('roles:id,name');
        $user->role = optional($user->roles->first())->name;
        unset($user->roles);

        return $user;
    }

    public function destroy(User $user)
    {
        // Limpia pivot para mantener integridad (opcional si FK cascade)
        $user->roles()->detach();
        $user->delete();

        return response()->noContent();
    }
}
