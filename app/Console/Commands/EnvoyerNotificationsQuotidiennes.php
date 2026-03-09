<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Versement;
use App\Models\Motard;
use App\Models\Moto;
use App\Models\Tournee;
use App\Models\Maintenance;
use App\Services\NotificationService;
use Carbon\Carbon;

class EnvoyerNotificationsQuotidiennes extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:quotidiennes';

    /**
     * The console command description.
     */
    protected $description = 'Envoie les notifications quotidiennes (arriérés, contrats, maintenances)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Envoi des notifications quotidiennes...');

        $this->verifierArrieresCritiques();
        $this->verifierContratsExpirants();
        $this->verifierMaintenancesProgrammees();
        $this->verifierTourneesJour();
        $this->nettoyerNotificationsExpirees();

        $this->info('Notifications quotidiennes envoyées avec succès.');

        return Command::SUCCESS;
    }

    /**
     * Vérifier les motards avec arriérés critiques
     */
    protected function verifierArrieresCritiques(): void
    {
        $this->info('Vérification des arriérés critiques...');

        $motards = Motard::where('is_active', true)->with('user')->get();
        $count = 0;

        foreach ($motards as $motard) {
            $totalArrieres = Versement::where('motard_id', $motard->id)
                ->where('arrieres', '>', 0)
                ->sum('arrieres');

            // Seuil critique: 30 000 FC
            if ($totalArrieres >= 30000) {
                NotificationService::notifierMotardArrieresCritiques($motard, $totalArrieres);
                $count++;

                // Si arriérés > 50 000 FC, notifier OKAMI
                if ($totalArrieres >= 50000) {
                    NotificationService::notifierOkamiArrieres($motard, $totalArrieres);
                }
            }
        }

        $this->info("  → {$count} notification(s) d'arriérés envoyée(s)");
    }

    /**
     * Vérifier les contrats de moto expirants
     */
    protected function verifierContratsExpirants(): void
    {
        $this->info('Vérification des contrats expirants...');

        $aujourdhui = Carbon::today();
        $dans7Jours = $aujourdhui->copy()->addDays(7);
        $count = 0;

        // Contrats qui expirent dans les 7 prochains jours
        $motosExpirantBientot = Moto::whereBetween('contrat_fin', [$aujourdhui, $dans7Jours])
            ->where('statut', 'actif')
            ->with('proprietaire.user')
            ->get();

        foreach ($motosExpirantBientot as $moto) {
            if (!$moto->proprietaire || !$moto->proprietaire->user) continue;

            $joursRestants = Carbon::parse($moto->contrat_fin)->diffInDays($aujourdhui);

            // Vérifier si notification déjà envoyée
            $existante = \App\Models\SystemNotification::where('type', 'contrat_expire_bientot')
                ->where('notifiable_type', Moto::class)
                ->where('notifiable_id', $moto->id)
                ->where('created_at', '>=', now()->subDays(3))
                ->exists();

            if (!$existante) {
                \App\Models\SystemNotification::create([
                    'user_id' => $moto->proprietaire->user->id,
                    'type' => 'contrat_expire_bientot',
                    'titre' => '⚠️ Contrat expire bientôt',
                    'message' => "Le contrat de votre moto {$moto->plaque_immatriculation} expire dans {$joursRestants} jour(s).",
                    'icon' => 'calendar-event',
                    'couleur' => 'warning',
                    'notifiable_type' => Moto::class,
                    'notifiable_id' => $moto->id,
                    'priorite' => 'haute',
                ]);
                $count++;
            }
        }

        // Contrats déjà expirés
        $motosExpirees = Moto::where('contrat_fin', '<', $aujourdhui)
            ->where('statut', 'actif')
            ->with('proprietaire.user')
            ->get();

        foreach ($motosExpirees as $moto) {
            $existante = \App\Models\SystemNotification::where('type', 'contrat_expire')
                ->where('notifiable_type', Moto::class)
                ->where('notifiable_id', $moto->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->exists();

            if (!$existante) {
                NotificationService::notifierContratExpire($moto);
                $count++;
            }
        }

        $this->info("  → {$count} notification(s) de contrat envoyée(s)");
    }

    /**
     * Vérifier les maintenances programmées pour aujourd'hui/demain
     */
    protected function verifierMaintenancesProgrammees(): void
    {
        $this->info('Vérification des maintenances programmées...');

        $aujourdhui = Carbon::today();
        $demain = $aujourdhui->copy()->addDay();
        $count = 0;

        $maintenances = Maintenance::whereIn('statut', ['en_attente', 'programmee'])
            ->whereBetween('date_intervention', [$aujourdhui, $demain])
            ->with('moto.proprietaire.user', 'moto.motardActif.user')
            ->get();

        foreach ($maintenances as $maintenance) {
            $existante = \App\Models\SystemNotification::where('type', 'maintenance_rappel')
                ->where('notifiable_type', Maintenance::class)
                ->where('notifiable_id', $maintenance->id)
                ->where('created_at', '>=', now()->subDay())
                ->exists();

            if (!$existante) {
                NotificationService::notifierMaintenanceProgrammee($maintenance);
                $count++;
            }
        }

        $this->info("  → {$count} notification(s) de maintenance envoyée(s)");
    }

    /**
     * Notifier les collecteurs de leurs tournées du jour
     */
    protected function verifierTourneesJour(): void
    {
        $this->info('Vérification des tournées du jour...');

        $aujourdhui = Carbon::today();
        $count = 0;

        $tournees = Tournee::whereDate('date', $aujourdhui)
            ->whereIn('statut', ['planifiee', 'confirmee'])
            ->with('collecteur.user')
            ->get();

        foreach ($tournees as $tournee) {
            $existante = \App\Models\SystemNotification::where('type', 'tournee_jour')
                ->where('notifiable_type', Tournee::class)
                ->where('notifiable_id', $tournee->id)
                ->where('created_at', '>=', now()->startOfDay())
                ->exists();

            if (!$existante) {
                NotificationService::notifierCollecteurTourneeJour($tournee);
                $count++;
            }
        }

        $this->info("  → {$count} notification(s) de tournée envoyée(s)");
    }

    /**
     * Nettoyer les notifications expirées
     */
    protected function nettoyerNotificationsExpirees(): void
    {
        $this->info('Nettoyage des notifications expirées...');

        $deleted = NotificationService::nettoyerNotificationsExpirees();

        $this->info("  → {$deleted} notification(s) expirée(s) supprimée(s)");
    }
}

