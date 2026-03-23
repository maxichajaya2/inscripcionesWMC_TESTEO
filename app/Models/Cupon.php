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
        'id_empresa',     // integer
        'codigo',         // character varying(100)
        'tipo_descuento', // character varying(50)
        'valor',          // integer
        'limite_usos',    // integer
        'usos_actuales',  // integer
        'fecha_inicio',   // character varying (Considera cambiar a date/timestamp en el futuro)
        'fecha_fin',      // character varying
        'is_active',      // boolean (Corregido de isactive a is_active según imagen)
    ];
}
