<?php

namespace App\Livewire\Admin\Cleaners;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Cleaner;
use App\Models\Zone;

#[Layout('components.dashlite-layout')]
class Edit extends Component
{
    public Cleaner $cleaner;

    public $name = '';
    public $email = '';
    public $telephone = '';
    public $zone = '';
    public $adresse = '';
    public $notes = '';
    public $is_active = true;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->cleaner->user_id,
            'telephone' => 'nullable|string|max:20',
            'zone' => 'nullable|string|max:100',
            'adresse' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function mount(Cleaner $cleaner)
    {
        $this->cleaner = $cleaner->load('user');
        $this->name = $cleaner->user->name;
        $this->email = $cleaner->user->email;
        $this->telephone = $cleaner->telephone;
        $this->zone = $cleaner->zone;
        $this->adresse = $cleaner->adresse;
        $this->notes = $cleaner->notes;
        $this->is_active = $cleaner->is_active;
    }

    public function save()
    {
        $this->validate();

        try {
            // Mettre à jour l'utilisateur
            $this->cleaner->user->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            // Mettre à jour le profil cleaner
            $this->cleaner->update([
                'telephone' => $this->telephone,
                'zone' => $this->zone,
                'adresse' => $this->adresse,
                'notes' => $this->notes,
                'is_active' => $this->is_active,
            ]);

            session()->flash('success', 'Laveur mis à jour avec succès.');
            return redirect()->route('admin.cleaners.index');

        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $zones = Zone::orderBy('nom')->get();

        return view('livewire.admin.cleaners.edit', [
            'zones' => $zones,
        ]);
    }
}

