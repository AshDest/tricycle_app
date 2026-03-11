<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PerformanceService;
use Carbon\Carbon;

class CalculerPerformancesMensuelles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'motards:performances {--mois= : Mois à calculer (1-12)} {--annee= : Année à calculer} {--attribuer-recompenses : Attribuer automatiquement les récompenses}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculer les performances mensuelles des motards et attribuer les récompenses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mois = $this->option('mois') ?? now()->month;
        $annee = $this->option('annee') ?? now()->year;

        $this->info("Calcul des performances pour {$mois}/{$annee}...");

        $service = new PerformanceService();

        try {
            // Calculer les performances
            $performances = $service->calculerPerformancesMensuelles((int)$mois, (int)$annee);

            $this->info("✅ Performances calculées pour {$performances->count()} motards");

            // Afficher les statistiques
            $stats = $service->getStatistiquesGlobales((int)$mois, (int)$annee);

            $this->table(
                ['Métrique', 'Valeur'],
                [
                    ['Score moyen', $stats['moyenne_score'] . '/100'],
                    ['Badges Diamant', $stats['badges']['diamant']],
                    ['Badges Or', $stats['badges']['or']],
                    ['Badges Argent', $stats['badges']['argent']],
                    ['Badges Bronze', $stats['badges']['bronze']],
                    ['Sans badge', $stats['badges']['aucun']],
                    ['Total accidents', $stats['total_accidents']],
                    ['Total arriérés', number_format($stats['total_arrieres']) . ' FC'],
                ]
            );

            // Attribuer les récompenses si demandé
            if ($this->option('attribuer-recompenses')) {
                $this->info("Attribution des récompenses...");
                $recompenses = $service->attribuerRecompensesMensuelles((int)$mois, (int)$annee);
                $this->info("✅ {$recompenses->count()} récompenses attribuées");
            }

            // Afficher le Top 5
            $top = $service->getTopMotards(5, (int)$mois, (int)$annee);
            if ($top->count() > 0) {
                $this->newLine();
                $this->info("🏆 Top 5 Motards:");
                $this->table(
                    ['Rang', 'Motard', 'Score', 'Badge'],
                    $top->map(fn($p, $i) => [
                        $i + 1,
                        $p->motard?->user?->name ?? 'N/A',
                        $p->score_total . '/100',
                        ucfirst($p->badge),
                    ])->toArray()
                );
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Erreur: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
