<?php

namespace App\Livewire\Cashier\Versements;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Versement;
use App\Models\Motard;
use App\Models\SystemSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Create extends Component
{
    public $motard_id = '';
    public $montant = '';
    public $mode_paiement = 'cash';
    public $notes = '';

    public $motardSelectionne = null;
    public $montantAttendu = 0;
    public $soldeActuel = 0;
    public $arrieresCumules = 0;
    public $tauxPaiement = 0;

    // Pour le téléchargement du reçu
    public $dernierVersementId = null;

    protected $rules = [
        'motard_id' => 'required|exists:motards,id',
        'montant' => 'required|numeric|min:1',
        'mode_paiement' => 'required|in:cash,mobile_money,depot',
        'notes' => 'nullable|string',
    ];

    protected $messages = [
        'motard_id.required' => 'Veuillez sélectionner un motard.',
        'montant.required' => 'Le montant est obligatoire.',
        'montant.min' => 'Le montant doit être supérieur à 0.',
        'mode_paiement.required' => 'Veuillez choisir un mode de paiement.',
    ];

    public function mount()
    {
        $caissier = auth()->user()->caissier;
        $this->soldeActuel = $caissier->solde_actuel ?? 0;
    }

    public function updatedMotardId($value)
    {
        if ($value) {
            $this->motardSelectionne = Motard::with(['user', 'moto'])->find($value);
            $this->montantAttendu = $this->motardSelectionne?->moto?->montant_journalier_attendu ?? SystemSetting::getMontantJournalierDefaut();
            $this->arrieresCumules = $this->motardSelectionne?->getTotalArrieres() ?? 0;
            $this->tauxPaiement = $this->motardSelectionne?->taux_paiement ?? 100;
        } else {
            $this->motardSelectionne = null;
            $this->montantAttendu = 0;
            $this->arrieresCumules = 0;
            $this->tauxPaiement = 0;
        }
    }

    public function enregistrer()
    {
        $this->validate();

        $caissier = auth()->user()->caissier;
        $motard = Motard::with('moto')->find($this->motard_id);
        $moto = $motard->moto;

        // Montant attendu depuis la moto ou paramètre système
        $montantAttendu = $moto?->montant_journalier_attendu ?? SystemSetting::getMontantJournalierDefaut();
        $montantVerse = (float) $this->montant;

        // Récupérer les arriérés cumulés du motard
        $arrieresCumules = $motard->getTotalArrieres();

        // Déterminer le statut et les arriérés
        $excedent = 0;
        $arrieresDuJour = 0;
        $remboursementArrieres = 0;
        $notesSupplementaires = '';

        if ($montantVerse >= $montantAttendu) {
            // Le montant couvre au moins le versement du jour
            $statut = 'payé';
            $arrieresDuJour = 0;

            // Vérifier s'il y a un excédent pour rembourser les arriérés
            $excedent = $montantVerse - $montantAttendu;

            if ($excedent > 0 && $arrieresCumules > 0) {
                // L'excédent va rembourser les arriérés anciens
                $remboursementArrieres = min($excedent, $arrieresCumules);
                $notesSupplementaires = "Excédent de " . number_format($excedent) . " FC utilisé pour rembourser " . number_format($remboursementArrieres) . " FC d'arriérés.";

                // Mettre à jour les anciens versements partiels pour réduire leurs arriérés
                $this->rembourserArrieres($motard, $remboursementArrieres);
            }
        } elseif ($montantVerse > 0) {
            // Versement partiel
            $statut = 'partiellement_payé';
            $arrieresDuJour = $montantAttendu - $montantVerse;
            $notesSupplementaires = "Montant insuffisant. Arriéré du jour: " . number_format($arrieresDuJour) . " FC";
        } else {
            $statut = 'non_effectué';
            $arrieresDuJour = $montantAttendu;
        }

        // Créer le versement
        $versement = Versement::create([
            'motard_id' => $this->motard_id,
            'moto_id' => $moto?->id,
            'caissier_id' => $caissier->id,
            'montant' => $montantVerse,
            'montant_attendu' => $montantAttendu,
            'arrieres' => $arrieresDuJour,
            'mode_paiement' => $this->mode_paiement,
            'statut' => $statut,
            'date_versement' => Carbon::today(),
            'validated_by_caissier_at' => Carbon::now(),
            'notes' => trim(($this->notes ? $this->notes . "\n" : '') . $notesSupplementaires),
        ]);

        // Mettre à jour le solde du caissier
        $caissier->increment('solde_actuel', $montantVerse);

        // Message de succès avec l'ID du versement pour télécharger le reçu
        session()->flash('success', 'Versement de ' . number_format($montantVerse) . ' FC enregistré avec succès.');
        session()->flash('dernierVersementId', $versement->id);

        // Rediriger vers la liste des versements
        return redirect()->route('cashier.versements.index');
    }

    /**
     * Rembourser les arriérés des anciens versements
     */
    protected function rembourserArrieres(Motard $motard, float $montantRemboursement)
    {
        if ($montantRemboursement <= 0) return;

        // Récupérer les versements avec arriérés, du plus ancien au plus récent
        $versementsAvecArrieres = Versement::where('motard_id', $motard->id)
            ->where('arrieres', '>', 0)
            ->orderBy('date_versement', 'asc')
            ->get();

        $restant = $montantRemboursement;

        foreach ($versementsAvecArrieres as $versement) {
            if ($restant <= 0) break;

            $arriereActuel = $versement->arrieres;
            $remboursement = min($restant, $arriereActuel);

            // Mettre à jour le versement
            $nouveauMontant = $versement->montant + $remboursement;
            $nouveauxArrieres = $arriereActuel - $remboursement;

            $nouveauStatut = $nouveauxArrieres <= 0 ? 'payé' : 'partiellement_payé';

            $versement->update([
                'montant' => $nouveauMontant,
                'arrieres' => max(0, $nouveauxArrieres),
                'statut' => $nouveauStatut,
                'notes' => ($versement->notes ? $versement->notes . "\n" : '') .
                           "[Remboursement de " . number_format($remboursement) . " FC le " . now()->format('d/m/Y') . "]",
            ]);

            $restant -= $remboursement;
        }
    }

    public function telechargerRecu($versementId)
    {
        $versement = Versement::with(['motard.user', 'moto', 'caissier.user'])->findOrFail($versementId);

        $pdf = Pdf::loadView('pdf.recu-versement', compact('versement'));

        // Dimensions d'un petit reçu (80mm x 200mm environ)
        $pdf->setPaper([0, 0, 226.77, 566.93], 'portrait'); // 80mm x 200mm en points

        $filename = 'recu_versement_' . $versement->id . '_' . now()->format('YmdHis') . '.pdf';

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    public function render()
    {
        $motards = Motard::with(['user', 'moto'])
            ->where('is_active', true)
            ->whereHas('moto')
            ->get();

        return view('livewire.cashier.versements.create', compact('motards'));
    }
}
