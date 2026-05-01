<?php

namespace App\Livewire\Cleaner;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Lavage;
use App\Models\Moto;

#[Layout('components.dashlite-layout')]
class EditLavage extends Component
{
    public Lavage $lavage;
    public bool $isAdminContext = false;
    public string $retourRoute = 'cleaner.lavages.index';
    public int $editWindowMinutes = 20;

    // Type de moto
    public $is_externe = false;

    // Pour moto interne
    public $moto_id = '';
    public $motoSelectionnee = null;

    // Pour moto externe
    public $plaque_externe = '';
    public $proprietaire_externe = '';
    public $telephone_externe = '';

    // Détails du lavage
    public $type_lavage = 'simple';
    public $prix_base = 0;
    public $remise = 0;
    public $prix_final = 0;
    public $mode_paiement = 'cash';
    public $notes = '';

    // Prévisualisation répartition
    public $partCleanerPreview = 0;
    public $partOkamiPreview = 0;

    // Prix configurés
    public $prixSimple = 2000;
    public $prixComplet = 3500;
    public $prixPremium = 5000;

    protected function rules()
    {
        $rules = [
            'type_lavage' => 'required|in:simple,complet,premium',
            'prix_final' => 'required|numeric|min:0',
            'mode_paiement' => 'required|in:cash,mobile_money',
            'notes' => 'nullable|string|max:500',
        ];

        if ($this->is_externe) {
            $rules['plaque_externe'] = 'nullable|string|max:20';
            $rules['proprietaire_externe'] = 'nullable|string|max:100';
            $rules['telephone_externe'] = 'nullable|string|max:20';
        } else {
            $rules['moto_id'] = 'required|exists:motos,id';
        }

        return $rules;
    }

    public function mount(Lavage $lavage)
    {
        $this->lavage = $lavage;
        $user = auth()->user();
        $this->isAdminContext = (bool) ($user && $user->hasRole('admin'));
        $this->retourRoute = $this->isAdminContext ? 'admin.lavages.index' : 'cleaner.lavages.index';

        if ($this->isAdminContext) {
            if (!$lavage->canBeEditedWithinMinutes($this->editWindowMinutes)) {
                session()->flash('error', 'Modification impossible: ce lavage a dépassé le délai de 20 minutes.');
                return redirect()->route($this->retourRoute);
            }
        } else {
            // Vérifier que le lavage appartient au cleaner connecté
            $cleaner = $user?->cleaner;
            if (!$cleaner || $lavage->cleaner_id !== $cleaner->id) {
                session()->flash('error', 'Vous n\'êtes pas autorisé à modifier ce lavage.');
                return redirect()->route($this->retourRoute);
            }

            // Côté cleaner, un lavage payé ne peut pas être modifié
            if ($lavage->statut_paiement === 'payé') {
                session()->flash('error', 'Ce lavage a déjà été payé et ne peut plus être modifié.');
                return redirect()->route($this->retourRoute);
            }
        }

        // Un lavage annulé ne doit plus être modifié
        if ($lavage->statut_paiement === 'annulé') {
            session()->flash('error', 'Ce lavage est annulé et ne peut pas être modifié.');
            return redirect()->route($this->retourRoute);
        }

        // Charger les prix configurés
        $this->prixSimple = Lavage::getPrixLavage('simple');
        $this->prixComplet = Lavage::getPrixLavage('complet');
        $this->prixPremium = Lavage::getPrixLavage('premium');

        // Remplir les champs avec les données existantes
        $this->is_externe = $lavage->is_externe;
        $this->moto_id = $lavage->moto_id;
        $this->plaque_externe = $lavage->plaque_externe;
        $this->proprietaire_externe = $lavage->proprietaire_externe;
        $this->telephone_externe = $lavage->telephone_externe;
        $this->type_lavage = $lavage->type_lavage;
        $this->prix_base = $lavage->prix_base;
        $this->remise = $lavage->remise;
        $this->prix_final = $lavage->prix_final;
        $this->mode_paiement = $lavage->mode_paiement;
        $this->notes = $lavage->notes;

        if ($this->moto_id) {
            $this->motoSelectionnee = Moto::with('proprietaire.user')->find($this->moto_id);
        }

        $this->updateRepartitionPreview();
    }

    public function updatedIsExterne($value)
    {
        if ($value) {
            $this->moto_id = '';
            $this->motoSelectionnee = null;
        } else {
            $this->plaque_externe = '';
            $this->proprietaire_externe = '';
            $this->telephone_externe = '';
        }
        $this->updateRepartitionPreview();
    }

    public function updatedMotoId($value)
    {
        if ($value) {
            $this->motoSelectionnee = Moto::with('proprietaire.user')->find($value);
        } else {
            $this->motoSelectionnee = null;
        }
        $this->updateRepartitionPreview();
    }

    public function updatedTypeLavage($value)
    {
        $this->prix_base = Lavage::getPrixLavage($value);
        $this->calculerPrixFinal();
    }

    public function updatedRemise($value)
    {
        $this->calculerPrixFinal();
    }

    public function updatedPrixFinal($value)
    {
        // Permettre la saisie libre du prix final
        $this->prix_final = max(0, (float) $value);
        $this->updateRepartitionPreview();
    }

    public function calculerPrixFinal()
    {
        $this->prix_final = max(0, $this->prix_base - ($this->remise ?? 0));
        $this->updateRepartitionPreview();
    }

    public function updateRepartitionPreview()
    {
        $prix = (float) $this->prix_final;

        if ($this->is_externe) {
            $this->partCleanerPreview = $prix;
            $this->partOkamiPreview = 0;
        } else {
            $this->partOkamiPreview = round($prix * (Lavage::PART_OKAMI_PERCENT / 100), 2);
            $this->partCleanerPreview = $prix - $this->partOkamiPreview;
        }
    }

    public function save()
    {
        // Contrôle serveur: délai strict de 20 minutes pour l'admin
        if ($this->isAdminContext && !$this->lavage->fresh()->canBeEditedWithinMinutes($this->editWindowMinutes)) {
            session()->flash('error', 'Modification impossible: le délai de 20 minutes est dépassé.');
            return redirect()->route($this->retourRoute);
        }

        // Côté cleaner, empêcher la modification d'un lavage payé
        if (!$this->isAdminContext && $this->lavage->fresh()->statut_paiement === 'payé') {
            session()->flash('error', 'Ce lavage a déjà été payé et ne peut plus être modifié.');
            return redirect()->route($this->retourRoute);
        }

        if ($this->lavage->fresh()->statut_paiement === 'annulé') {
            session()->flash('error', 'Ce lavage est annulé et ne peut pas être modifié.');
            return redirect()->route($this->retourRoute);
        }

        $this->validate();

        $cleaner = $this->lavage->cleaner;

        // Calculer l'ancienne part du cleaner pour ajuster le solde
        $anciennePartCleaner = $this->lavage->part_cleaner;

        // Mettre à jour le lavage
        $this->lavage->update([
            'moto_id' => $this->is_externe ? null : $this->moto_id,
            'is_externe' => $this->is_externe,
            'plaque_externe' => $this->is_externe ? $this->plaque_externe : null,
            'proprietaire_externe' => $this->is_externe ? $this->proprietaire_externe : null,
            'telephone_externe' => $this->is_externe ? $this->telephone_externe : null,
            'type_lavage' => $this->type_lavage,
            'prix_base' => $this->prix_base,
            'prix_final' => $this->prix_final,
            'remise' => $this->remise ?? 0,
            'mode_paiement' => $this->mode_paiement,
            'notes' => $this->notes,
        ]);

        // Ajuster le solde du laveur
        $nouvellePartCleaner = $this->lavage->fresh()->part_cleaner;
        $difference = $nouvellePartCleaner - $anciennePartCleaner;
        if ($cleaner && $difference != 0) {
            $cleaner->increment('solde_actuel', $difference);
        }

        session()->flash('success', 'Lavage modifié avec succès!');
        return redirect()->route($this->retourRoute);
    }

    public function annuler()
    {
        if ($this->isAdminContext) {
            session()->flash('error', 'Annulation non autorisée depuis cette interface.');
            return redirect()->route($this->retourRoute);
        }

        $cleaner = $this->lavage->cleaner;

        // Rembourser le solde du cleaner
        if ($cleaner) {
            $cleaner->decrement('solde_actuel', $this->lavage->part_cleaner);
        }

        // Supprimer le lavage
        $this->lavage->update(['statut_paiement' => 'annulé']);

        session()->flash('success', 'Lavage annulé avec succès!');
        return redirect()->route($this->retourRoute);
    }

    public function render()
    {
        $motos = Moto::where('statut', 'actif')
            ->with('proprietaire.user')
            ->orderBy('plaque_immatriculation')
            ->get();

        return view('livewire.cleaner.edit-lavage', [
            'motos' => $motos,
        ]);
    }
}

