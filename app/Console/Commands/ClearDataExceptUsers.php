<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearDataExceptUsers extends Command
{
    protected $signature = 'data:clear {--force : Force sans confirmation}';
    protected $description = 'Supprime toutes les données sauf les utilisateurs et les tables système';

    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('⚠️  Êtes-vous sûr de vouloir supprimer TOUTES les données sauf les utilisateurs?')) {
            $this->info('Opération annulée.');
            return;
        }

        $this->info('🗑️  Suppression des données en cours...');

        // Désactiver les contraintes de clés étrangères
        Schema::disableForeignKeyConstraints();

        // Tables à conserver (ne pas vider)
        $tablesToKeep = [
            'users',
            'password_reset_tokens',
            'sessions',
            'cache',
            'cache_locks',
            'jobs',
            'job_batches',
            'failed_jobs',
            'migrations',
            'personal_access_tokens',
            'roles',
            'permissions',
            'role_has_permissions',
            'model_has_roles',
            'model_has_permissions',
        ];

        // Tables à vider (dans l'ordre pour respecter les dépendances)
        $tablesToClear = [
            'versements',
            'collectes',
            'payments',
            'lavages',
            'depense_lavages',
            'maintenances',
            'accidents',
            'tournees',
            'system_notifications',
            'motards',
            'motos',
            'caissiers',
            'collecteurs',
            'cleaners',
            'proprietaires',
            'zones',
            'system_settings',
        ];

        $cleared = 0;

        foreach ($tablesToClear as $table) {
            if (Schema::hasTable($table)) {
                try {
                    DB::table($table)->truncate();
                    $this->line("  ✓ Table '{$table}' vidée");
                    $cleared++;
                } catch (\Exception $e) {
                    $this->warn("  ⚠ Table '{$table}': " . $e->getMessage());
                }
            }
        }

        // Réactiver les contraintes de clés étrangères
        Schema::enableForeignKeyConstraints();

        $this->newLine();
        $this->info("✅ {$cleared} tables vidées avec succès!");
        $this->info("👤 Les utilisateurs et leurs rôles ont été conservés.");

        return 0;
    }
}

