<?php

namespace App\Livewire\Admin\Settings;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\SystemSetting;
use App\Models\Moto;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    // Paramètres généraux
    public $nom_societe = '';
    public $devise = '';

    // Paramètres versements
    public $montant_journalier_defaut = 5000;
    public $seuil_arriere_faible = 25000;
    public $seuil_arriere_moyen = 50000;
    public $seuil_arriere_critique = 100000;

    // Gestion des tarifs motos
    public $searchMoto = '';
    public $editingMotoId = null;
    public $editingMotoTarif = 0;

    // Pour modification en masse
    public $nouveauTarifMasse = 0;
    public $selectedMotos = [];
    public $selectAll = false;

    protected $rules = [
        'nom_societe' => 'required|string|max:255',
        'devise' => 'required|string|max:10',
        'montant_journalier_defaut' => 'required|numeric|min:0',
        'seuil_arriere_faible' => 'required|numeric|min:0',
        'seuil_arriere_moyen' => 'required|numeric|min:0',
        'seuil_arriere_critique' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        $this->loadSettings();
    }

    protected function loadSettings()
    {
        $this->nom_societe = SystemSetting::get('nom_societe', 'New Technology Hub Sarl');
        $this->devise = SystemSetting::get('devise', 'FC');
        $this->montant_journalier_defaut = SystemSetting::get('montant_journalier_defaut', 5000);
        $this->seuil_arriere_faible = SystemSetting::get('seuil_arriere_faible', 25000);
        $this->seuil_arriere_moyen = SystemSetting::get('seuil_arriere_moyen', 50000);
        $this->seuil_arriere_critique = SystemSetting::get('seuil_arriere_critique', 100000);
    }

    public function saveGeneralSettings()
    {
        $this->validate([
            'nom_societe' => 'required|string|max:255',
            'devise' => 'required|string|max:10',
        ]);

        SystemSetting::set('nom_societe', $this->nom_societe, 'string', 'general');
        SystemSetting::set('devise', $this->devise, 'string', 'general');

        session()->flash('success', 'Paramètres généraux mis à jour avec succès.');
    }

    public function saveVersementSettings()
    {
        $this->validate([
            'montant_journalier_defaut' => 'required|numeric|min:0',
            'seuil_arriere_faible' => 'required|numeric|min:0',
            'seuil_arriere_moyen' => 'required|numeric|min:0',
            'seuil_arriere_critique' => 'required|numeric|min:0',
        ]);

        SystemSetting::set('montant_journalier_defaut', $this->montant_journalier_defaut, 'decimal', 'versements');
        SystemSetting::set('seuil_arriere_faible', $this->seuil_arriere_faible, 'decimal', 'versements');
        SystemSetting::set('seuil_arriere_moyen', $this->seuil_arriere_moyen, 'decimal', 'versements');
        SystemSetting::set('seuil_arriere_critique', $this->seuil_arriere_critique, 'decimal', 'versements');

        session()->flash('success', 'Paramètres des versements mis à jour avec succès.');
    }

    // ========== Gestion des tarifs motos ==========

    public function editMotoTarif(int $motoId)
    {
        $moto = Moto::find($motoId);
        if ($moto) {
            $this->editingMotoId = $motoId;
            $this->editingMotoTarif = $moto->montant_journalier_attendu ?? $this->montant_journalier_defaut;
        }
    }

    public function saveMotoTarif()
    {
        $this->validate([
            'editingMotoTarif' => 'required|numeric|min:0',
        ]);

        $moto = Moto::find($this->editingMotoId);
        if ($moto) {
            $moto->update(['montant_journalier_attendu' => $this->editingMotoTarif]);
            session()->flash('success', "Tarif de la moto {$moto->plaque_immatriculation} mis à jour: " . number_format($this->editingMotoTarif) . " FC");
        }

        $this->cancelEditMoto();
    }

    public function cancelEditMoto()
    {
        $this->editingMotoId = null;
        $this->editingMotoTarif = 0;
    }

    public function appliquerTarifDefaut(int $motoId)
    {
        $moto = Moto::find($motoId);
        if ($moto) {
            $moto->update(['montant_journalier_attendu' => $this->montant_journalier_defaut]);
            session()->flash('success', "Tarif par défaut appliqué à {$moto->plaque_immatriculation}");
        }
    }

    // ========== Modification en masse ==========

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedMotos = Moto::when($this->searchMoto, function ($q) {
                $q->where('plaque_immatriculation', 'like', '%' . $this->searchMoto . '%')
                  ->orWhereHas('proprietaire.user', function ($q2) {
                      $q2->where('name', 'like', '%' . $this->searchMoto . '%');
                  });
            })->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedMotos = [];
        }
    }

    public function appliquerTarifMasse()
    {
        $this->validate([
            'nouveauTarifMasse' => 'required|numeric|min:0',
        ]);

        if (empty($this->selectedMotos)) {
            session()->flash('error', 'Veuillez sélectionner au moins une moto.');
            return;
        }

        $count = Moto::whereIn('id', $this->selectedMotos)
            ->update(['montant_journalier_attendu' => $this->nouveauTarifMasse]);

        $this->selectedMotos = [];
        $this->selectAll = false;
        $this->nouveauTarifMasse = 0;

        session()->flash('success', "Tarif mis à jour pour {$count} moto(s).");
    }

    public function appliquerTarifDefautATous()
    {
        if (empty($this->selectedMotos)) {
            session()->flash('error', 'Veuillez sélectionner au moins une moto.');
            return;
        }

        $count = Moto::whereIn('id', $this->selectedMotos)
            ->update(['montant_journalier_attendu' => $this->montant_journalier_defaut]);

        $this->selectedMotos = [];
        $this->selectAll = false;

        session()->flash('success', "Tarif par défaut ({$this->montant_journalier_defaut} FC) appliqué à {$count} moto(s).");
    }

    public function render()
    {
        $motos = Moto::with(['proprietaire.user', 'motard.user'])
            ->when($this->searchMoto, function ($q) {
                $q->where('plaque_immatriculation', 'like', '%' . $this->searchMoto . '%')
                  ->orWhereHas('proprietaire.user', function ($q2) {
                      $q2->where('name', 'like', '%' . $this->searchMoto . '%');
                  });
            })
            ->orderBy('plaque_immatriculation')
            ->paginate(15);

        $statsMotos = [
            'total' => Moto::count(),
            'avecTarif' => Moto::whereNotNull('montant_journalier_attendu')->where('montant_journalier_attendu', '>', 0)->count(),
            'sansTarif' => Moto::where(function($q) {
                $q->whereNull('montant_journalier_attendu')->orWhere('montant_journalier_attendu', 0);
            })->count(),
            'tarifMoyen' => Moto::whereNotNull('montant_journalier_attendu')->avg('montant_journalier_attendu') ?? 0,
        ];

        return view('livewire.admin.settings.index', compact('motos', 'statsMotos'));
    }
}
