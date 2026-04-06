<?php

namespace App\Services;

use App\Models\Moto;
use App\Models\Versement;
use App\Models\Proprietaire;
use App\Models\SystemSetting;
use Carbon\Carbon;

/**
 * Service de calcul de répartition des recettes
 *
 * Principe:
 * - Semaine = 6 jours de travail
 * - Tous les versements vont dans une caisse unique (plus de split OKAMI/Propriétaire)
 */
class RepartitionService
{
    const JOURS_SEMAINE = 6;

    /**
     * Obtenir le montant journalier attendu (depuis les paramètres système ou défaut)
     */
    public static function getMontantJournalier(): float
    {
        return (float) SystemSetting::get('montant_journalier_defaut', 10000);
    }

    /**
     * Calculer le montant hebdomadaire attendu
     */
    public static function getMontantHebdomadaire(?float $montantJournalier = null): float
    {
        $montantJournalier = $montantJournalier ?? self::getMontantJournalier();
        return $montantJournalier * self::JOURS_SEMAINE;
    }

    /**
     * Obtenir la répartition détaillée pour un montant donné
     * (Simplifié: plus de scission)
     */
    public static function getRepartition(float $montantTotal): array
    {
        return [
            'total' => $montantTotal,
            'jours_semaine' => self::JOURS_SEMAINE,
        ];
    }

    /**
     * @deprecated Conservé pour compatibilité, retourne 0
     */
    public static function getPartProprietaire(float $montantTotal): float
    {
        return $montantTotal;
    }

    /**
     * @deprecated Conservé pour compatibilité, retourne 0
     */
    public static function getPartOkami(float $montantTotal): float
    {
        return 0;
    }

    /**
     * Calculer la répartition hebdomadaire pour une moto
     */
    public static function getRepartitionHebdomadaireMoto(Moto $moto, ?Carbon $dateDebut = null): array
    {
        $dateDebut = $dateDebut ?? Carbon::now()->startOfWeek();
        $dateFin = $dateDebut->copy()->addDays(self::JOURS_SEMAINE - 1);

        $montantJournalier = $moto->montant_journalier_attendu ?? self::getMontantJournalier();
        $montantHebdomadaireAttendu = $montantJournalier * self::JOURS_SEMAINE;

        // Versements de la semaine
        $versements = Versement::where('moto_id', $moto->id)
            ->whereBetween('date_versement', [$dateDebut, $dateFin])
            ->get();

        $montantVerse = $versements->sum('montant');
        $nbVersements = $versements->count();

        return [
            'moto_id' => $moto->id,
            'plaque' => $moto->plaque_immatriculation,
            'periode' => [
                'debut' => $dateDebut->format('d/m/Y'),
                'fin' => $dateFin->format('d/m/Y'),
            ],
            'montant_journalier' => $montantJournalier,
            'montant_hebdomadaire_attendu' => $montantHebdomadaireAttendu,
            'montant_verse' => $montantVerse,
            'nb_versements' => $nbVersements,
            'ecart' => $montantVerse - $montantHebdomadaireAttendu,
        ];
    }

    /**
     * Calculer la répartition hebdomadaire pour un propriétaire (toutes ses motos)
     */
    public static function getRepartitionHebdomadaireProprietaire(Proprietaire $proprietaire, ?Carbon $dateDebut = null): array
    {
        $dateDebut = $dateDebut ?? Carbon::now()->startOfWeek();
        $dateFin = $dateDebut->copy()->addDays(self::JOURS_SEMAINE - 1);

        $motos = $proprietaire->motos()->where('statut', 'actif')->get();

        $totalAttendu = 0;
        $totalVerse = 0;
        $detailsMotos = [];

        foreach ($motos as $moto) {
            $detail = self::getRepartitionHebdomadaireMoto($moto, $dateDebut->copy());
            $detailsMotos[] = $detail;

            $totalAttendu += $detail['montant_hebdomadaire_attendu'];
            $totalVerse += $detail['montant_verse'];
        }

        return [
            'proprietaire_id' => $proprietaire->id,
            'proprietaire_nom' => $proprietaire->user->name ?? 'N/A',
            'periode' => [
                'debut' => $dateDebut->format('d/m/Y'),
                'fin' => $dateFin->format('d/m/Y'),
            ],
            'nb_motos' => $motos->count(),
            'total_attendu' => $totalAttendu,
            'total_verse' => $totalVerse,
            'total_part_proprietaire' => $totalVerse, // Plus de scission: tout va au propriétaire
            'total_part_okami' => 0,                  // Plus de scission: part OKAMI = 0
            'ecart' => $totalVerse - $totalAttendu,
            'details_motos' => $detailsMotos,
        ];
    }

    /**
     * Calculer le résumé global hebdomadaire (toutes les motos actives)
     */
    public static function getResumeHebdomadaireGlobal(?Carbon $dateDebut = null): array
    {
        $dateDebut = $dateDebut ?? Carbon::now()->startOfWeek();
        $dateFin = $dateDebut->copy()->addDays(self::JOURS_SEMAINE - 1);

        $motos = Moto::where('statut', 'actif')->get();

        $totalAttendu = 0;
        $totalVerse = 0;

        foreach ($motos as $moto) {
            $montantJournalier = $moto->montant_journalier_attendu ?? self::getMontantJournalier();
            $totalAttendu += $montantJournalier * self::JOURS_SEMAINE;

            $versements = Versement::where('moto_id', $moto->id)
                ->whereBetween('date_versement', [$dateDebut, $dateFin])
                ->sum('montant');

            $totalVerse += $versements;
        }

        return [
            'periode' => [
                'debut' => $dateDebut->format('d/m/Y'),
                'fin' => $dateFin->format('d/m/Y'),
            ],
            'nb_motos_actives' => $motos->count(),
            'total_attendu' => $totalAttendu,
            'total_verse' => $totalVerse,
            'repartition_attendue' => [
                'part_proprietaires' => $totalAttendu, // Plus de scission: tout va au propriétaire
                'part_okami' => 0,
            ],
            'repartition_verse' => [
                'part_proprietaires' => $totalVerse, // Plus de scission: tout va au propriétaire
                'part_okami' => 0,
            ],
            'ecart' => $totalVerse - $totalAttendu,
            'taux_recouvrement' => $totalAttendu > 0 ? round(($totalVerse / $totalAttendu) * 100, 2) : 0,
        ];
    }

    /**
     * Obtenir les semaines du mois en cours
     */
    public static function getSemainesDuMois(?Carbon $mois = null): array
    {
        $mois = $mois ?? Carbon::now();
        $debut = $mois->copy()->startOfMonth();
        $fin = $mois->copy()->endOfMonth();

        $semaines = [];
        $current = $debut->copy()->startOfWeek();

        while ($current->lte($fin)) {
            $finSemaine = $current->copy()->addDays(self::JOURS_SEMAINE - 1);

            $semaines[] = [
                'debut' => $current->copy(),
                'fin' => $finSemaine,
                'label' => 'Semaine du ' . $current->format('d/m') . ' au ' . $finSemaine->format('d/m'),
            ];

            $current->addDays(7);
        }

        return $semaines;
    }
}

