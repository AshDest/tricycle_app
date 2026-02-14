<?php

namespace App\Livewire\Admin\Motos;

use Livewire\Component;
use App\Models\Moto;
use App\Models\Proprietaire;
use App\Models\Motard;

class Create extends Component
{
    public $numero_matricule = '';
    public $plaque_immatriculation = '';
    public $numero_chassis = '';
    public $proprietaire_id = '';
    public $motard_id = '';
    public $montant_journalier_attendu = '';
    public $statut = 'actif';

    protected $rules = [
        'numero_matricule' => 'required|string|unique:motos,numero_matricule',
        'plaque_immatriculation' => 'required|string|unique:motos,plaque_immatriculation',
        'numero_chassis' => 'nullable|string',
        'proprietaire_id' => 'required|exists:proprietaires,id',
        'motard_id' => 'nullable|exists:motards,id',
        'montant_journalier_attendu' => 'required|numeric|min:0',
        'statut' => 'required|in:actif,suspendu,maintenance',
    ];

    public function save()
    {
        $this->validate();

        Moto::create([
            'numero_matricule' => $this->numero_matricule,
            'plaque_immatriculation' => $this->plaque_immatriculation,
            'numero_chassis' => $this->numero_chassis,
            'proprietaire_id' => $this->proprietaire_id,
            'motard_id' => $this->motard_id ?: null,
            'montant_journalier_attendu' => $this->montant_journalier_attendu,
            'statut' => $this->statut,
        ]);

        session()->flash('success', 'Moto ajoutee avec succes.');
        return redirect()->route('admin.motos.index');
    }

    public function render()
    {
        $proprietaires = Proprietaire::with('user')->get();
        $motards = Motard::with('user')->where('is_active', true)->get();

        return view('livewire.admin.motos.create', compact('proprietaires', 'motards'));
    }
}
