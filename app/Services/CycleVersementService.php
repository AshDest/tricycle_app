<?php

namespace App\Services;

use App\Models\Motard;
use App\Models\Versement;
use Carbon\Carbon;

/**
 * Service de gestion du cycle de versement des motards.
 *
 * Principe:
 * - Un motard doit totaliser 6 jours de travail (versements journaliers).
 * - Ce cycle ne dépend PAS de la semaine civile.
 * - Après 6 jours travaillés, le 7ème jour est un jour de repos (pas de versement requis).
 * - Si le motard ne travaille pas certains jours, le cycle reprend là où il s'est arrêté.
 * - Chaque motard (titulaire et secondaire) a son propre cycle indépendant.
 * - Une moto peut être utilisée par un motard secondaire qui a son propre cycle.
 */
class CycleVersementService
{
    const JOURS_TRAVAIL_PAR_CYCLE = 6;

    /**
     * Obtenir les informations du cycle actuel d'un motard.
     *
     * @param Motard $motard
     * @return array
     */
    public static function getCycleInfo(Motard $motard): array
    {
        // RÈGLE CYCLE:
        // - On compte UNIQUEMENT les jours où CE motard a personnellement travaillé.
        // - Cas 1: Il est titulaire de la moto ET aucun remplaçant n'a été désigné (motard_secondaire_id IS NULL)
        //   → C'est lui qui a travaillé, ça compte dans son cycle.
        // - Cas 2: Il est désigné comme motard secondaire sur la moto d'un autre
        //   → C'est lui qui a physiquement travaillé, ça compte dans son cycle.
        // - Cas EXCLU: Un versement sur sa moto (motard_id = lui) mais avec un motard_secondaire_id renseigné
        //   → C'est le remplaçant qui a travaillé, PAS lui. Ne compte PAS dans son cycle.

        // Jours où il a travaillé comme titulaire (sans remplaçant)
        $joursCommeTitulaire = Versement::where('motard_id', $motard->id)
            ->whereNull('motard_secondaire_id') // Pas de remplaçant = c'est lui qui a travaillé
            ->where(function ($q) {
                $q->where('type', 'journalier')->orWhereNull('type');
            })
            ->where('statut', '!=', 'non_effectué')
            ->orderBy('date_versement')
            ->get()
            ->groupBy(fn($v) => Carbon::parse($v->date_versement)->format('Y-m-d'))
            ->keys();

        // Jours où il a travaillé comme remplaçant sur la moto d'un autre
        $joursCommeSecondaire = Versement::where('motard_secondaire_id', $motard->id)
            ->where(function ($q) {
                $q->where('type', 'journalier')->orWhereNull('type');
            })
            ->where('statut', '!=', 'non_effectué')
            ->orderBy('date_versement')
            ->get()
            ->groupBy(fn($v) => Carbon::parse($v->date_versement)->format('Y-m-d'))
            ->keys();

        // Fusionner et dédupliquer: tous les jours où ce motard a PHYSIQUEMENT travaillé
        $toutesLesDates = $joursCommeTitulaire->merge($joursCommeSecondaire)
            ->unique()
            ->sort()
            ->values();

        $totalJoursTravailles = $toutesLesDates->count();

        if ($totalJoursTravailles == 0) {
            return [
                'jour_dans_cycle' => 0,
                'jours_travailles_cycle' => 0,
                'jours_restants_cycle' => self::JOURS_TRAVAIL_PAR_CYCLE,
                'est_jour_repos' => false,
                'cycle_numero' => 1,
                'total_jours_travailles' => 0,
                'dernier_versement' => null,
                'prochain_repos_apres' => self::JOURS_TRAVAIL_PAR_CYCLE,
                'dates_cycle_actuel' => [],
                'message' => 'Aucun versement enregistré. Nouveau cycle (Jour 1/' . self::JOURS_TRAVAIL_PAR_CYCLE . ').',
            ];
        }

        // Calculer le cycle actuel
        $cycleNumero = (int) floor(($totalJoursTravailles - 1) / self::JOURS_TRAVAIL_PAR_CYCLE) + 1;
        $joursDansCycleActuel = (($totalJoursTravailles - 1) % self::JOURS_TRAVAIL_PAR_CYCLE) + 1;

        $dernierVersementDate = Carbon::parse($toutesLesDates->last());
        $joursDepuisDernier = $dernierVersementDate->diffInDays(Carbon::today());

        // Déterminer si aujourd'hui est un jour de repos
        // Repos = le cycle est complet (6 jours travaillés) ET le dernier versement est récent
        $cycleComplet = ($joursDansCycleActuel == self::JOURS_TRAVAIL_PAR_CYCLE);
        $estJourRepos = false;

        if ($cycleComplet) {
            // Le cycle est complet. Le prochain jour calendaire après le dernier versement est repos.
            // Si on est le jour juste après le 6ème versement → repos
            // Si on est 2+ jours après → le repos a été "consommé", nouveau cycle
            if ($joursDepuisDernier == 1) {
                $estJourRepos = true;
            } elseif ($joursDepuisDernier == 0) {
                // Le 6ème versement est aujourd'hui → demain sera repos
                $estJourRepos = false;
            } else {
                // Plus d'1 jour depuis le 6ème versement → repos consommé, nouveau cycle
                $joursDansCycleActuel = 0;
                $cycleNumero++;
            }
        }

        $joursRestants = $estJourRepos ? 0 : (self::JOURS_TRAVAIL_PAR_CYCLE - $joursDansCycleActuel);

        // Dates du cycle actuel
        $debutIndexCycle = ($cycleNumero - 1) * self::JOURS_TRAVAIL_PAR_CYCLE;
        $datesCycleActuel = $toutesLesDates->slice($debutIndexCycle, self::JOURS_TRAVAIL_PAR_CYCLE)->values();

        // Message descriptif
        $message = self::genererMessage($joursDansCycleActuel, $estJourRepos, $cycleNumero, $joursRestants);

        return [
            'jour_dans_cycle' => $joursDansCycleActuel,
            'jours_travailles_cycle' => $joursDansCycleActuel,
            'jours_restants_cycle' => $joursRestants,
            'est_jour_repos' => $estJourRepos,
            'cycle_numero' => $cycleNumero,
            'total_jours_travailles' => $totalJoursTravailles,
            'dernier_versement' => $dernierVersementDate->format('d/m/Y'),
            'prochain_repos_apres' => $joursRestants,
            'dates_cycle_actuel' => $datesCycleActuel->toArray(),
            'message' => $message,
        ];
    }

    /**
     * Vérifier si un motard peut faire un versement aujourd'hui.
     */
    public static function peutFaireVersement(Motard $motard, ?Carbon $date = null): array
    {
        $date = $date ?? Carbon::today();
        $cycleInfo = self::getCycleInfo($motard);

        // Jour de repos → pas de versement journalier requis (mais arriérés OK)
        if ($cycleInfo['est_jour_repos']) {
            return [
                'peut_verser' => false,
                'peut_verser_arrieres' => true,
                'raison' => 'Jour de repos (cycle #' . $cycleInfo['cycle_numero'] . ' complété). Seul le remboursement d\'arriérés est autorisé.',
                'cycle_info' => $cycleInfo,
            ];
        }

        // Vérifier si un versement journalier existe déjà pour cette date
        $versementExistant = Versement::where('motard_id', $motard->id)
            ->whereDate('date_versement', $date)
            ->where(function ($q) {
                $q->where('type', 'journalier')->orWhereNull('type');
            })
            ->exists();

        if ($versementExistant) {
            return [
                'peut_verser' => false,
                'peut_verser_arrieres' => true,
                'raison' => 'Un versement journalier existe déjà pour cette date.',
                'cycle_info' => $cycleInfo,
            ];
        }

        return [
            'peut_verser' => true,
            'peut_verser_arrieres' => true,
            'raison' => 'Jour ' . ($cycleInfo['jour_dans_cycle'] + 1) . '/' . self::JOURS_TRAVAIL_PAR_CYCLE . ' du cycle #' . $cycleInfo['cycle_numero'],
            'cycle_info' => $cycleInfo,
        ];
    }

    /**
     * Obtenir le cycle info pour un motard secondaire sur une moto donnée.
     * Le secondaire a son propre cycle indépendant.
     */
    public static function getCycleInfoSecondaire(Motard $motardSecondaire): array
    {
        return self::getCycleInfo($motardSecondaire);
    }

    /**
     * Obtenir un résumé des cycles pour tous les motards actifs.
     */
    public static function getResumeCyclesMotards(): array
    {
        $motards = Motard::with(['user', 'moto'])->where('is_active', true)->get();
        $resume = [];

        foreach ($motards as $motard) {
            $cycleInfo = self::getCycleInfo($motard);
            $resume[] = [
                'motard_id' => $motard->id,
                'motard_nom' => $motard->user?->name ?? 'N/A',
                'moto_plaque' => $motard->moto?->plaque_immatriculation ?? 'N/A',
                'cycle_info' => $cycleInfo,
            ];
        }

        return $resume;
    }

    /**
     * Générer un message descriptif pour l'état du cycle.
     */
    private static function genererMessage(int $joursDansCycle, bool $estRepos, int $cycleNum, int $joursRestants): string
    {
        if ($estRepos) {
            return "🟢 Jour de repos! Cycle #{$cycleNum} complété (" . self::JOURS_TRAVAIL_PAR_CYCLE . "/" . self::JOURS_TRAVAIL_PAR_CYCLE . " jours travaillés).";
        }

        if ($joursDansCycle == 0) {
            return "🔵 Nouveau cycle #{$cycleNum} — Jour 1/" . self::JOURS_TRAVAIL_PAR_CYCLE . ".";
        }

        $prochainJour = $joursDansCycle + 1;
        if ($prochainJour > self::JOURS_TRAVAIL_PAR_CYCLE) {
            return "🟢 Cycle #{$cycleNum} terminé! Prochain versement = nouveau cycle.";
        }

        return "🔵 Cycle #{$cycleNum} — Jour {$joursDansCycle}/" . self::JOURS_TRAVAIL_PAR_CYCLE . " travaillé(s). Encore {$joursRestants} jour(s) avant le repos.";
    }
}

