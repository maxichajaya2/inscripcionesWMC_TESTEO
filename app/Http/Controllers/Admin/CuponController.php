<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cupon;
use Illuminate\Http\Request;

class CuponController extends Controller
{
    public function index()
    {
        // Traemos SOLO los cupones que no han sido "eliminados" (is_delete = false)
        // y los ordenamos por el más reciente
        $cupones = Cupon::where('is_delete', false)->latest()->get();

        return inertia('Admin/Cupones/Index', [
            'cupones' => $cupones
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo_cupon' => 'required|string|max:100|unique:pgsql_second.cupones,codigo_cupon',
            'tipo_descuento' => 'required|string|max:50',
            'valor' => 'required|numeric', // Cambié integer por numeric por si usas decimales (ej. porcentajes)
            'razon_social' => 'required|string|max:255', // Ajustado a required como pediste antes
            'tipo_documento' => 'required|string|max:10',
            'num_documento' => 'required|string|max:100',
            'eci_cod' => 'required|string|max:100',
            'limite_usos' => 'required|integer|min:1',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'is_active' => 'boolean',
        ]);

        $validated['usos_actuales'] = 0;
        $validated['is_active'] = $request->is_active ?? false;
        // Por defecto al crear, nos aseguramos de que no esté eliminado
        $validated['is_delete'] = false;

        Cupon::create($validated);

        return redirect()->back();
    }

    public function update(Request $request, Cupon $cupone)
    {
        $validated = $request->validate([
            'codigo_cupon' => 'required|string|max:100|unique:pgsql_second.cupones,codigo_cupon,' . $cupone->id,
            'tipo_descuento' => 'required|string|max:50',
            'valor' => 'required|numeric',
            'razon_social' => 'required|string|max:255',
            'tipo_documento' => 'required|string|max:10',
            'num_documento' => 'required|string|max:100',
            'eci_cod' => 'required|string|max:100',
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
        // Eliminación lógica: Cambiamos el estado de is_delete a true
        // Usamos save() directamente en lugar de update() para no lidiar con el $fillable aquí
        $cupone->is_delete = true;
        $cupone->save();

        return redirect()->back();
    }
}
