<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cupon;
// use Spatie\Permission\Models\Role;
use App\Models\Role;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        // 1. Estadísticas Generales (Tarjetas)
        $stats = [
            'total_usuarios' => User::count(),
            'total_roles' => Role::count(),
            'cupones_activos' => Cupon::where('is_active', true)->count(),
            'usos_totales_cupones' => Cupon::sum('usos_actuales'),
        ];

        // 2. Actividad Reciente: Últimos 5 usuarios registrados
        $ultimosUsuarios = User::with('roles')
            ->latest()
            ->take(5)
            ->get();

        // 3. Actividad Reciente: Últimos 5 cupones creados
        $ultimosCupones = Cupon::latest()
            ->take(5)
            ->get();

        return inertia('Admin/Index', [
            'stats' => $stats,
            'ultimosUsuarios' => $ultimosUsuarios,
            'ultimosCupones' => $ultimosCupones,
        ]);
    }
}
