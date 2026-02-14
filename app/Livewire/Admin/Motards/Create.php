<?php

namespace App\Livewire\Admin\Motards;

use Livewire\Component;
use App\Models\User;
use App\Models\Motard;
use Illuminate\Support\Facades\Hash;

class Create extends Component
{
    public $name = '';
    public $email = '';
    public $phone = '';
    public $password = '';
    public $numero_identifiant = '';
    public $licence_numero = '';
    public $zone_affectation = '';
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'nullable|string|max:20',
        'password' => 'required|string|min:6',
        'numero_identifiant' => 'required|string|unique:motards,numero_identifiant',
        'licence_numero' => 'nullable|string',
        'zone_affectation' => 'required|string|max:255',
    ];

    public function save()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => Hash::make($this->password),
        ]);

        $user->assignRole('driver');

        Motard::create([
            'user_id' => $user->id,
            'numero_identifiant' => $this->numero_identifiant,
            'licence_numero' => $this->licence_numero,
            'zone_affectation' => $this->zone_affectation,
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Motard cree avec succes.');
        return redirect()->route('admin.motards.index');
    }

    public function render()
    {
        return view('livewire.admin.motards.create');
    }
}
