<?php

namespace App\Http\Controllers\Asociados;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Cupon;
use App\Models\Role;

class AsociadosController extends Controller
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

        return inertia('Asociados/Index', [
            'stats' => $stats,
            'ultimosUsuarios' => $ultimosUsuarios,
            'ultimosCupones' => $ultimosCupones,
        ]);
    }
}
