<?php

namespace App\Livewire\Admin\Zones;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Zone;

#[Layout('components.dashlite-layout')]
class Edit extends Component
{
    public $zone;
    public $nom = '';
    public $description = '';
    public $communes = '';
    public $is_active = true;

    protected $rules = [
        'nom' => 'required|string|max:255',
        'description' => 'nullable|string',
        'communes' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function mount(Zone $zone)
    {
        $this->zone = $zone;
        $this->nom = $zone->nom;
        $this->description = $zone->description;
        $this->communes = is_array($zone->communes) ? implode(', ', $zone->communes) : ($zone->communes ? implode(', ', json_decode($zone->communes, true)) : '');
        $this->is_active = $zone->is_active;
    }

    public function save()
    {
        $this->validate();

        $this->zone->update([
            'nom' => $this->nom,
            'description' => $this->description,
            'communes' => $this->communes ? json_encode(array_filter(array_map('trim', explode(',', $this->communes)))) : null,
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Zone mise a jour avec succes.');
        return redirect()->route('admin.zones.index');
    }

    public function render()
    {
        return view('livewire.admin.zones.edit');
    }
}
