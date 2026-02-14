<?php

namespace App\Livewire\Admin\Collecteurs;

use Livewire\Component;
use App\Models\Collecteur;

class Edit extends Component
{
    public $collecteur;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $numero_identifiant = '';
    public $zone_affectation = '';
    public $telephone = '';
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'phone' => 'nullable|string|max:20',
        'numero_identifiant' => 'required|string',
        'zone_affectation' => 'required|string|max:255',
        'telephone' => 'required|string|max:20',
    ];

    public function mount(Collecteur $collecteur)
    {
        $this->collecteur = $collecteur;
        $this->name = $collecteur->user->name;
        $this->email = $collecteur->user->email;
        $this->phone = $collecteur->user->phone;
        $this->numero_identifiant = $collecteur->numero_identifiant;
        $this->zone_affectation = $collecteur->zone_affectation;
        $this->telephone = $collecteur->telephone;
        $this->is_active = $collecteur->is_active;
    }

    public function save()
    {
        $this->validate();

        $this->collecteur->user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);

        $this->collecteur->update([
            'numero_identifiant' => $this->numero_identifiant,
            'zone_affectation' => $this->zone_affectation,
            'telephone' => $this->telephone,
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Collecteur mise a jour avec succes.');
        return redirect()->route('admin.collecteurs.index');
    }

    public function render()
    {
        return view('livewire.admin.collecteurs.edit');
    }
}
