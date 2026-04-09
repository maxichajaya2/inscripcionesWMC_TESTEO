<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cupon extends Model
{
    use HasFactory;

    protected $connection = "pgsql_second";
    protected $table = 'cupones';

    protected $fillable = [
        'codigo_cupon', // <-- ¡Este era el que faltaba!
        'tipo_descuento',
        'valor',
        'razon_social',
        'tipo_documento',
        'num_documento',
        'eci_cod',
        'limite_usos',
        'usos_actuales',
        'fecha_inicio',
        'fecha_fin',
        'is_active',
        'is_delete',
    ];

    public function inscritos()
    {
        // Un cupón tiene muchas inscripciones
        return $this->hasMany(Inscripcion::class, 'id_cupon');
    }
}
