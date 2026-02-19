<?php

namespace App\Livewire\Admin\Maintenances;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Moto;
use App\Models\Maintenance;

#[Layout('components.dashlite-layout')]
class Create extends Component
{
    public $moto_id = '';
    public $motard_id = '';
    public $type = '';
    public $description = '';
    public $date_intervention = '';
    public $technicien_garage_nom = '';
    public $technicien_telephone = '';
    public $garage_adresse = '';
    public $cout_pieces = '';
    public $cout_main_oeuvre = '';
    public $qui_a_paye = '';
    public $prochain_entretien = '';
    public $statut = 'en_attente';

    protected $rules = [
        'moto_id' => 'required|exists:motos,id',
        'type' => 'required|in:preventive,corrective,remplacement',
        'description' => 'required|string',
        'date_intervention' => 'required|date',
        'technicien_garage_nom' => 'required|string|max:255',
        'technicien_telephone' => 'required|string|max:20',
        'garage_adresse' => 'required|string',
        'cout_pieces' => 'nullable|numeric|min:0',
        'cout_main_oeuvre' => 'nullable|numeric|min:0',
        'qui_a_paye' => 'required|in:motard,proprietaire,nth,okami',
        'prochain_entretien' => 'nullable|date|after:date_intervention',
        'statut' => 'required|in:en_attente,en_cours,termine',
    ];

    public function mount()
    {
        // Récupérer le moto_id depuis l'URL si fourni
        if (request()->has('moto_id')) {
            $this->moto_id = request()->get('moto_id');
        }

        $this->date_intervention = now()->format('Y-m-d');
    }

    public function save()
    {
        $this->validate();

        Maintenance::create([
            'moto_id' => $this->moto_id,
            'type' => $this->type,
            'description' => $this->description,
            'date_intervention' => $this->date_intervention,
            'technicien_garage_nom' => $this->technicien_garage_nom,
            'technicien_telephone' => $this->technicien_telephone,
            'garage_adresse' => $this->garage_adresse,
            'cout_pieces' => $this->cout_pieces ?? 0,
            'cout_main_oeuvre' => $this->cout_main_oeuvre ?? 0,
            'qui_a_paye' => $this->qui_a_paye,
            'prochain_entretien' => $this->prochain_entretien ?: null,
            'statut' => $this->statut,
        ]);

        session()->flash('success', 'Maintenance créée avec succès.');
        return redirect()->route('admin.maintenances.index');
    }

    public function render()
    {
        $motos = Moto::with('proprietaire.user')->orderBy('plaque_immatriculation')->get();
        return view('livewire.admin.maintenances.create', compact('motos'));
    }
}
