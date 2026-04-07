<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role; // Asegúrate de usar TU modelo que tiene la conexión secundaria
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. LIMPIAR CACHÉ (Obligatorio)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. DEFINIR PERMISOS
        $permisos = [
            'ver dashboard admin',
            'gestionar usuarios',
            'gestionar cupones',
            'ver reportes',
            'acceso participante'
        ];

        foreach ($permisos as $permiso) {
            // Usamos DB para asegurar la conexión secundaria en permisos
            DB::connection('pgsql_second')->table('permissions')->updateOrInsert(
                ['name' => $permiso, 'guard_name' => 'web'],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // 3. CREAR ROLES Y ASIGNAR PERMISOS
        // Usamos updateOrCreate para que no explote si ya existen
        $roleAdmin = Role::updateOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $roleAsociado = Role::updateOrCreate(['name' => 'asociado', 'guard_name' => 'web']);
        $roleParticipante = Role::updateOrCreate(['name' => 'participante', 'guard_name' => 'web']);

        // Asignar todos los permisos al admin (Spatie se encarga de las tablas pivote)
        $roleAdmin->syncPermissions($permisos);
        $roleAsociado->syncPermissions(['gestionar cupones', 'ver dashboard admin']);
        $roleParticipante->syncPermissions(['acceso participante']);

        // 4. CREAR USUARIOS DE PRUEBA (Opcional)
        // OJO: Si tus usuarios están en la DB principal (local_cms), quita el comentario:
        /*
        User::updateOrCreate(
            ['email' => 'admin@wmc.com'],
            [
                'name' => 'Administrador WMC',
                'password' => Hash::make('admin123'),
            ]
        )->assignRole($roleAdmin);
        */

        $this->command->info('Roles y Permisos creados en pgsql_second correctamente.');
    }
}
