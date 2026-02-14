<?php

namespace App\Livewire\Admin\Motards;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Motard;

#[Layout('components.dashlite-layout')]
class Edit extends Component
{
    public Motard $motard;

    public $name = '';
    public $email = '';
    public $phone = '';
    public $numero_identifiant = '';
    public $licence_numero = '';
    public $zone_affectation = '';
    public $is_active = true;

    public function mount(Motard $motard)
    {
        $this->motard = $motard->load('user');
        $this->name = $motard->user->name;
        $this->email = $motard->user->email;
        $this->phone = $motard->user->phone ?? '';
        $this->numero_identifiant = $motard->numero_identifiant;
        $this->licence_numero = $motard->licence_numero ?? '';
        $this->zone_affectation = $motard->zone_affectation;
        $this->is_active = $motard->is_active;
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->motard->user_id,
            'phone' => 'nullable|string|max:20',
            'numero_identifiant' => 'required|string|unique:motards,numero_identifiant,' . $this->motard->id,
            'licence_numero' => 'nullable|string',
            'zone_affectation' => 'required|string|max:255',
        ];
    }

    public function save()
    {
        $this->validate();

        $this->motard->user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);

        $this->motard->update([
            'numero_identifiant' => $this->numero_identifiant,
            'licence_numero' => $this->licence_numero,
            'zone_affectation' => $this->zone_affectation,
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Motard mis a jour avec succes.');
        return redirect()->route('admin.motards.index');
    }

    public function render()
    {
        return view('livewire.admin.motards.edit');
    }
}
