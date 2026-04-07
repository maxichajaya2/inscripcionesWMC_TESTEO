<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role; // Importamos el de Spatie
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // ... (el código anterior de permisos)

        // 3. CREAR ROLES USANDO LA CONEXIÓN MANUAL
        // Usamos el método on('pgsql_second') para que sepa a dónde ir
        $roleAdmin = Role::on('pgsql_second')->updateOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $roleAsociado = Role::on('pgsql_second')->updateOrCreate(['name' => 'asociado', 'guard_name' => 'web']);
        $roleParticipante = Role::on('pgsql_second')->updateOrCreate(['name' => 'participante', 'guard_name' => 'web']);

        // 4. ASIGNAR PERMISOS
        // Spatie necesita saber que trabajamos en esa conexión
        $roleAdmin->syncPermissions(Permission::on('pgsql_second')->all());

        $this->command->info('¡Roles creados con éxito!');
    }
}
