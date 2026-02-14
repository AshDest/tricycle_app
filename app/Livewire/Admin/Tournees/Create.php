<?php

namespace App\Livewire\Admin\Tournees;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tournee;
use App\Models\Collecteur;

#[Layout('components.dashlite-layout')]
class Create extends Component
{
    public $collecteur_id = '';
    public $date = '';
    public $zone = '';
    public $statut = '';
    public $heure_debut_prevue = '';
    public $heure_fin_prevue = '';

    protected $rules = [
        'collecteur_id' => 'required|exists:collecteurs,id',
        'date' => 'required|date',
        'zone' => 'required|string|max:255',
        'statut' => 'required|string',
        'heure_debut_prevue' => 'nullable|date_format:H:i',
        'heure_fin_prevue' => 'nullable|date_format:H:i',
    ];

    public function save()
    {
        $this->validate();

        Tournee::create([
            'collecteur_id' => $this->collecteur_id,
            'date' => $this->date,
            'zone' => $this->zone,
            'statut' => $this->statut,
            'heure_debut_prevue' => $this->heure_debut_prevue,
            'heure_fin_prevue' => $this->heure_fin_prevue,
        ]);

        session()->flash('success', 'Tournee creee avec succes.');
        return redirect()->route('admin.tournees.index');
    }

    public function render()
    {
        $collecteurs = Collecteur::all();
        return view('livewire.admin.tournees.create', compact('collecteurs'));
    }
}
