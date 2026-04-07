<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    // Forzamos la conexión a la base de datos secundaria
    protected $connection = 'pgsql_second';
}
