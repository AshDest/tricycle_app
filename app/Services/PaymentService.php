<?php

namespace App\Services;

use App\Models\Versement;
use App\Models\Motard;
use App\Models\Caissier;
use App\Models\Proprietaire;
use App\Models\Payment;
use Carbon\Carbon;

/**
 * Service de gestion des paiements et versements.
 * Gère le flux: Motard → Caissier → Collecteur → NTH
 */
class PaymentService
{
    /**
     * Calculer le statut du versement automatiquement
     */
    public function calculateStatus(Versement $versement): string
    {
        if ($versement->montant >= $versement->montant_attendu) {
            return 'payé';
        } elseif ($versement->montant > 0) {
            return 'partiellement_payé';
        } elseif (Carbon::parse($versement->date_versement)->isPast()) {
            return 'en_retard';
        }
        return 'non_effectué';
    }

    /**
     * Créer un versement (par le caissier)
     */
    public function creerVersement(array $data, Caissier $caissier): Versement
    {
        $motard = Motard::findOrFail($data['motard_id']);
        $moto = $motard->motoActuelle;

        if (!$moto) {
            throw new \Exception('Ce motard n\'a pas de moto assignée.');
        }

        $versement = Versement::create([
            'motard_id' => $motard->id,
            'moto_id' => $moto->id,
            'montant' => $data['montant'],
            'montant_attendu' => $moto->montant_journalier_attendu,
            'date_versement' => $data['date_versement'] ?? now()->toDateString(),
            'heure_versement' => $data['heure_versement'] ?? now()->toTimeString(),
            'mode_paiement' => $data['mode_paiement'] ?? 'cash',
            'caissier_id' => $caissier->id,
            'validated_by_caissier_at' => now(),
            'notes' => $data['notes'] ?? null,
        ]);

        // Calculer et mettre à jour le statut
        $versement->update(['statut' => $this->calculateStatus($versement)]);

        // Mettre à jour le solde du caissier
        $caissier->increment('solde_actuel', $versement->montant);

        return $versement;
    }

    /**
     * Obtenir les arriérés d'un motard
     */
    public function getArrearsForDriver(Motard $motard): array
    {
        $versements = $motard->versements()
            ->where('statut', '!=', 'payé')
            ->get();

        return [
            'total' => $versements->sum('montant_attendu') - $versements->sum('montant'),
            'count' => $versements->count(),
            'versements' => $versements,
        ];
    }

    /**
     * Obtenir les statistiques du motard
     */
    public function getDriverStats(Motard $motard): array
    {
        $versements = $motard->versements;

        return [
            'total_jours_payes' => $versements->where('statut', 'payé')->count(),
            'total_jours_retard' => $versements->where('statut', 'en_retard')->count(),
            'total_jours_partiels' => $versements->where('statut', 'partiellement_payé')->count(),
            'montant_cumule_arrieres' => $versements->sum('montant_attendu') - $versements->sum('montant'),
            'montant_total_verse' => $versements->sum('montant'),
        ];
    }

    /**
     * Obtenir le solde d'un caissier (argent non encore collecté)
     */
    public function getCaissierBalance(Caissier $caissier): float
    {
        return $caissier->versements()
            ->whereNull('collecte_id')
            ->where('statut', '!=', 'non_effectué')
            ->sum('montant');
    }

    /**
     * Calculer le total dû à un propriétaire pour une période
     */
    public function calculerTotalDuProprietaire(Proprietaire $proprietaire, ?Carbon $debut = null, ?Carbon $fin = null): float
    {
        $query = Versement::whereIn('moto_id', $proprietaire->motos->pluck('id'))
            ->where('statut', 'payé');

        if ($debut) {
            $query->whereDate('date_versement', '>=', $debut);
        }
        if ($fin) {
            $query->whereDate('date_versement', '<=', $fin);
        }

        return $query->sum('montant');
    }

    /**
     * Créer une demande de paiement pour un propriétaire
     */
    public function creerDemandePaiement(Proprietaire $proprietaire, array $data): Payment
    {
        $totalDu = $this->calculerTotalDuProprietaire(
            $proprietaire,
            $data['periode_debut'] ?? null,
            $data['periode_fin'] ?? null
        );

        return Payment::create([
            'proprietaire_id' => $proprietaire->id,
            'total_du' => $totalDu,
            'total_paye' => 0,
            'mode_paiement' => $data['mode_paiement'],
            'numero_compte' => $proprietaire->getNumeroCompte($data['mode_paiement']),
            'statut' => 'en_attente',
            'date_demande' => now(),
            'periode_debut' => $data['periode_debut'] ?? null,
            'periode_fin' => $data['periode_fin'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Obtenir les motards en retard de paiement
     */
    public function getMotardsEnRetard(): \Illuminate\Database\Eloquent\Collection
    {
        return Motard::whereHas('versements', function ($q) {
            $q->where('statut', 'en_retard');
        })->with(['user', 'motoActuelle'])->get();
    }

    /**
     * Obtenir les statistiques globales pour le dashboard admin
     */
    public function getGlobalStats(): array
    {
        $aujourdhui = now()->toDateString();

        return [
            'versements_aujourdhui' => Versement::whereDate('date_versement', $aujourdhui)->sum('montant'),
            'versements_attendus_aujourdhui' => Versement::whereDate('date_versement', $aujourdhui)->sum('montant_attendu'),
            'motards_en_retard' => Motard::whereHas('versements', fn($q) => $q->where('statut', 'en_retard'))->count(),
            'arrieres_cumules' => Versement::where('statut', '!=', 'payé')
                ->selectRaw('SUM(montant_attendu - montant) as total')
                ->value('total') ?? 0,
            'paiements_en_attente' => Payment::where('statut', 'en_attente')->count(),
        ];
    }
}
