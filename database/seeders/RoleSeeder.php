<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// IMPORTANTE: Asegúrate de usar los modelos de Spatie si no creaste el personalizado
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. LIMPIEZA INICIAL en pgsql_second
        // Usamos la conexión explícita para los truncates y deletes
        DB::connection('pgsql_second')->statement('SET CONSTRAINTS ALL DEFERRED');

        DB::connection('pgsql_second')->table('model_has_permissions')->delete();
        DB::connection('pgsql_second')->table('model_has_roles')->delete();
        DB::connection('pgsql_second')->table('role_has_permissions')->delete();

        // Forzamos a los modelos de Spatie a usar la segunda conexión para borrar
        Permission::on('pgsql_second')->query()->delete();
        Role::on('pgsql_second')->query()->delete();

        // Usuarios (Asumimos que están en la DB principal o donde indique el modelo User)
        User::whereIn('email', ['admin@wmc.com', 'asociado@wmc.com', 'user@wmc.com'])->delete();

        // 2. Limpiar caché de Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 3. Definir Permisos (Usando la conexión secundaria)
        $permisos = [
            'ver dashboard admin',
            'gestionar usuarios',
            'gestionar cupones',
            'ver reportes',
            'acceso participante'
        ];

        foreach ($permisos as $permiso) {
            // Usamos el método on() para crear en la DB secundaria
            Permission::on('pgsql_second')->create(['name' => $permiso, 'guard_name' => 'web']);
        }

        // 4. Crear Roles y Asignar Permisos (Usando la conexión secundaria)
        $roleAdmin = Role::on('pgsql_second')->create(['name' => 'admin', 'guard_name' => 'web']);
        // Traemos todos los permisos de la segunda DB para asignarlos
        $roleAdmin->givePermissionTo(Permission::on('pgsql_second')->all());

        $roleAsociado = Role::on('pgsql_second')->create(['name' => 'asociado', 'guard_name' => 'web']);
        $roleAsociado->givePermissionTo(Permission::on('pgsql_second')->whereIn('name', ['gestionar cupones', 'ver dashboard admin'])->get());

        $roleParticipante = Role::on('pgsql_second')->create(['name' => 'participante', 'guard_name' => 'web']);
        $roleParticipante->givePermissionTo(Permission::on('pgsql_second')->where('name', 'acceso participante')->first());

        // 5. CREAR USUARIOS DE PRUEBA
        // Nota: El método assignRole funcionará siempre y cuando el modelo Role
        // esté configurado para apuntar a la DB correcta o estemos pasando el objeto correcto.

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

        $this->command->info('Tablas de pgsql_second limpiadas y cuentas creadas correctamente.');
    }
}
