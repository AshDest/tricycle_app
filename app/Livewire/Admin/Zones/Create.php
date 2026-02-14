<?php

namespace App\Livewire\Admin\Zones;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Zone;

#[Layout('components.dashlite-layout')]
class Create extends Component
{
    public $nom = '';
    public $description = '';
    public $communes = '';
    public $is_active = true;

    protected $rules = [
        'nom' => 'required|string|max:255|unique:zones,nom',
        'description' => 'nullable|string',
        'communes' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function save()
    {
        $this->validate();

        Zone::create([
            'nom' => $this->nom,
            'description' => $this->description,
            'communes' => $this->communes ? json_encode(array_filter(array_map('trim', explode(',', $this->communes)))) : null,
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Zone creee avec succes.');
        return redirect()->route('admin.zones.index');
    }

    public function render()
    {
        return view('livewire.admin.zones.create');
    }
}
