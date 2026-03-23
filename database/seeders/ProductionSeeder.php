<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ProductionSeeder extends Seeder
{
    /**
     * Seed pour la production uniquement :
     * - Rôles & Permissions
     * - Compte Super Admin (unique)
     */
    public function run(): void
    {
        // 1. Créer les rôles et permissions
        $this->call(RoleAndPermissionSeeder::class);

        // 2. Créer le rôle super-admin s'il n'existe pas
        if (!Role::where('name', 'super-admin')->exists()) {
            $superAdminRole = Role::create(['name' => 'super-admin']);
            $superAdminRole->givePermissionTo(\Spatie\Permission\Models\Permission::all());
        }

        // 3. Créer le compte Super Admin s'il n'existe pas
        if (!User::where('email', 'superadmin@okamisarl.org')->exists()) {
            $superAdmin = User::create([
                'name' => 'Super Admin',
                'email' => 'superadmin@okamisarl.org',
                'password' => Hash::make('OkamiAdmin@2026!'),
                'email_verified_at' => now(),
            ]);
            $superAdmin->assignRole('super-admin');

            $this->command->info('');
            $this->command->info('✅ Compte Super Admin créé :');
            $this->command->table(
                ['Email', 'Mot de passe'],
                [['superadmin@okamisarl.org', 'OkamiAdmin@2026!']]
            );
            $this->command->warn('⚠️  CHANGEZ CE MOT DE PASSE IMMÉDIATEMENT APRÈS LA PREMIÈRE CONNEXION !');
        } else {
            $this->command->info('ℹ️  Compte Super Admin existe déjà, création ignorée.');
        }
    }
}

