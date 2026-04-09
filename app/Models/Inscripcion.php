<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inscripcion extends Model
{
    use HasFactory;

    protected $connection = 'pgsql_second';
    protected $table = "inscripcion";

    protected $casts = [
        'id_categoria_cursos_viajes' => 'array',
    ];

    public function categoria_inscripcion(): BelongsTo
    {
        return $this->belongsTo(CategoriaInscripcion::class, 'id_categoria_inscripcion');
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    public function facturacion(): BelongsTo
    {
        return $this->belongsTo(Facturacion::class, 'id_facturacion');
    }

    public function cupon(): BelongsTo
    {
        return $this->belongsTo(Cupon::class, 'id_cupon');
    }

    public function categoria_cursos_viajes(): BelongsTo
    {
        return $this->belongsTo(CategoriaCursoViaje::class, 'id_categoria_cursos_viajes');
    }


}
