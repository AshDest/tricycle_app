<?php

namespace App\Livewire\Supervisor\Maintenances;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use App\Models\Maintenance;
use App\Models\Moto;
use App\Models\Motard;
use App\Models\Accident;

#[Layout('components.dashlite-layout')]
class Create extends Component
{
    use WithFileUploads;

    public $moto_id = '';
    public $motard_id = '';
    public $accident_id = '';
    public $type = 'preventive';
    public $description = '';
    public $date_intervention = '';
    public $technicien_garage_nom = '';
    public $technicien_telephone = '';
    public $garage_adresse = '';
    public $prochain_entretien = '';
    public $cout_pieces = 0;
    public $cout_main_oeuvre = 0;
    public $qui_a_paye = 'proprietaire';
    public $statut = 'en_attente';
    public $notes = '';

    // Liste des accidents filtrés par moto
    public $accidentsDisponibles = [];

    protected function rules()
    {
        return [
            'moto_id' => 'required|exists:motos,id',
            'motard_id' => 'nullable|exists:motards,id',
            'accident_id' => 'nullable|exists:accidents,id',
            'type' => 'required|in:preventive,corrective,remplacement',
            'description' => 'required|string|max:1000',
            'date_intervention' => 'required|date',
            'technicien_garage_nom' => 'nullable|string|max:255',
            'technicien_telephone' => 'nullable|string|max:20',
            'garage_adresse' => 'nullable|string|max:255',
            'prochain_entretien' => 'nullable|date|after:date_intervention',
            'cout_pieces' => 'nullable|numeric|min:0',
            'cout_main_oeuvre' => 'nullable|numeric|min:0',
            'qui_a_paye' => 'required|in:motard,proprietaire,nth,okami',
            'statut' => 'required|in:en_attente,en_cours,termine',
            'notes' => 'nullable|string|max:500',
        ];
    }

    protected $messages = [
        'moto_id.required' => 'Veuillez sélectionner une moto.',
        'type.required' => 'Le type de maintenance est obligatoire.',
        'description.required' => 'La description est obligatoire.',
        'date_intervention.required' => 'La date d\'intervention est obligatoire.',
        'prochain_entretien.after' => 'Le prochain entretien doit être après la date d\'intervention.',
    ];

    public function updatedMotoId($value)
    {
        if ($value) {
            $moto = Moto::with('motard')->find($value);
            if ($moto && $moto->motard_id) {
                $this->motard_id = $moto->motard_id;
            }

            // Charger les accidents non réparés pour cette moto
            $this->accidentsDisponibles = Accident::where('moto_id', $value)
                ->whereNotIn('statut', ['cloture'])
                ->whereDoesntHave('maintenance')
                ->orderBy('date_heure', 'desc')
                ->get();
        } else {
            $this->accidentsDisponibles = [];
        }
        $this->accident_id = '';
    }

    public function updatedAccidentId($value)
    {
        if ($value) {
            $accident = Accident::find($value);
            if ($accident) {
                // Pré-remplir les champs basés sur l'accident
                $this->type = 'corrective';
                $this->description = "Réparation suite à l'accident du " . $accident->date_heure?->format('d/m/Y') . " - " . $accident->lieu . "\n\nDommages: " . ($accident->pieces_endommagees ?? 'Non spécifié');
                $this->cout_pieces = $accident->estimation_cout ?? 0;
                $this->qui_a_paye = $accident->prise_en_charge ?? 'proprietaire';
            }
        }
    }

    public function save()
    {
        $this->validate();

        $maintenance = Maintenance::create([
            'moto_id' => $this->moto_id,
            'motard_id' => $this->motard_id ?: null,
            'accident_id' => $this->accident_id ?: null,
            'type' => $this->type,
            'description' => $this->description,
            'date_intervention' => $this->date_intervention,
            'technicien_garage_nom' => $this->technicien_garage_nom ?: null,
            'technicien_telephone' => $this->technicien_telephone ?: null,
            'garage_adresse' => $this->garage_adresse ?: null,
            'prochain_entretien' => $this->prochain_entretien ?: null,
            'cout_pieces' => $this->cout_pieces ?: 0,
            'cout_main_oeuvre' => $this->cout_main_oeuvre ?: 0,
            'qui_a_paye' => $this->qui_a_paye,
            'statut' => $this->statut,
            'notes' => $this->notes ?: null,
        ]);

        // Si lié à un accident, mettre à jour le statut de l'accident
        if ($this->accident_id) {
            Accident::where('id', $this->accident_id)->update([
                'statut' => 'reparation_programmee',
                'reparation_programmee_at' => now(),
            ]);
        }

        session()->flash('success', 'Maintenance enregistrée avec succès.');
        return redirect()->route('supervisor.maintenances.index');
    }

    public function render()
    {
        $motos = Moto::with(['proprietaire.user', 'motard.user'])
            ->orderBy('plaque_immatriculation')
            ->get();

        $motards = Motard::with('user')
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        return view('livewire.supervisor.maintenances.create', [
            'motos' => $motos,
            'motards' => $motards,
            'types' => Maintenance::getTypes(),
            'payeurs' => Maintenance::getPayeurs(),
        ]);
    }
}

