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
        $usuarios = User::with('roles')->latest()->get();
        $roles = Role::all();

        // Verifica que los usuarios se estén cargando correctamente con sus roles
        return inertia('Admin/Usuarios/Index', [
            'usuarios' => $usuarios,
            'roles' => $roles
        ]);
    }

    public function store(Request $request)
{
    // 1. La validación DEBE ser lo primero.
    // Laravel automáticamente enviará un error 422 si el email ya existe.
    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|string|email|max:255|unique:users,email',
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
        'role'     => 'required|string|exists:roles,name',
    ], [
        'email.unique' => 'Este correo electrónico ya está registrado en el sistema.',
    ]);

    try {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);
        return redirect()->back();

    } catch (\Illuminate\Database\QueryException $e) {
        // Si falló la unicidad a nivel de SQL (segunda capa de seguridad)
        return redirect()->back()->withErrors([
            'email' => 'El correo ya existe en nuestra base de datos.'
        ]);
    }
}

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email,' . $usuario->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role'     => 'required|string|exists:roles,name', // Validamos 'role'
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

        // syncRoles elimina los anteriores y deja solo el nuevo enviado en el string/array
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
