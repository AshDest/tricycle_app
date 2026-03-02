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
 * - Part Propriétaire = 5/6 des recettes (équivalent 5 jours)
 * - Part OKAMI = 1/6 des recettes (équivalent 1 jour)
 */
class RepartitionService
{
    const JOURS_SEMAINE = 6;
    const JOURS_PROPRIETAIRE = 5;
    const JOURS_OKAMI = 1;

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
     * Calculer la part du propriétaire (5/6)
     */
    public static function getPartProprietaire(float $montantTotal): float
    {
        return ($montantTotal / self::JOURS_SEMAINE) * self::JOURS_PROPRIETAIRE;
    }

    /**
     * Calculer la part OKAMI (1/6)
     */
    public static function getPartOkami(float $montantTotal): float
    {
        return ($montantTotal / self::JOURS_SEMAINE) * self::JOURS_OKAMI;
    }

    /**
     * Obtenir la répartition détaillée pour un montant donné
     */
    public static function getRepartition(float $montantTotal): array
    {
        return [
            'total' => $montantTotal,
            'part_proprietaire' => self::getPartProprietaire($montantTotal),
            'part_okami' => self::getPartOkami($montantTotal),
            'pourcentage_proprietaire' => round((self::JOURS_PROPRIETAIRE / self::JOURS_SEMAINE) * 100, 2),
            'pourcentage_okami' => round((self::JOURS_OKAMI / self::JOURS_SEMAINE) * 100, 2),
            'jours_semaine' => self::JOURS_SEMAINE,
            'jours_proprietaire' => self::JOURS_PROPRIETAIRE,
            'jours_okami' => self::JOURS_OKAMI,
        ];
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

        $repartition = self::getRepartition($montantVerse);

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
            'part_proprietaire' => $repartition['part_proprietaire'],
            'part_okami' => $repartition['part_okami'],
            'part_proprietaire_attendue' => self::getPartProprietaire($montantHebdomadaireAttendu),
            'part_okami_attendue' => self::getPartOkami($montantHebdomadaireAttendu),
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
        $totalPartProprietaire = 0;
        $totalPartOkami = 0;
        $detailsMotos = [];

        foreach ($motos as $moto) {
            $detail = self::getRepartitionHebdomadaireMoto($moto, $dateDebut->copy());
            $detailsMotos[] = $detail;

            $totalAttendu += $detail['montant_hebdomadaire_attendu'];
            $totalVerse += $detail['montant_verse'];
            $totalPartProprietaire += $detail['part_proprietaire'];
            $totalPartOkami += $detail['part_okami'];
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
            'ecart' => $totalVerse - $totalAttendu,
            'total_part_proprietaire' => $totalPartProprietaire,
            'total_part_okami' => $totalPartOkami,
            'part_proprietaire_attendue' => self::getPartProprietaire($totalAttendu),
            'part_okami_attendue' => self::getPartOkami($totalAttendu),
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

        $repartitionVerse = self::getRepartition($totalVerse);
        $repartitionAttendu = self::getRepartition($totalAttendu);

        return [
            'periode' => [
                'debut' => $dateDebut->format('d/m/Y'),
                'fin' => $dateFin->format('d/m/Y'),
            ],
            'nb_motos_actives' => $motos->count(),
            'total_attendu' => $totalAttendu,
            'total_verse' => $totalVerse,
            'ecart' => $totalVerse - $totalAttendu,
            'taux_recouvrement' => $totalAttendu > 0 ? round(($totalVerse / $totalAttendu) * 100, 2) : 0,
            'repartition_verse' => [
                'part_proprietaires' => $repartitionVerse['part_proprietaire'],
                'part_okami' => $repartitionVerse['part_okami'],
            ],
            'repartition_attendue' => [
                'part_proprietaires' => $repartitionAttendu['part_proprietaire'],
                'part_okami' => $repartitionAttendu['part_okami'],
            ],
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

