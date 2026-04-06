<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        // Traemos a los usuarios con sus roles asignados
        $usuarios = User::with('roles')->latest()->get();
        // Traemos los roles disponibles para mostrarlos en el formulario
        $roles = Role::all();

        return inertia('Admin/Usuarios/Index', [
            'usuarios' => $usuarios,
            'roles' => $roles
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => 'array',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($request->has('roles')) {
            $user->assignRole($request->roles);
        }

        return redirect()->back();
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $usuario->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'roles' => 'array',
        ]);

        $usuario->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Solo actualiza la contraseña si el admin escribió una nueva
        if ($request->filled('password')) {
            $usuario->update([
                'password' => Hash::make($request->password),
            ]);
        }

        // Sincroniza los roles (elimina los viejos y pone los nuevos)
        if ($request->has('roles')) {
            $usuario->syncRoles($request->roles);
        }

        return redirect()->back();
    }

    public function destroy(User $usuario)
    {
        // Medida de seguridad: evitar que un usuario se elimine a sí mismo
        if (auth()->id() === $usuario->id) {
            return redirect()->back()->withErrors(['error' => 'No puedes eliminarte a ti mismo.']);
        }

        $usuario->delete();
        return redirect()->back();
    }
}
