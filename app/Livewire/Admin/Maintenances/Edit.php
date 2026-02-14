<?php

namespace App\Livewire\Admin\Maintenances;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Maintenance;
use App\Models\Moto;

#[Layout('components.dashlite-layout')]
class Edit extends Component
{
    public $maintenance;
    public $moto_id = '';
    public $type = '';
    public $description = '';
    public $date_intervention = '';
    public $technicien_garage_nom = '';
    public $technicien_telephone = '';
    public $garage_adresse = '';
    public $cout_pieces = '';
    public $cout_main_oeuvre = '';
    public $qui_a_paye = '';
    public $statut = '';

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
        'statut' => 'required|string',
    ];

    public function mount(Maintenance $maintenance)
    {
        $this->maintenance = $maintenance;
        $this->moto_id = $maintenance->moto_id;
        $this->type = $maintenance->type;
        $this->description = $maintenance->description;
        $this->date_intervention = $maintenance->date_intervention;
        $this->technicien_garage_nom = $maintenance->technicien_garage_nom;
        $this->technicien_telephone = $maintenance->technicien_telephone;
        $this->garage_adresse = $maintenance->garage_adresse;
        $this->cout_pieces = $maintenance->cout_pieces;
        $this->cout_main_oeuvre = $maintenance->cout_main_oeuvre;
        $this->qui_a_paye = $maintenance->qui_a_paye;
        $this->statut = $maintenance->statut;
    }

    public function save()
    {
        $this->validate();

        $this->maintenance->update([
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
            'statut' => $this->statut,
        ]);

        session()->flash('success', 'Maintenance mise a jour avec succes.');
        return redirect()->route('admin.maintenances.index');
    }

    public function render()
    {
        $motos = Moto::all();
        return view('livewire.admin.maintenances.edit', compact('motos'));
    }
}
