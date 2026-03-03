<?php

namespace App\Livewire\Cleaner;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Lavage;
use App\Models\Moto;
use App\Models\SystemSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class EnregistrerLavage extends Component
{
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

    // Pour le reçu
    public $dernierLavageId = null;

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
            $rules['plaque_externe'] = 'required|string|max:20';
            $rules['proprietaire_externe'] = 'nullable|string|max:100';
            $rules['telephone_externe'] = 'nullable|string|max:20';
        } else {
            $rules['moto_id'] = 'required|exists:motos,id';
        }

        return $rules;
    }

    protected $messages = [
        'moto_id.required' => 'Veuillez sélectionner une moto du système.',
        'plaque_externe.required' => 'Veuillez saisir la plaque de la moto externe.',
        'type_lavage.required' => 'Veuillez sélectionner un type de lavage.',
        'prix_final.required' => 'Le prix est obligatoire.',
    ];

    public function mount()
    {
        // Charger les prix configurés
        $this->prixSimple = Lavage::getPrixLavage('simple');
        $this->prixComplet = Lavage::getPrixLavage('complet');
        $this->prixPremium = Lavage::getPrixLavage('premium');

        // Prix par défaut
        $this->prix_base = $this->prixSimple;
        $this->prix_final = $this->prixSimple;

        $this->updateRepartitionPreview();
    }

    public function updatedIsExterne($value)
    {
        // Reset les champs selon le type
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

    public function calculerPrixFinal()
    {
        $this->prix_final = max(0, $this->prix_base - ($this->remise ?? 0));
        $this->updateRepartitionPreview();
    }

    public function updateRepartitionPreview()
    {
        $prix = (float) $this->prix_final;

        if ($this->is_externe) {
            // Moto externe: 100% pour le laveur
            $this->partCleanerPreview = $prix;
            $this->partOkamiPreview = 0;
        } else {
            // Moto du système: 80% laveur, 20% OKAMI
            $this->partOkamiPreview = round($prix * (Lavage::PART_OKAMI_PERCENT / 100), 2);
            $this->partCleanerPreview = $prix - $this->partOkamiPreview;
        }
    }

    public function enregistrer()
    {
        $this->validate();

        $cleaner = auth()->user()->cleaner;

        if (!$cleaner) {
            session()->flash('error', 'Profil laveur non trouvé.');
            return;
        }

        // Créer le lavage
        $lavage = Lavage::create([
            'cleaner_id' => $cleaner->id,
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
            'statut_paiement' => 'payé',
            'date_lavage' => now(),
            'notes' => $this->notes,
        ]);

        // Mettre à jour le solde du laveur
        $cleaner->increment('solde_actuel', $lavage->part_cleaner);

        $this->dernierLavageId = $lavage->id;

        session()->flash('success', 'Lavage enregistré avec succès! N° ' . $lavage->numero_lavage);
        session()->flash('dernierLavageId', $lavage->id);

        return redirect()->route('cleaner.lavages.index');
    }

    public function telechargerRecu($lavageId)
    {
        $lavage = Lavage::with(['cleaner.user', 'moto.proprietaire.user'])->findOrFail($lavageId);

        $pdf = Pdf::loadView('pdf.recu-lavage', compact('lavage'));
        $pdf->setPaper([0, 0, 226.77, 400], 'portrait');

        $filename = 'recu_lavage_' . $lavage->numero_lavage . '.pdf';

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    public function render()
    {
        // Motos actives du système
        $motos = Moto::where('statut', 'actif')
            ->with('proprietaire.user')
            ->orderBy('plaque_immatriculation')
            ->get();

        return view('livewire.cleaner.enregistrer-lavage', [
            'motos' => $motos,
        ]);
    }
}

