<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Cleaner;
use Illuminate\Support\Facades\Hash;

class CleanerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un utilisateur laveur de test
        $user = User::create([
            'name' => 'Jean Laveur',
            'email' => 'laveur@tricycle.com',
            'password' => Hash::make('password'),
        ]);

        $user->assignRole('cleaner');

        Cleaner::create([
            'user_id' => $user->id,
            'zone' => 'Gombe',
            'telephone' => '+243 999 888 777',
            'adresse' => 'Av. du Commerce, Gombe',
            'is_active' => true,
        ]);

        $this->command->info('Laveur de test créé: laveur@tricycle.com / password');
    }
}

