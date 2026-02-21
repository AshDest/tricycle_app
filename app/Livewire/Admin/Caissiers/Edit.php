<?php

namespace App\Livewire\Admin\Caissiers;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Caissier;
use App\Models\Zone;

#[Layout('components.dashlite-layout')]
class Edit extends Component
{
    public $caissier;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $nom_point_collecte = '';
    public $zone_id = '';
    public $adresse = '';
    public $telephone = '';
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'phone' => 'nullable|string|max:20',
        'nom_point_collecte' => 'required|string|max:255',
        'zone_id' => 'required|exists:zones,id',
        'adresse' => 'required|string|max:255',
        'telephone' => 'required|string|max:20',
    ];

    protected $messages = [
        'zone_id.required' => 'Veuillez sélectionner une zone.',
        'zone_id.exists' => 'La zone sélectionnée est invalide.',
    ];

    public function mount(Caissier $caissier)
    {
        $this->caissier = $caissier;
        $this->name = $caissier->user->name;
        $this->email = $caissier->user->email;
        $this->phone = $caissier->user->phone;
        $this->nom_point_collecte = $caissier->nom_point_collecte;

        // Chercher la zone correspondante
        $zone = Zone::where('nom', $caissier->zone)->first();
        $this->zone_id = $zone?->id ?? '';

        $this->adresse = $caissier->adresse;
        $this->telephone = $caissier->telephone;
        $this->is_active = $caissier->is_active;
    }

    public function save()
    {
        $this->validate();

        // Récupérer le nom de la zone
        $zone = Zone::find($this->zone_id);

        $this->caissier->user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);

        $this->caissier->update([
            'nom_point_collecte' => $this->nom_point_collecte,
            'zone' => $zone->nom ?? '',
            'adresse' => $this->adresse,
            'telephone' => $this->telephone,
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Caissier mis à jour avec succès.');
        return redirect()->route('admin.caissiers.index');
    }

    public function render()
    {
        $zones = Zone::orderBy('nom')->get();

        return view('livewire.admin.caissiers.edit', compact('zones'));
    }
}
