<?php

namespace App\Livewire\Cashier\Versements;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Versement;
use App\Models\Motard;
use App\Models\SystemSetting;
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

        // Le statut sera déterminé automatiquement dans le modèle Versement
        // via la méthode boot(), mais on le définit aussi ici pour la clarté
        $statut = 'non_effectue';
        if ($this->montant >= $montantAttendu) {
            $statut = 'paye';
        } elseif ($this->montant > 0) {
            $statut = 'partiel';
        }

        // Calcul des arriérés
        $arrieres = max(0, $montantAttendu - $this->montant);

        Versement::create([
            'motard_id' => $this->motard_id,
            'moto_id' => $moto?->id,
            'caissier_id' => $caissier->id,
            'montant' => $this->montant,
            'montant_attendu' => $montantAttendu,
            'arrieres' => $arrieres,
            'mode_paiement' => $this->mode_paiement,
            'statut' => $statut,
            'date_versement' => Carbon::today(),
            'validated_by_caissier_at' => Carbon::now(),
            'notes' => $this->notes,
        ]);

        // Mettre à jour le solde du caissier
        $caissier->increment('solde_actuel', $this->montant);

        // Message personnalisé selon le statut
        if ($statut === 'paye') {
            session()->flash('success', 'Versement complet enregistré avec succès.');
        } else {
            session()->flash('warning', 'Versement partiel enregistré. Arriérés: ' . number_format($arrieres) . ' FC');
        }

        return redirect()->route('cashier.versements.index');
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
