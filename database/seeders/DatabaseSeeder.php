<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Motard;
use App\Models\Moto;
use App\Models\Proprietaire;
use App\Models\Caissier;
use App\Models\Collecteur;
use App\Models\Zone;
use App\Models\Versement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. D'abord créer les rôles et permissions
        $this->call(RoleAndPermissionSeeder::class);

        // 2. Créer les zones
        $this->createZones();

        // 3. Créer les utilisateurs de test pour chaque rôle
        $this->createAdminUsers();
        $this->createSupervisorUsers();
        $this->createCashierUsers();
        $this->createCollectorUsers();
        $this->createOwnerUsers();
        $this->createDriverUsers();

        // 4. Créer quelques versements de test
        $this->createSampleVersements();

        $this->command->info('✅ Base de données initialisée avec succès !');
        $this->command->newLine();
        $this->command->info('📧 Comptes de test créés :');
        $this->command->table(
            ['Rôle', 'Email', 'Mot de passe'],
            [
                ['Admin', 'admin@tricycle.app', 'password'],
                ['Superviseur (OKAMI)', 'okami@tricycle.app', 'password'],
                ['Caissier', 'caissier@tricycle.app', 'password'],
                ['Collecteur', 'collecteur@tricycle.app', 'password'],
                ['Propriétaire', 'proprietaire@tricycle.app', 'password'],
                ['Motard', 'motard@tricycle.app', 'password'],
            ]
        );
    }

    /**
     * Créer les zones de collecte
     */
    private function createZones(): void
    {
        $zones = [
            ['nom' => 'Zone Centre', 'description' => 'Centre-ville et environs'],
            ['nom' => 'Zone Nord', 'description' => 'Quartiers Nord'],
            ['nom' => 'Zone Sud', 'description' => 'Quartiers Sud'],
            ['nom' => 'Zone Est', 'description' => 'Quartiers Est'],
            ['nom' => 'Zone Ouest', 'description' => 'Quartiers Ouest'],
        ];

        foreach ($zones as $zone) {
            Zone::create($zone);
        }
    }

    /**
     * Créer les utilisateurs Admin (NTH)
     */
    private function createAdminUsers(): void
    {
        $admin = User::create([
            'name' => 'Administrateur NTH',
            'email' => 'admin@tricycle.app',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        $admin2 = User::create([
            'name' => 'John Admin',
            'email' => 'john.admin@nth-sarl.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin2->assignRole('admin');
    }

    /**
     * Créer les utilisateurs Superviseur (OKAMI)
     */
    private function createSupervisorUsers(): void
    {
        $supervisor = User::create([
            'name' => 'Superviseur OKAMI',
            'email' => 'okami@tricycle.app',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $supervisor->assignRole('supervisor');

        $supervisor2 = User::create([
            'name' => 'Marie Supervision',
            'email' => 'marie.okami@tricycle.app',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $supervisor2->assignRole('supervisor');
    }

    /**
     * Créer les utilisateurs Caissier
     */
    private function createCashierUsers(): void
    {
        $caissiers = [
            ['name' => 'Caissier Principal', 'email' => 'caissier@tricycle.app', 'point' => 'Caisse Centre-Ville'],
            ['name' => 'Amadou Caisse', 'email' => 'amadou.caisse@tricycle.app', 'point' => 'Caisse Marché Nord'],
            ['name' => 'Fatou Collecte', 'email' => 'fatou.collecte@tricycle.app', 'point' => 'Caisse Quartier Sud'],
        ];

        foreach ($caissiers as $index => $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('cashier');

            Caissier::create([
                'user_id' => $user->id,
                'numero_identifiant' => 'CAI-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'nom_point_collecte' => $data['point'],
                'zone' => 'Kinshasa',
                'telephone' => '77' . rand(1000000, 9999999),
                'adresse' => 'Adresse point de collecte ' . ($index + 1),
                'is_active' => true,
            ]);
        }
    }

    /**
     * Créer les utilisateurs Collecteur
     */
    private function createCollectorUsers(): void
    {
        $collecteurs = [
            ['name' => 'Collecteur Principal', 'email' => 'collecteur@tricycle.app'],
            ['name' => 'Ibrahima Tournée', 'email' => 'ibrahima.tournee@tricycle.app'],
        ];

        foreach ($collecteurs as $index => $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('collector');

            Collecteur::create([
                'user_id' => $user->id,
                'numero_identifiant' => 'COL-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'zone_affectation' => 'Kinshasa',
                'telephone' => '78' . rand(1000000, 9999999),
                'is_active' => true,
            ]);
        }
    }

    /**
     * Créer les utilisateurs Propriétaire
     */
    private function createOwnerUsers(): void
    {
        $proprietaires = [
            ['name' => 'Propriétaire Test', 'email' => 'proprietaire@tricycle.app', 'nb_motos' => 5],
            ['name' => 'Moussa Bailleur', 'email' => 'moussa.bailleur@tricycle.app', 'nb_motos' => 3],
            ['name' => 'Awa Investissement', 'email' => 'awa.invest@tricycle.app', 'nb_motos' => 8],
        ];

        foreach ($proprietaires as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('owner');

            $proprietaire = Proprietaire::create([
                'user_id' => $user->id,
                'raison_sociale' => $data['name'],
                'telephone' => '76' . rand(1000000, 9999999),
                'adresse' => 'Adresse du propriétaire',
                'numero_compte_mpesa' => '24' . rand(10000000, 99999999),
            ]);

            // Créer des motos pour ce propriétaire
            for ($i = 1; $i <= $data['nb_motos']; $i++) {
                Moto::create([
                    'proprietaire_id' => $proprietaire->id,
                    'numero_matricule' => 'MAT-' . strtoupper(substr($user->name, 0, 2)) . '-' . rand(1000, 9999),
                    'plaque_immatriculation' => 'TC-' . strtoupper(substr($user->name, 0, 2)) . '-' . rand(1000, 9999),
                    'numero_chassis' => 'CHS' . rand(100000000, 999999999),
                    'statut' => 'actif',
                    'montant_journalier_attendu' => rand(2000, 3000),
                ]);
            }
        }
    }

    /**
     * Créer les utilisateurs Motard
     */
    private function createDriverUsers(): void
    {
        $motos = Moto::whereNull('motard_id')->get();

        $motards = [
            ['name' => 'Motard Test', 'email' => 'motard@tricycle.app'],
            ['name' => 'Ousmane Conducteur', 'email' => 'ousmane.driver@tricycle.app'],
            ['name' => 'Aliou Moto', 'email' => 'aliou.moto@tricycle.app'],
            ['name' => 'Cheikh Tricycle', 'email' => 'cheikh.tricycle@tricycle.app'],
            ['name' => 'Pape Rouleur', 'email' => 'pape.rouleur@tricycle.app'],
        ];

        foreach ($motards as $index => $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('driver');

            $motard = Motard::create([
                'user_id' => $user->id,
                'numero_identifiant' => 'MOT-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'licence_numero' => 'PERM-' . rand(10000, 99999),
                'zone_affectation' => 'Kinshasa',
                'is_active' => true,
            ]);

            // Assigner une moto disponible si possible
            if ($motos->count() > $index) {
                $moto = $motos[$index];
                $moto->update(['motard_id' => $motard->id]);
            }
        }
    }

    /**
     * Créer des versements de test
     */
    private function createSampleVersements(): void
    {
        $motards = Motard::with('motoActuelle')->get()->filter(fn($m) => $m->motoActuelle);
        $caissiers = Caissier::all();

        if ($motards->isEmpty() || $caissiers->isEmpty()) {
            return;
        }

        // Créer des versements pour les 7 derniers jours
        for ($day = 6; $day >= 0; $day--) {
            $date = now()->subDays($day);

            foreach ($motards as $motard) {
                $moto = $motard->motoActuelle;
                if (!$moto) continue;

                $montantAttendu = $moto->montant_journalier_attendu ?? 2500;
                $montantPaye = $this->getRandomMontant($montantAttendu);
                $statut = $this->getStatutFromMontant($montantPaye, $montantAttendu);

                Versement::create([
                    'motard_id' => $motard->id,
                    'moto_id' => $moto->id,
                    'montant' => $montantPaye,
                    'montant_attendu' => $montantAttendu,
                    'date_versement' => $date->toDateString(),
                    'heure_versement' => sprintf('%02d:%02d', rand(6, 20), rand(0, 59)),
                    'mode_paiement' => ['cash', 'mobile_money'][rand(0, 1)],
                    'statut' => $statut,
                    'caissier_id' => $caissiers->random()->id,
                    'validated_by_caissier_at' => $date,
                ]);
            }
        }
    }

    /**
     * Génère un montant aléatoire
     */
    private function getRandomMontant(float $montantAttendu): float
    {
        $rand = rand(1, 100);

        if ($rand <= 70) {
            return $montantAttendu;
        } elseif ($rand <= 90) {
            return round($montantAttendu * (rand(30, 90) / 100), 0);
        } else {
            return 0;
        }
    }

    /**
     * Détermine le statut
     */
    private function getStatutFromMontant(float $montantPaye, float $montantAttendu): string
    {
        if ($montantPaye >= $montantAttendu) {
            return 'payé';
        } elseif ($montantPaye > 0) {
            return 'partiellement_payé';
        } else {
            return 'non_effectué';
        }
    }
}
