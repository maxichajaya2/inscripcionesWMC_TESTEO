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
        // 1. LIMPIEZA INICIAL
        DB::connection('pgsql_second')->statement('SET CONSTRAINTS ALL DEFERRED');

        // Limpieza de tablas pivote
        DB::connection('pgsql_second')->table('model_has_permissions')->delete();
        DB::connection('pgsql_second')->table('model_has_roles')->delete();
        DB::connection('pgsql_second')->table('role_has_permissions')->delete();

        // Limpieza de tablas principales (Sin el ->query())
        Permission::on('pgsql_second')->delete();
        Role::on('pgsql_second')->delete();

        // Limpiar caché de Spatie
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
            Permission::on('pgsql_second')->create(['name' => $permiso, 'guard_name' => 'web']);
        }

        // 3. CREAR ROLES Y ASIGNAR PERMISOS
        $roleAdmin = Role::on('pgsql_second')->create(['name' => 'admin', 'guard_name' => 'web']);
        $roleAdmin->givePermissionTo(Permission::on('pgsql_second')->all());

        $roleAsociado = Role::on('pgsql_second')->create(['name' => 'asociado', 'guard_name' => 'web']);
        $roleAsociado->givePermissionTo(Permission::on('pgsql_second')->whereIn('name', ['gestionar cupones', 'ver dashboard admin'])->get());

        $roleParticipante = Role::on('pgsql_second')->create(['name' => 'participante', 'guard_name' => 'web']);
        $roleParticipante->givePermissionTo(Permission::on('pgsql_second')->where('name', 'acceso participante')->first());

        // 4. CREAR USUARIOS (Opcional - Revisa si los quieres borrar antes)
        User::whereIn('email', ['admin@wmc.com', 'asociado@wmc.com', 'user@wmc.com'])->delete();

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

        $this->command->info('Seed completado con éxito en pgsql_second.');
    }
}
