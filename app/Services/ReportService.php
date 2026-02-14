<?php

namespace App\Services;

use App\Models\Versement;
use App\Models\Motard;
use App\Models\Tournee;
use App\Models\Collecte;
use App\Models\Zone;
use App\Models\Maintenance;
use App\Models\Accident;
use Carbon\Carbon;

/**
 * Service de génération des rapports.
 * Rapports: quotidien, hebdomadaire, mensuel.
 * Comparaisons par zone et par motard.
 */
class ReportService
{
    /**
     * Générer rapport quotidien
     */
    public function generateDailyReport(string $date): array
    {
        $versements = Versement::whereDate('date_versement', $date)->get();
        $tournees = Tournee::whereDate('date', $date)->with('collectes')->get();

        // Statistiques par zone
        $parZone = [];
        $zones = Zone::all();
        foreach ($zones as $zone) {
            $zoneVersements = $versements->filter(function ($v) use ($zone) {
                return $v->motard->zone_affectation === $zone->nom;
            });
            $parZone[$zone->nom] = [
                'total_collecte' => $zoneVersements->sum('montant'),
                'total_attendu' => $zoneVersements->sum('montant_attendu'),
                'nombre_versements' => $zoneVersements->count(),
            ];
        }

        return [
            'date' => $date,
            'total_collecte' => $versements->sum('montant'),
            'total_attendu' => $versements->sum('montant_attendu'),
            'arrieres' => $versements->sum('montant_attendu') - $versements->sum('montant'),
            'nombre_versements' => $versements->count(),
            'taux_recouvrement' => $versements->sum('montant_attendu') > 0
                ? round(($versements->sum('montant') / $versements->sum('montant_attendu')) * 100, 2)
                : 0,
            'par_zone' => $parZone,
            'tournees' => [
                'total' => $tournees->count(),
                'terminees' => $tournees->where('statut', 'terminee')->count(),
                'en_retard' => $tournees->where('statut', 'en_retard')->count(),
            ],
            'versements' => $versements,
        ];
    }

    /**
     * Générer rapport hebdomadaire
     */
    public function generateWeeklyReport(Carbon $dateDebut): array
    {
        $dateFin = $dateDebut->copy()->addDays(6);

        $versements = Versement::whereBetween('date_versement', [$dateDebut, $dateFin])->get();

        // Détails par jour
        $parJour = [];
        for ($i = 0; $i < 7; $i++) {
            $jour = $dateDebut->copy()->addDays($i);
            $jourVersements = $versements->filter(function ($v) use ($jour) {
                return $v->date_versement->isSameDay($jour);
            });
            $parJour[$jour->format('Y-m-d')] = [
                'jour' => $jour->locale('fr')->dayName,
                'total_collecte' => $jourVersements->sum('montant'),
                'total_attendu' => $jourVersements->sum('montant_attendu'),
                'nombre_versements' => $jourVersements->count(),
            ];
        }

        // Comparaison par zone
        $parZone = $this->getComparaisonParZone($versements);

        return [
            'semaine' => $dateDebut->weekOfYear,
            'date_debut' => $dateDebut->format('Y-m-d'),
            'date_fin' => $dateFin->format('Y-m-d'),
            'total_collecte' => $versements->sum('montant'),
            'total_attendu' => $versements->sum('montant_attendu'),
            'arrieres_cumules' => $versements->sum('montant_attendu') - $versements->sum('montant'),
            'taux_recouvrement' => $versements->sum('montant_attendu') > 0
                ? round(($versements->sum('montant') / $versements->sum('montant_attendu')) * 100, 2)
                : 0,
            'par_jour' => $parJour,
            'par_zone' => $parZone,
        ];
    }

    /**
     * Générer rapport mensuel
     */
    public function generateMonthlyReport(int $month, int $year): array
    {
        $versements = Versement::whereYear('date_versement', $year)
            ->whereMonth('date_versement', $month)
            ->get();

        // Détails par motard
        $motards = Motard::with('user')->get();
        $detailsMotards = [];

        foreach ($motards as $motard) {
            $motardVersements = $versements->where('motard_id', $motard->id);
            if ($motardVersements->count() > 0) {
                $detailsMotards[] = [
                    'motard_id' => $motard->id,
                    'motard_name' => $motard->user->name,
                    'zone' => $motard->zone_affectation,
                    'total_collecte' => $motardVersements->sum('montant'),
                    'total_attendu' => $motardVersements->sum('montant_attendu'),
                    'arrieres' => $motardVersements->sum('montant_attendu') - $motardVersements->sum('montant'),
                    'jours_payes' => $motardVersements->where('statut', 'payé')->count(),
                    'jours_retard' => $motardVersements->where('statut', 'en_retard')->count(),
                ];
            }
        }

        // Comparaison par zone
        $parZone = $this->getComparaisonParZone($versements);

        // Maintenances et accidents du mois
        $maintenances = Maintenance::whereYear('date_intervention', $year)
            ->whereMonth('date_intervention', $month)
            ->get();
        $accidents = Accident::whereYear('date_heure', $year)
            ->whereMonth('date_heure', $month)
            ->get();

        return [
            'mois' => Carbon::create($year, $month)->locale('fr')->monthName,
            'month' => $month,
            'year' => $year,
            'total_collecte' => $versements->sum('montant'),
            'total_attendu' => $versements->sum('montant_attendu'),
            'arrieres_cumules' => $versements->sum('montant_attendu') - $versements->sum('montant'),
            'taux_recouvrement' => $versements->sum('montant_attendu') > 0
                ? round(($versements->sum('montant') / $versements->sum('montant_attendu')) * 100, 2)
                : 0,
            'par_zone' => $parZone,
            'details_motards' => $detailsMotards,
            'maintenances' => [
                'nombre' => $maintenances->count(),
                'cout_total' => $maintenances->sum('cout_total'),
            ],
            'accidents' => [
                'nombre' => $accidents->count(),
                'cout_estime' => $accidents->sum('estimation_cout'),
            ],
        ];
    }

    /**
     * Générer rapport pour un propriétaire
     */
    public function generateOwnerReport(int $proprietaireId, int $month, int $year): array
    {
        $proprietaire = \App\Models\Proprietaire::with(['motos', 'payments'])->findOrFail($proprietaireId);

        $versements = Versement::whereIn('moto_id', $proprietaire->motos->pluck('id'))
            ->whereYear('date_versement', $year)
            ->whereMonth('date_versement', $month)
            ->get();

        // Détails par moto
        $detailsMotos = [];
        foreach ($proprietaire->motos as $moto) {
            $motoVersements = $versements->where('moto_id', $moto->id);
            $detailsMotos[] = [
                'moto_id' => $moto->id,
                'matricule' => $moto->numero_matricule,
                'plaque' => $moto->plaque_immatriculation,
                'motard' => $moto->motard?->user->name ?? 'Non assigné',
                'total_collecte' => $motoVersements->sum('montant'),
                'total_attendu' => $motoVersements->sum('montant_attendu'),
            ];
        }

        // Paiements du mois
        $paiements = $proprietaire->payments()
            ->whereYear('date_demande', $year)
            ->whereMonth('date_demande', $month)
            ->get();

        return [
            'proprietaire' => [
                'id' => $proprietaire->id,
                'nom' => $proprietaire->raison_sociale ?? $proprietaire->user->name,
            ],
            'mois' => Carbon::create($year, $month)->locale('fr')->monthName,
            'year' => $year,
            'total_collecte' => $versements->sum('montant'),
            'total_attendu' => $versements->sum('montant_attendu'),
            'details_motos' => $detailsMotos,
            'paiements' => [
                'total_paye' => $paiements->where('statut', 'payé')->sum('total_paye'),
                'en_attente' => $paiements->where('statut', 'en_attente')->sum('total_du'),
            ],
        ];
    }

    /**
     * Obtenir la comparaison par zone
     */
    private function getComparaisonParZone($versements): array
    {
        $parZone = [];
        $zones = Zone::all();

        foreach ($zones as $zone) {
            $zoneVersements = $versements->filter(function ($v) use ($zone) {
                return $v->motard->zone_affectation === $zone->nom;
            });

            if ($zoneVersements->count() > 0) {
                $parZone[$zone->nom] = [
                    'total_collecte' => $zoneVersements->sum('montant'),
                    'total_attendu' => $zoneVersements->sum('montant_attendu'),
                    'arrieres' => $zoneVersements->sum('montant_attendu') - $zoneVersements->sum('montant'),
                    'taux_recouvrement' => $zoneVersements->sum('montant_attendu') > 0
                        ? round(($zoneVersements->sum('montant') / $zoneVersements->sum('montant_attendu')) * 100, 2)
                        : 0,
                    'nombre_motards' => $zoneVersements->pluck('motard_id')->unique()->count(),
                ];
            }
        }

        return $parZone;
    }

    /**
     * Obtenir les statistiques OKAMI (dashboard superviseur)
     */
    public function getOkamiDashboardStats(): array
    {
        $aujourdhui = now()->toDateString();
        $debutMois = now()->startOfMonth();

        return [
            'aujourdhui' => $this->generateDailyReport($aujourdhui),
            'ce_mois' => [
                'total_collecte' => Versement::whereMonth('date_versement', now()->month)
                    ->whereYear('date_versement', now()->year)
                    ->sum('montant'),
                'arrieres_cumules' => Versement::where('statut', '!=', 'payé')
                    ->selectRaw('SUM(montant_attendu - montant) as total')
                    ->value('total') ?? 0,
            ],
            'motards_en_retard' => Motard::whereHas('versements', function ($q) {
                $q->where('statut', 'en_retard');
            })->count(),
            'alertes' => $this->getAlertes(),
        ];
    }

    /**
     * Obtenir les alertes actives
     */
    private function getAlertes(): array
    {
        return [
            'arrieres_critiques' => Motard::all()->filter(function ($m) {
                return $m->hasArrieresCritiques();
            })->count(),
            'accidents_non_resolus' => Accident::whereNull('reparation_terminee_at')->count(),
            'maintenances_en_attente' => Maintenance::where('statut', 'en_attente')->count(),
        ];
    }
}
