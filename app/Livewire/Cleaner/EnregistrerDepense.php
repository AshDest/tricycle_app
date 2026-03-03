<?php

namespace App\Livewire\Cleaner;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\DepenseLavage;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class EnregistrerDepense extends Component
{
    public $categorie = 'produits';
    public $description = '';
    public $montant = '';
    public $mode_paiement = 'cash';
    public $reference_paiement = '';
    public $fournisseur = '';
    public $date_depense = '';
    public $notes = '';

    public $soldeActuel = 0;

    protected function rules()
    {
        return [
            'categorie' => 'required|in:' . implode(',', array_keys(DepenseLavage::CATEGORIES)),
            'description' => 'required|string|max:255',
            'montant' => 'required|numeric|min:1',
            'mode_paiement' => 'required|in:cash,mobile_money',
            'reference_paiement' => 'nullable|string|max:100',
            'fournisseur' => 'nullable|string|max:100',
            'date_depense' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:500',
        ];
    }

    protected $messages = [
        'categorie.required' => 'Veuillez sélectionner une catégorie.',
        'description.required' => 'La description est obligatoire.',
        'montant.required' => 'Le montant est obligatoire.',
        'montant.min' => 'Le montant doit être supérieur à 0.',
        'date_depense.required' => 'La date est obligatoire.',
        'date_depense.before_or_equal' => 'La date ne peut pas être dans le futur.',
    ];

    public function mount()
    {
        $this->date_depense = now()->format('Y-m-d');
        $cleaner = auth()->user()->cleaner;
        $this->soldeActuel = $cleaner->solde_actuel ?? 0;
    }

    public function updatedMontant($value)
    {
        // Vérifier si le montant dépasse le solde
        $cleaner = auth()->user()->cleaner;
        $this->soldeActuel = $cleaner->solde_actuel ?? 0;
    }

    public function enregistrer()
    {
        $this->validate();

        $cleaner = auth()->user()->cleaner;

        if (!$cleaner) {
            session()->flash('error', 'Profil laveur non trouvé.');
            return;
        }

        $montant = (float) $this->montant;

        // Vérifier si le solde est suffisant
        if ($montant > $cleaner->solde_actuel) {
            session()->flash('error', 'Solde insuffisant! Votre solde actuel est de ' . number_format($cleaner->solde_actuel) . ' FC.');
            return;
        }

        // Créer la dépense
        $depense = DepenseLavage::create([
            'cleaner_id' => $cleaner->id,
            'categorie' => $this->categorie,
            'description' => $this->description,
            'montant' => $montant,
            'mode_paiement' => $this->mode_paiement,
            'reference_paiement' => $this->reference_paiement,
            'fournisseur' => $this->fournisseur,
            'date_depense' => $this->date_depense,
            'notes' => $this->notes,
        ]);

        session()->flash('success', 'Dépense enregistrée avec succès! N° ' . $depense->numero_depense . '. Nouveau solde: ' . number_format($cleaner->fresh()->solde_actuel) . ' FC');

        return redirect()->route('cleaner.depenses.index');
    }

    public function render()
    {
        return view('livewire.cleaner.enregistrer-depense', [
            'categories' => DepenseLavage::CATEGORIES,
        ]);
    }
}

