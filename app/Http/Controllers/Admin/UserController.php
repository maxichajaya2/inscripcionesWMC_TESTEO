<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role; // Tu modelo personalizado con $connection = 'pgsql_second'
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        // Cargamos usuarios de DB1 y Roles de DB2 (via modelo Role)
        $usuarios = User::with('roles')->latest()->get();
        $roles = Role::all();

        return inertia('Admin/Usuarios/Index', [
            'usuarios' => $usuarios,
            'roles' => $roles
        ]);
    }

    public function store(Request $request)
{
    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|string|email|max:255|unique:users,email',
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
        'role'     => 'required|string|exists:pgsql_second.roles,name', // Validamos en la DB correcta
    ]);

    try {
        // 1. Creamos el usuario en DB principal
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 2. IMPORTANTE: Buscamos el rol explícitamente en la DB secundaria
        // Usamos tu modelo personalizado Role que ya tiene la conexión pgsql_second
        $role = Role::where('name', $request->role)->first();

        // 3. Asignamos el objeto Rol (no solo el nombre)
        if ($role) {
            $user->assignRole($role);
        }

        return redirect()->back();

    } catch (\Exception $e) {
        return redirect()->back()->withErrors(['email' => 'Error al crear: ' . $e->getMessage()]);
    }
}

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email,' . $usuario->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role'     => 'required|string|exists:pgsql_second.roles,name', // <--- FIX
        ]);

        $usuario->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $usuario->update([
                'password' => Hash::make($request->password),
            ]);
        }

        // Sincroniza en la conexión secundaria
        $usuario->syncRoles([$request->role]);

        return redirect()->back();
    }

    public function destroy(User $usuario)
    {
        if (auth()->id() === $usuario->id) {
            return redirect()->back()->withErrors(['error' => 'No puedes eliminarte a ti mismo.']);
        }

        $usuario->delete();
        return redirect()->back();
    }
}
