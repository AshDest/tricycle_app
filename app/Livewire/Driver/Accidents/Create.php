<?php

namespace App\Livewire\Driver\Accidents;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Accident;
use App\Models\Motard;

#[Layout('components.dashlite-layout')]
class Create extends Component
{
    public $moto_id = '';
    public $date_heure = '';
    public $lieu = '';
    public $description = '';
    public $temoignage_motard = '';
    public $temoin_nom = '';
    public $temoin_telephone = '';
    public $estimation_cout = '';
    public $gravite = '';

    protected $rules = [
        'moto_id' => 'required|exists:motos,id',
        'date_heure' => 'required|date_format:Y-m-d H:i',
        'lieu' => 'required|string|max:255',
        'description' => 'required|string',
        'temoignage_motard' => 'required|string',
        'temoin_nom' => 'nullable|string|max:255',
        'temoin_telephone' => 'nullable|string|max:20',
        'estimation_cout' => 'nullable|numeric|min:0',
        'gravite' => 'required|in:mineur,modere,grave',
    ];

    public function save()
    {
        $this->validate();

        $motard = auth()->user()->motard;

        Accident::create([
            'moto_id' => $this->moto_id,
            'motard_id' => $motard->id,
            'date_heure' => $this->date_heure,
            'lieu' => $this->lieu,
            'description' => $this->description,
            'temoignage_motard' => $this->temoignage_motard,
            'temoin_nom' => $this->temoin_nom,
            'temoin_telephone' => $this->temoin_telephone,
            'estimation_cout' => $this->estimation_cout ?? 0,
            'gravite' => $this->gravite,
            'statut' => 'en_attente',
        ]);

        session()->flash('success', 'Accident declare avec succes.');
        return redirect()->route('driver.historique');
    }

    public function render()
    {
        $motard = auth()->user()->motard;
        $motos = $motard ? $motard->motos()->get() : collect();
        return view('livewire.driver.accidents.create', compact('motos'));
    }
}
