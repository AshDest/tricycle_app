<?php

namespace App\Livewire\Admin\Collecteurs;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Collecteur;
use App\Models\Zone;

#[Layout('components.dashlite-layout')]
class Edit extends Component
{
    public $collecteur;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $zone_id = '';
    public $telephone = '';
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'phone' => 'nullable|string|max:20',
        'zone_id' => 'required|exists:zones,id',
        'telephone' => 'required|string|max:20',
    ];

    protected $messages = [
        'zone_id.required' => 'Veuillez sélectionner une zone.',
    ];

    public function mount(Collecteur $collecteur)
    {
        $this->collecteur = $collecteur;
        $this->name = $collecteur->user->name;
        $this->email = $collecteur->user->email;
        $this->phone = $collecteur->user->phone;

        // Chercher la zone correspondante
        $zone = Zone::where('nom', $collecteur->zone_affectation)->first();
        $this->zone_id = $zone?->id ?? '';

        $this->telephone = $collecteur->telephone;
        $this->is_active = $collecteur->is_active;
    }

    public function save()
    {
        $this->validate();

        // Récupérer le nom de la zone
        $zone = Zone::find($this->zone_id);

        $this->collecteur->user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);

        $this->collecteur->update([
            'zone_affectation' => $zone->nom ?? '',
            'telephone' => $this->telephone,
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Collecteur mis à jour avec succès.');
        return redirect()->route('admin.collecteurs.index');
    }

    public function render()
    {
        $zones = Zone::orderBy('nom')->get();

        return view('livewire.admin.collecteurs.edit', compact('zones'));
    }
}
