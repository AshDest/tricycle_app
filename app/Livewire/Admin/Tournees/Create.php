<?php

namespace App\Livewire\Admin\Tournees;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tournee;
use App\Models\Collecteur;
use App\Models\Caissier;
use App\Models\Zone;
use App\Models\Collecte;

#[Layout('components.dashlite-layout')]
class Create extends Component
{
    public $collecteur_id = '';
    public $date = '';
    public $zone_id = '';
    public $heure_debut_prevue = '';
    public $heure_fin_prevue = '';
    public $caissiers_selectionnes = [];

    protected $rules = [
        'collecteur_id' => 'required|exists:collecteurs,id',
        'date' => 'required|date|after_or_equal:today',
        'zone_id' => 'required|exists:zones,id',
        'heure_debut_prevue' => 'nullable|date_format:H:i',
        'heure_fin_prevue' => 'nullable|date_format:H:i',
        'caissiers_selectionnes' => 'required|array|min:1',
    ];

    protected $messages = [
        'collecteur_id.required' => 'Veuillez sélectionner un collecteur.',
        'date.required' => 'La date est obligatoire.',
        'date.after_or_equal' => 'La date doit être aujourd\'hui ou dans le futur.',
        'zone_id.required' => 'Veuillez sélectionner une zone.',
        'caissiers_selectionnes.required' => 'Veuillez sélectionner au moins un caissier.',
        'caissiers_selectionnes.min' => 'Veuillez sélectionner au moins un caissier.',
    ];

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
    }

    /**
     * Quand la zone change, réinitialiser les caissiers sélectionnés
     */
    public function updatedZoneId()
    {
        $this->caissiers_selectionnes = [];
    }

    /**
     * Sélectionner tous les caissiers de la zone
     */
    public function selectAllCaissiers()
    {
        if ($this->zone_id) {
            $zone = Zone::find($this->zone_id);
            $caissiers = Caissier::where('zone', $zone->nom)
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();
            $this->caissiers_selectionnes = $caissiers;
        }
    }

    /**
     * Désélectionner tous les caissiers
     */
    public function deselectAllCaissiers()
    {
        $this->caissiers_selectionnes = [];
    }

    public function save()
    {
        $this->validate();

        $zone = Zone::find($this->zone_id);

        // Créer la tournée
        $tournee = Tournee::create([
            'collecteur_id' => $this->collecteur_id,
            'date' => $this->date,
            'zone' => $zone->nom,
            'statut' => 'planifiee',
            'heure_debut_prevue' => $this->heure_debut_prevue ?: null,
            'heure_fin_prevue' => $this->heure_fin_prevue ?: null,
        ]);

        // Créer les collectes pour chaque caissier sélectionné
        foreach ($this->caissiers_selectionnes as $caissier_id) {
            $caissier = Caissier::find($caissier_id);

            Collecte::create([
                'tournee_id' => $tournee->id,
                'caissier_id' => $caissier_id,
                'montant_attendu' => $caissier->solde_actuel ?? 0,
                'montant_collecte' => 0,
                'ecart' => 0,
                'statut' => 'en_attente',
                'valide_par_collecteur' => false,
            ]);
        }

        session()->flash('success', 'Tournée créée avec succès avec ' . count($this->caissiers_selectionnes) . ' caissier(s).');
        return redirect()->route('admin.tournees.index');
    }

    public function render()
    {
        $collecteurs = Collecteur::with('user')->where('is_active', true)->get();
        $zones = Zone::where('is_active', true)->orderBy('nom')->get();

        // Caissiers de la zone sélectionnée
        $caissiers = collect();
        if ($this->zone_id) {
            $zone = Zone::find($this->zone_id);
            $caissiers = Caissier::with('user')
                ->where('zone', $zone->nom)
                ->where('is_active', true)
                ->get();
        }

        return view('livewire.admin.tournees.create', compact('collecteurs', 'zones', 'caissiers'));
    }
}
