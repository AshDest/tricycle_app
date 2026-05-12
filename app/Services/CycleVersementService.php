<?php

namespace App\Services;

use App\Models\Motard;
use App\Models\Versement;
use Carbon\Carbon;

/**
 * Service de gestion du cycle de versement des motards.
 *
 * Principe:
 * - Les versements sont requis du Lundi au Vendredi (jours ouvrables).
 * - Samedi et Dimanche sont des jours de repos (aucun versement journalier requis).
 * - Les arriérés peuvent être remboursés n'importe quel jour.
 * - Chaque motard (titulaire et secondaire) a son propre suivi indépendant.
 */
class CycleVersementService
{
    const JOURS_TRAVAIL_PAR_CYCLE = 5; // Lundi à Vendredi

    /**
     * Obtenir les informations de la semaine courante d'un motard.
     *
     * @param Motard $motard
     * @return array
     */
    public static function getCycleInfo(Motard $motard): array
    {
        $today = Carbon::today();
        // Repos = Samedi (6) ou Dimanche (7)
        $estWeekend = $today->isWeekend();

        // Jours où il a travaillé comme titulaire (sans remplaçant)
        $joursCommeTitulaire = Versement::where('motard_id', $motard->id)
            ->whereNull('motard_secondaire_id')
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

        // Tous les jours où ce motard a physiquement travaillé
        $toutesLesDates = $joursCommeTitulaire->merge($joursCommeSecondaire)
            ->unique()
            ->sort()
            ->values();

        $totalJoursTravailles = $toutesLesDates->count();

        // Semaine courante: Lundi → Vendredi
        $debutSemaine = $today->copy()->startOfWeek(Carbon::MONDAY);
        $finSemaine   = $today->copy()->startOfWeek(Carbon::MONDAY)->addDays(4); // Vendredi

        $datesCetteSemaine = $toutesLesDates->filter(function ($date) use ($debutSemaine, $finSemaine) {
            $d = Carbon::parse($date);
            return $d->between($debutSemaine, $finSemaine);
        })->values();

        $joursTravailesCetteSemaine = $datesCetteSemaine->count();
        $joursRestantsSemaine = max(0, self::JOURS_TRAVAIL_PAR_CYCLE - $joursTravailesCetteSemaine);

        $dernierVersementDate = $toutesLesDates->isNotEmpty() ? Carbon::parse($toutesLesDates->last()) : null;

        // Numéro de semaine ISO (pour compatibilité affichage)
        $cycleNumero = $today->weekOfYear;

        if ($estWeekend) {
            $message = '🟢 Weekend — Jour de repos. Les versements reprennent lundi.';
        } elseif ($joursTravailesCetteSemaine == 0) {
            $message = '🔵 Semaine ' . $cycleNumero . ' — Aucun versement cette semaine. Jour 1/5.';
        } else {
            $message = '🔵 Semaine ' . $cycleNumero . ' — ' . $joursTravailesCetteSemaine . '/5 jour(s) travaillé(s). Encore ' . $joursRestantsSemaine . ' jour(s).';
        }

        return [
            'jour_dans_cycle'       => $joursTravailesCetteSemaine,
            'jours_travailles_cycle'=> $joursTravailesCetteSemaine,
            'jours_restants_cycle'  => $joursRestantsSemaine,
            'est_jour_repos'        => $estWeekend,
            'cycle_numero'          => $cycleNumero,
            'total_jours_travailles'=> $totalJoursTravailles,
            'dernier_versement'     => $dernierVersementDate?->format('d/m/Y'),
            'prochain_repos_apres'  => $joursRestantsSemaine,
            'dates_cycle_actuel'    => $datesCetteSemaine->toArray(),
            'message'               => $message,
        ];
    }

    /**
     * Vérifier si un motard peut faire un versement à la date donnée.
     */
    public static function peutFaireVersement(Motard $motard, ?Carbon $date = null): array
    {
        $date = $date ?? Carbon::today();
        $cycleInfo = self::getCycleInfo($motard);

        // Weekend → pas de versement journalier (arriérés toujours OK)
        if ($date->isWeekend()) {
            return [
                'peut_verser'         => false,
                'peut_verser_arrieres'=> true,
                'raison'              => 'Jour de repos (weekend). Seul le remboursement d\'arriérés est autorisé.',
                'cycle_info'          => $cycleInfo,
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
                'peut_verser'         => false,
                'peut_verser_arrieres'=> true,
                'raison'              => 'Un versement journalier existe déjà pour cette date.',
                'cycle_info'          => $cycleInfo,
            ];
        }

        return [
            'peut_verser'         => true,
            'peut_verser_arrieres'=> true,
            'raison'              => $date->translatedFormat('l') . ' — jour ouvrable (Lun-Ven)',
            'cycle_info'          => $cycleInfo,
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

}

