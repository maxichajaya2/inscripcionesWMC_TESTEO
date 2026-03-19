<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ocupacion extends Model
{
    use HasFactory;

    protected $connection = "pgsql";
    protected $table = 'ocupacion';

    protected $fillable = [
        'id',           // Necesario porque lo estás forzando desde la API
        'name',         // Corresponde a character varying(100)
        'descripcion',  // Corresponde a text
        'isactive',     // Corresponde a boolean
    ];
}
