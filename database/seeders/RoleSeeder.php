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
        // 1. LIMPIEZA INICIAL (Uso de DB directo para evitar fallos de modelo)
        DB::connection('pgsql_second')->statement('SET CONSTRAINTS ALL DEFERRED');

        DB::connection('pgsql_second')->table('model_has_permissions')->delete();
        DB::connection('pgsql_second')->table('model_has_roles')->delete();
        DB::connection('pgsql_second')->table('role_has_permissions')->delete();

        // Usamos Query Builder para borrar, que es lo más seguro en conexiones cruzadas
        DB::connection('pgsql_second')->table('permissions')->delete();
        DB::connection('pgsql_second')->table('roles')->delete();

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
            // Usamos on() para crear
            Permission::on('pgsql_second')->create(['name' => $permiso, 'guard_name' => 'web']);
        }

        // 3. CREAR ROLES
        $roleAdmin = Role::on('pgsql_second')->create(['name' => 'admin', 'guard_name' => 'web']);
        $roleAsociado = Role::on('pgsql_second')->create(['name' => 'asociado', 'guard_name' => 'web']);
        $roleParticipante = Role::on('pgsql_second')->create(['name' => 'participante', 'guard_name' => 'web']);

        // 4. ASIGNAR PERMISOS (Aquí estaba el error, usamos get() en lugar de all())
        $todosLosPermisos = Permission::on('pgsql_second')->get();
        $roleAdmin->syncPermissions($todosLosPermisos);

        $permisosAsociado = Permission::on('pgsql_second')->whereIn('name', ['gestionar cupones', 'ver dashboard admin'])->get();
        $roleAsociado->syncPermissions($permisosAsociado);

        $permisoParticipante = Permission::on('pgsql_second')->where('name', 'acceso participante')->get();
        $roleParticipante->syncPermissions($permisoParticipante);

        // 5. USUARIOS (Asegúrate de que User esté en la DB correcta)
        User::whereIn('email', ['admin@wmc.com', 'asociado@wmc.com', 'user@wmc.com'])->delete();

        $user1 = User::create([
            'name' => 'Administrador WMC',
            'email' => 'admin@wmc.com',
            'password' => Hash::make('admin123'),
        ]);
        $user1->assignRole($roleAdmin);

        $user2 = User::create([
            'name' => 'Personal Asociado',
            'email' => 'asociado@wmc.com',
            'password' => Hash::make('asociado123'),
        ]);
        $user2->assignRole($roleAsociado);

        $user3 = User::create([
            'name' => 'Participante WMC',
            'email' => 'user@wmc.com',
            'password' => Hash::make('user123'),
        ]);
        $user3->assignRole($roleParticipante);

        $this->command->info('Seed completado exitosamente.');
    }
}
