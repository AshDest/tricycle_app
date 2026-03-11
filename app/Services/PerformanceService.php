<?php

namespace App\Services;

use App\Models\Motard;
use App\Models\Versement;
use App\Models\Accident;
use App\Models\PerformanceMotard;
use App\Models\Recompense;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service de calcul et gestion des performances des motards
 */
class PerformanceService
{
    // Pondération des scores (total = 100%)
    const POIDS_REGULARITE = 40;     // 40% pour la régularité des versements
    const POIDS_SECURITE = 30;       // 30% pour la sécurité (pas d'accidents)
    const POIDS_VERSEMENT = 30;      // 30% pour les versements complets

    // Seuils pour les badges
    const SEUIL_DIAMANT = 95;
    const SEUIL_OR = 85;
    const SEUIL_ARGENT = 70;
    const SEUIL_BRONZE = 50;

    /**
     * Calculer les performances d'un motard pour un mois donné
     */
    public function calculerPerformanceMensuelle(Motard $motard, int $mois, int $annee): PerformanceMotard
    {
        $debut = Carbon::create($annee, $mois, 1)->startOfMonth();
        $fin = $debut->copy()->endOfMonth();

        // Récupérer la moto assignée
        $moto = $motard->motoAssignee;
        $montantJournalier = $moto?->montant_journalier_attendu ?? 10000;

        // Statistiques des versements
        $statsVersements = $this->calculerStatsVersements($motard, $debut, $fin, $montantJournalier);

        // Statistiques des accidents
        $statsAccidents = $this->calculerStatsAccidents($motard, $debut, $fin);

        // Calculer les scores
        $scoreRegularite = $this->calculerScoreRegularite($statsVersements);
        $scoreSecurite = $this->calculerScoreSecurite($statsAccidents);
        $scoreVersement = $this->calculerScoreVersement($statsVersements);

        // Score total pondéré
        $scoreTotal = (int) round(
            ($scoreRegularite * self::POIDS_REGULARITE +
             $scoreSecurite * self::POIDS_SECURITE +
             $scoreVersement * self::POIDS_VERSEMENT) / 100
        );

        // Déterminer le badge
        $badge = PerformanceMotard::determinerBadge($scoreTotal);

        // Créer ou mettre à jour l'enregistrement
        return PerformanceMotard::updateOrCreate(
            [
                'motard_id' => $motard->id,
                'mois' => $mois,
                'annee' => $annee,
            ],
            [
                'jours_travailles' => $statsVersements['jours_travailles'],
                'versements_a_temps' => $statsVersements['versements_a_temps'],
                'versements_en_retard' => $statsVersements['versements_en_retard'],
                'total_verse' => $statsVersements['total_verse'],
                'total_attendu' => $statsVersements['total_attendu'],
                'arrieres_cumules' => $statsVersements['arrieres'],
                'accidents_total' => $statsAccidents['total'],
                'accidents_mineurs' => $statsAccidents['mineurs'],
                'accidents_moderes' => $statsAccidents['moderes'],
                'accidents_graves' => $statsAccidents['graves'],
                'score_regularite' => $scoreRegularite,
                'score_securite' => $scoreSecurite,
                'score_versement' => $scoreVersement,
                'score_total' => $scoreTotal,
                'badge' => $badge,
            ]
        );
    }

    /**
     * Calculer les statistiques de versements
     */
    private function calculerStatsVersements(Motard $motard, Carbon $debut, Carbon $fin, float $montantJournalier): array
    {
        // Nombre de jours ouvrables (exclure dimanches)
        $joursOuvrables = 0;
        $date = $debut->copy();
        while ($date->lte($fin)) {
            if ($date->dayOfWeek !== Carbon::SUNDAY) {
                $joursOuvrables++;
            }
            $date->addDay();
        }

        // Versements du mois
        $versements = Versement::where('motard_id', $motard->id)
            ->whereBetween('date_versement', [$debut, $fin])
            ->get();

        $joursAvecVersement = $versements->pluck('date_versement')
            ->map(fn($d) => $d->format('Y-m-d'))
            ->unique()
            ->count();

        $totalVerse = $versements->sum('montant');
        $totalAttendu = $joursOuvrables * $montantJournalier;

        // Versements à temps vs en retard
        $versementsATemps = $versements->filter(function ($v) {
            return $v->statut === 'payé' && $v->montant >= ($v->montant_attendu ?? 0);
        })->count();

        $versementsEnRetard = $versements->filter(function ($v) {
            return $v->statut === 'partiellement_payé' || $v->montant < ($v->montant_attendu ?? 0);
        })->count();

        $arrieres = max(0, $totalAttendu - $totalVerse);

        return [
            'jours_travailles' => $joursAvecVersement,
            'jours_ouvrables' => $joursOuvrables,
            'versements_a_temps' => $versementsATemps,
            'versements_en_retard' => $versementsEnRetard,
            'total_verse' => $totalVerse,
            'total_attendu' => $totalAttendu,
            'arrieres' => $arrieres,
        ];
    }

    /**
     * Calculer les statistiques d'accidents
     */
    private function calculerStatsAccidents(Motard $motard, Carbon $debut, Carbon $fin): array
    {
        $accidents = Accident::where('motard_id', $motard->id)
            ->whereBetween('date_heure', [$debut, $fin])
            ->get();

        return [
            'total' => $accidents->count(),
            'mineurs' => $accidents->where('gravite', 'mineur')->count(),
            'moderes' => $accidents->where('gravite', 'modere')->count(),
            'graves' => $accidents->where('gravite', 'grave')->count(),
        ];
    }

    /**
     * Calculer le score de régularité (0-100)
     */
    private function calculerScoreRegularite(array $stats): int
    {
        if ($stats['jours_ouvrables'] == 0) return 100;

        // Pourcentage de jours avec versement
        $tauxPresence = ($stats['jours_travailles'] / $stats['jours_ouvrables']) * 100;

        // Pénalité pour versements en retard
        $totalVersements = $stats['versements_a_temps'] + $stats['versements_en_retard'];
        $penaliteRetard = 0;
        if ($totalVersements > 0) {
            $penaliteRetard = ($stats['versements_en_retard'] / $totalVersements) * 20;
        }

        return max(0, min(100, (int) round($tauxPresence - $penaliteRetard)));
    }

    /**
     * Calculer le score de sécurité (0-100)
     */
    private function calculerScoreSecurite(array $stats): int
    {
        // Score parfait si pas d'accident
        if ($stats['total'] == 0) return 100;

        // Pénalités par type d'accident
        $penalites = [
            'mineurs' => 5,   // -5 points par accident mineur
            'moderes' => 15,  // -15 points par accident modéré
            'graves' => 30,   // -30 points par accident grave
        ];

        $penaliteTotal =
            $stats['mineurs'] * $penalites['mineurs'] +
            $stats['moderes'] * $penalites['moderes'] +
            $stats['graves'] * $penalites['graves'];

        return max(0, 100 - $penaliteTotal);
    }

    /**
     * Calculer le score de versement complet (0-100)
     */
    private function calculerScoreVersement(array $stats): int
    {
        if ($stats['total_attendu'] == 0) return 100;

        // Pourcentage du montant versé
        $tauxVersement = ($stats['total_verse'] / $stats['total_attendu']) * 100;

        return max(0, min(100, (int) round($tauxVersement)));
    }

    /**
     * Calculer les performances de tous les motards pour un mois
     */
    public function calculerPerformancesMensuelles(int $mois, int $annee): Collection
    {
        $motards = Motard::where('is_active', true)->get();
        $performances = collect();

        foreach ($motards as $motard) {
            $performance = $this->calculerPerformanceMensuelle($motard, $mois, $annee);
            $performances->push($performance);
        }

        // Mettre à jour les rangs
        $this->mettreAJourRangs($mois, $annee);

        return $performances;
    }

    /**
     * Mettre à jour les rangs mensuels
     */
    private function mettreAJourRangs(int $mois, int $annee): void
    {
        $performances = PerformanceMotard::where('mois', $mois)
            ->where('annee', $annee)
            ->orderByDesc('score_total')
            ->get();

        $rang = 1;
        foreach ($performances as $perf) {
            $perf->update(['rang_mensuel' => $rang++]);
        }
    }

    /**
     * Obtenir le classement des motards pour un mois
     */
    public function getClassementMensuel(int $mois, int $annee): Collection
    {
        return PerformanceMotard::where('mois', $mois)
            ->where('annee', $annee)
            ->with('motard.user')
            ->orderBy('rang_mensuel')
            ->get();
    }

    /**
     * Obtenir les top motards performants
     */
    public function getTopMotards(int $limite = 10, ?int $mois = null, ?int $annee = null): Collection
    {
        $mois = $mois ?? now()->month;
        $annee = $annee ?? now()->year;

        return PerformanceMotard::where('mois', $mois)
            ->where('annee', $annee)
            ->where('badge', '!=', 'aucun')
            ->with('motard.user')
            ->orderByDesc('score_total')
            ->limit($limite)
            ->get();
    }

    /**
     * Obtenir les motards éligibles aux récompenses
     */
    public function getMotardsEligiblesRecompenses(int $mois, int $annee, string $categorieMinimum = 'bronze'): Collection
    {
        $badgesAcceptes = match($categorieMinimum) {
            'diamant' => ['diamant'],
            'or' => ['diamant', 'or'],
            'argent' => ['diamant', 'or', 'argent'],
            default => ['diamant', 'or', 'argent', 'bronze'],
        };

        return PerformanceMotard::where('mois', $mois)
            ->where('annee', $annee)
            ->whereIn('badge', $badgesAcceptes)
            ->with('motard.user')
            ->orderByDesc('score_total')
            ->get();
    }

    /**
     * Attribuer automatiquement les récompenses mensuelles
     */
    public function attribuerRecompensesMensuelles(int $mois, int $annee): Collection
    {
        $debut = Carbon::create($annee, $mois, 1);
        $fin = $debut->copy()->endOfMonth();

        $performances = $this->getMotardsEligiblesRecompenses($mois, $annee, 'bronze');
        $recompenses = collect();

        foreach ($performances as $perf) {
            // Vérifier si une récompense existe déjà pour cette période
            $existante = Recompense::where('motard_id', $perf->motard_id)
                ->where('periode_debut', $debut->format('Y-m-d'))
                ->where('periode_fin', $fin->format('Y-m-d'))
                ->exists();

            if ($existante) continue;

            // Créer la récompense
            $type = 'badge_' . $perf->badge;
            $titre = $this->genererTitreRecompense($perf);

            $recompense = Recompense::create([
                'motard_id' => $perf->motard_id,
                'type' => $type,
                'categorie' => 'excellence',
                'titre' => $titre,
                'description' => "Performance du mois de {$perf->periode}. Rang: {$perf->rang_mensuel}",
                'periode_debut' => $debut,
                'periode_fin' => $fin,
                'score_regularite' => $perf->score_regularite,
                'score_securite' => $perf->score_securite,
                'score_versement' => $perf->score_versement,
                'score_total' => $perf->score_total,
                'statut' => 'attribue',
            ]);

            $recompenses->push($recompense);
        }

        return $recompenses;
    }

    /**
     * Générer le titre de la récompense
     */
    private function genererTitreRecompense(PerformanceMotard $perf): string
    {
        $badge = ucfirst($perf->badge);
        return "Badge {$badge} - {$perf->periode}";
    }

    /**
     * Obtenir les statistiques de performance globales
     */
    public function getStatistiquesGlobales(?int $mois = null, ?int $annee = null): array
    {
        $mois = $mois ?? now()->month;
        $annee = $annee ?? now()->year;

        $performances = PerformanceMotard::where('mois', $mois)
            ->where('annee', $annee)
            ->get();

        return [
            'total_motards' => $performances->count(),
            'moyenne_score' => round($performances->avg('score_total') ?? 0, 1),
            'badges' => [
                'diamant' => $performances->where('badge', 'diamant')->count(),
                'or' => $performances->where('badge', 'or')->count(),
                'argent' => $performances->where('badge', 'argent')->count(),
                'bronze' => $performances->where('badge', 'bronze')->count(),
                'aucun' => $performances->where('badge', 'aucun')->count(),
            ],
            'moyenne_regularite' => round($performances->avg('score_regularite') ?? 0, 1),
            'moyenne_securite' => round($performances->avg('score_securite') ?? 0, 1),
            'moyenne_versement' => round($performances->avg('score_versement') ?? 0, 1),
            'total_accidents' => $performances->sum('accidents_total'),
            'total_arrieres' => $performances->sum('arrieres_cumules'),
        ];
    }

    /**
     * Obtenir l'historique des performances d'un motard
     */
    public function getHistoriqueMotard(Motard $motard, int $nombreMois = 6): Collection
    {
        return PerformanceMotard::where('motard_id', $motard->id)
            ->orderByDesc('annee')
            ->orderByDesc('mois')
            ->limit($nombreMois)
            ->get();
    }
}

