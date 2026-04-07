<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. LIMPIEZA INICIAL (Opcional pero recomendado para pruebas)
        // Desactivamos revisión de llaves foráneas para poder vaciar tablas con relaciones
        DB::connection('pgsql_second')->statement('SET CONSTRAINTS ALL DEFERRED');

        // Borramos registros de las tablas de Spatie para empezar de cero
        DB::connection('pgsql_second')->table('model_has_permissions')->delete();
        DB::connection('pgsql_second')->table('model_has_roles')->delete();
        DB::connection('pgsql_second')->table('role_has_permissions')->delete();
        Permission::query()->delete();
        Role::query()->delete();

        // Opcional: Borrar los usuarios de prueba anteriores para no tener errores de "Email duplicado"
        User::whereIn('email', ['admin@wmc.com', 'asociado@wmc.com', 'user@wmc.com'])->delete();

        // 2. Limpiar caché de Spatie (Vital)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 3. Definir Permisos
        $permisos = [
            'ver dashboard admin',
            'gestionar usuarios',
            'gestionar cupones',
            'ver reportes',
            'acceso participante'
        ];

        foreach ($permisos as $permiso) {
            Permission::create(['name' => $permiso, 'guard_name' => 'web']);
        }

        // 4. Crear Roles y Asignar Permisos
        $roleAdmin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $roleAdmin->givePermissionTo(Permission::all());

        $roleAsociado = Role::create(['name' => 'asociado', 'guard_name' => 'web']);
        $roleAsociado->givePermissionTo(['gestionar cupones', 'ver dashboard admin']);

        $roleParticipante = Role::create(['name' => 'participante', 'guard_name' => 'web']);
        $roleParticipante->givePermissionTo('acceso participante');

        // 5. CREAR USUARIOS DE PRUEBA

        User::create([
            'name' => 'Administrador WMC',
            'email' => 'admin@wmc.com',
            'password' => Hash::make('admin123'),
        ])->assignRole($roleAdmin);

        User::create([
            'name' => 'Personal Asociado',
            'email' => 'asociado@wmc.com',
            'password' => Hash::make('asociado123'),
        ])->assignRole($roleAsociado);

        User::create([
            'name' => 'Participante WMC',
            'email' => 'user@wmc.com',
            'password' => Hash::make('user123'),
        ])->assignRole($roleParticipante);

        $this->command->info('Tablas limpiadas y cuentas de prueba creadas correctamente.');
    }
}
