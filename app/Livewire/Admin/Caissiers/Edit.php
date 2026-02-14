<?php

namespace App\Livewire\Admin\Caissiers;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Caissier;

#[Layout('components.dashlite-layout')]
class Edit extends Component
{
    public $caissier;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $numero_identifiant = '';
    public $nom_point_collecte = '';
    public $zone = '';
    public $adresse = '';
    public $telephone = '';
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'phone' => 'nullable|string|max:20',
        'numero_identifiant' => 'required|string',
        'nom_point_collecte' => 'required|string|max:255',
        'zone' => 'required|string|max:255',
        'adresse' => 'required|string|max:255',
        'telephone' => 'required|string|max:20',
    ];

    public function mount(Caissier $caissier)
    {
        $this->caissier = $caissier;
        $this->name = $caissier->user->name;
        $this->email = $caissier->user->email;
        $this->phone = $caissier->user->phone;
        $this->numero_identifiant = $caissier->numero_identifiant;
        $this->nom_point_collecte = $caissier->nom_point_collecte;
        $this->zone = $caissier->zone;
        $this->adresse = $caissier->adresse;
        $this->telephone = $caissier->telephone;
        $this->is_active = $caissier->is_active;
    }

    public function save()
    {
        $this->validate();

        $this->caissier->user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);

        $this->caissier->update([
            'numero_identifiant' => $this->numero_identifiant,
            'nom_point_collecte' => $this->nom_point_collecte,
            'zone' => $this->zone,
            'adresse' => $this->adresse,
            'telephone' => $this->telephone,
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Caissier mise a jour avec succes.');
        return redirect()->route('admin.caissiers.index');
    }

    public function render()
    {
        return view('livewire.admin.caissiers.edit');
    }
}
