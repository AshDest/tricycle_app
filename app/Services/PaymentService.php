<?php

namespace App\Services;

use App\Models\Versement;
use App\Models\Motard;
use App\Models\Caissier;
use App\Models\Proprietaire;
use App\Models\Payment;
use App\Models\Moto;
use App\Models\SystemSetting;
use Carbon\Carbon;

/**
 * Service de gestion des paiements et versements.
 * Gère le flux: Motard → Caissier → Collecteur → OKAMI → Propriétaire
 *
 * RÈGLES MÉTIER:
 * - Montant journalier attendu par moto: configurable dans les paramètres système
 * - Montant hebdomadaire: montant journalier × 5 jours
 * - Le propriétaire ne peut recevoir que ce qui a été collecté (pas plus)
 */
class PaymentService
{
    const JOURS_PAR_SEMAINE = 5;

    /**
     * Obtenir le montant journalier par défaut depuis les paramètres système
     */
    public static function getMontantJournalierDefault(): float
    {
        return SystemSetting::getMontantJournalierDefaut();
    }

    /**
     * Calculer le statut du versement automatiquement
     */
    public function calculateStatus(Versement $versement): string
    {
        if ($versement->montant >= $versement->montant_attendu) {
            return 'paye';
        } elseif ($versement->montant > 0) {
            return 'partiel';
        } elseif (Carbon::parse($versement->date_versement)->isPast()) {
            return 'en_retard';
        }
        return 'non_effectue';
    }

    /**
     * Créer un versement (par le caissier)
     */
    public function creerVersement(array $data, Caissier $caissier): Versement
    {
        $motard = Motard::findOrFail($data['motard_id']);
        $moto = $motard->moto;

        if (!$moto) {
            throw new \Exception('Ce motard n\'a pas de moto assignée.');
        }

        $montantAttendu = $moto->montant_journalier_attendu ?? self::getMontantJournalierDefault();

        $versement = Versement::create([
            'motard_id' => $motard->id,
            'moto_id' => $moto->id,
            'montant' => $data['montant'],
            'montant_attendu' => $montantAttendu,
            'arrieres' => max(0, $montantAttendu - $data['montant']),
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
     * Calculer le SOLDE DISPONIBLE d'un propriétaire
     * = Total versements payés de ses motos - Total paiements déjà effectués
     */
    public function getSoldeDisponibleProprietaire(Proprietaire $proprietaire): float
    {
        // Total des versements payés pour les motos du propriétaire
        $totalVersements = Versement::whereIn('moto_id', $proprietaire->motos->pluck('id'))
            ->where('versements.statut', 'payé')
            ->sum('montant');

        // Total des paiements déjà validés pour ce propriétaire
        $totalPaiementsValides = Payment::where('proprietaire_id', $proprietaire->id)
            ->whereIn('statut', ['paye', 'approuve'])
            ->sum('total_paye');

        return max(0, $totalVersements - $totalPaiementsValides);
    }

    /**
     * Obtenir les statistiques financières d'un propriétaire
     */
    public function getStatsProprietaire(Proprietaire $proprietaire): array
    {
        $motosIds = $proprietaire->motos->pluck('id');

        // Versements par statut
        $versements = Versement::whereIn('moto_id', $motosIds)->get();

        $totalVersementsPaies = $versements->where('statut', 'payé')->sum('montant');
        $totalVersementsAttendus = $versements->sum('montant_attendu');
        $totalArrieres = $totalVersementsAttendus - $versements->sum('montant');

        // Paiements
        $paiementsValides = Payment::where('proprietaire_id', $proprietaire->id)
            ->whereIn('statut', ['paye', 'approuve'])
            ->sum('total_paye');

        $paiementsEnAttente = Payment::where('proprietaire_id', $proprietaire->id)
            ->where('statut', 'en_attente')
            ->sum('total_du');

        return [
            'total_motos' => $proprietaire->motos->count(),
            'motos_actives' => $proprietaire->motos->where('statut', 'actif')->count(),
            'total_versements_payes' => $totalVersementsPaies,
            'total_versements_attendus' => $totalVersementsAttendus,
            'total_arrieres_motards' => max(0, $totalArrieres),
            'total_paiements_recus' => $paiementsValides,
            'paiements_en_attente' => $paiementsEnAttente,
            'solde_disponible' => max(0, $totalVersementsPaies - $paiementsValides),
        ];
    }

    /**
     * Obtenir tous les propriétaires avec leur solde disponible
     * Pour le dashboard du Collecteur
     */
    public function getProprietairesAvecSolde(): \Illuminate\Support\Collection
    {
        return Proprietaire::with(['user', 'motos'])->get()->map(function ($proprietaire) {
            $stats = $this->getStatsProprietaire($proprietaire);
            $proprietaire->solde_disponible = $stats['solde_disponible'];
            $proprietaire->total_motos = $stats['total_motos'];
            $proprietaire->motos_actives = $stats['motos_actives'];
            return $proprietaire;
        });
    }

    /**
     * Vérifier si un montant peut être payé à un propriétaire
     */
    public function peutPayerMontant(Proprietaire $proprietaire, float $montant): bool
    {
        $soldeDisponible = $this->getSoldeDisponibleProprietaire($proprietaire);
        return $montant <= $soldeDisponible;
    }

    /**
     * Créer une demande de paiement (par OKAMI)
     */
    public function creerDemandePaiementOKAMI(array $data, int $okamUserId): Payment
    {
        $proprietaire = Proprietaire::findOrFail($data['proprietaire_id']);
        $montantDemande = $data['montant'];

        // Vérifier que le montant ne dépasse pas le solde disponible
        $soldeDisponible = $this->getSoldeDisponibleProprietaire($proprietaire);
        if ($montantDemande > $soldeDisponible) {
            throw new \Exception("Le montant demandé ({$montantDemande} FC) dépasse le solde disponible ({$soldeDisponible} FC) du propriétaire.");
        }

        return Payment::create([
            'proprietaire_id' => $proprietaire->id,
            'total_du' => $montantDemande,
            'total_paye' => 0,
            'mode_paiement' => $data['mode_paiement'],
            'numero_compte' => $proprietaire->getNumeroCompte($data['mode_paiement']) ?? $data['numero_compte'] ?? null,
            'statut' => 'en_attente',
            'date_demande' => now(),
            'demande_par' => $okamUserId,
            'demande_at' => now(),
            'periode_debut' => $data['periode_debut'] ?? null,
            'periode_fin' => $data['periode_fin'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Traiter un paiement (par Collecteur/Admin)
     */
    public function traiterPaiement(Payment $payment, array $data, int $collecteurUserId): Payment
    {
        $payment->update([
            'total_paye' => $data['montant_paye'],
            'numero_envoi' => $data['numero_envoi'],
            'reference_paiement' => $data['reference_paiement'] ?? null,
            'date_paiement' => now(),
            'statut' => 'paye',
            'traite_par' => $collecteurUserId,
            'notes' => $data['notes'] ?? $payment->notes,
        ]);

        return $payment;
    }

    /**
     * Valider un paiement (par OKAMI)
     */
    public function validerPaiement(Payment $payment, int $okamUserId, ?string $notes = null): Payment
    {
        $payment->update([
            'statut' => 'approuve',
            'valide_par' => $okamUserId,
            'valide_at' => now(),
            'notes_validation' => $notes,
        ]);

        return $payment;
    }

    /**
     * Rejeter un paiement (par OKAMI ou Collecteur)
     */
    public function rejeterPaiement(Payment $payment, int $userId, string $motif): Payment
    {
        $payment->update([
            'statut' => 'rejete',
            'notes' => $motif,
            'traite_par' => $userId,
        ]);

        return $payment;
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
        return $caissier->solde_actuel ?? 0;
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
                ->selectRaw('COALESCE(SUM(montant_attendu - montant), 0) as total')
                ->value('total') ?? 0,
            'paiements_en_attente' => Payment::whereIn('statut', ['demande', 'en_cours'])->count(),
            'paiements_a_valider' => Payment::where('statut', 'paye')->count(),
        ];
    }
}
