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
        'codigo_cupon',
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
    ];
}
