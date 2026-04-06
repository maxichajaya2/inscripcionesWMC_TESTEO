<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cupon; // Asegúrate de tener tu modelo Cupon creado
use Illuminate\Http\Request;

class CuponController extends Controller
{
    public function index()
    {
        // Traemos los cupones ordenados por el más reciente
        $cupones = Cupon::latest()->get();

        return inertia('Admin/Cupones/Index', [
            'cupones' => $cupones
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo_cupon' => 'required|string|max:100|unique:pgsql_second.cupones,codigo_cupon',
            'tipo_descuento' => 'required|string|max:50',
            'valor' => 'required|integer',
            'razon_social' => 'nullable|string|max:255',
            'tipo_documento' => 'nullable|string|max:10',
            'num_documento' => 'nullable|string|max:100',
            'eci_cod' => 'nullable|string|max:100',
            'limite_usos' => 'required|integer|min:1',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'is_active' => 'boolean',
        ]);

        // Al crear, forzamos usos_actuales a 0
        $validated['usos_actuales'] = 0;

        // Si no se marca el checkbox, en Laravel puede llegar null, lo forzamos a false
        $validated['is_active'] = $request->is_active ?? false;

        Cupon::create($validated);

        return redirect()->back();
    }

    public function update(Request $request, Cupon $cupone) // Ojo: Laravel a veces pluraliza "cupones" a "cupone" en el binding. Ajusta el nombre de la variable según tu modelo.
    {
        $validated = $request->validate([
          'codigo_cupon' => 'required|string|max:100|unique:pgsql_second.cupones,codigo_cupon,' . $cupone->id,
            'tipo_descuento' => 'required|string|max:50',
            'valor' => 'required|integer',
            'razon_social' => 'nullable|string|max:255',
            'tipo_documento' => 'nullable|string|max:10',
            'num_documento' => 'nullable|string|max:100',
            'eci_cod' => 'nullable|string|max:100',
            'limite_usos' => 'required|integer|min:1',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->is_active ?? false;

        $cupone->update($validated);

        return redirect()->back();
    }

    public function destroy(Cupon $cupone)
    {
        $cupone->delete();
        return redirect()->back();
    }
}
