<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Autor extends Model
{
    use HasFactory;

    protected $connection = "pgsql_second";
    protected $table = "autor"; // Asegúrate de que la tabla en la BD se llame así

    protected $fillable = [
        // Campos del Trabajo Técnico
        'correlativo',
        'categoria',

        // Datos del Autor (Mapeados desde el Excel)
        'autor_nombre',
        'autor_cargo',
        'autor_empresa',
        'autor_celular',
        'autor_correo',
        'autor_pais',
        'autor_rol',
    ];

}
