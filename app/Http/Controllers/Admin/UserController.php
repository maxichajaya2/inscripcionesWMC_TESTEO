<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        // Eloquent ya sabe que debe buscar en pgsql_second gracias al modelo
        $usuarios = User::with('roles')
            ->where('id', '!=', auth()->id())
            ->latest()
            ->get();

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
            // FIX: Forzamos la validación unique hacia la conexión pgsql_second
            'email'    => 'required|string|email|max:255|unique:pgsql_second.users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role'     => 'required|string|exists:pgsql_second.roles,name',
        ]);

        try {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $role = Role::where('name', $request->role)->first();

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
            // FIX: Forzamos pgsql_second y además ignoramos el ID del usuario actual para que pueda guardar sin cambiar el correo
            'email'    => 'required|string|email|max:255|unique:pgsql_second.users,email,' . $usuario->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role'     => 'required|string|exists:pgsql_second.roles,name',
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

        $role = Role::where('name', $request->role)->first();

        if ($role) {
            $usuario->syncRoles([$role]);
        }

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
