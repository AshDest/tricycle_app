<?php

namespace App\Livewire\Admin\Caissiers;

use Livewire\Component;
use App\Models\User;
use App\Models\Caissier;
use Illuminate\Support\Facades\Hash;

class Create extends Component
{
    public $name = '';
    public $email = '';
    public $phone = '';
    public $password = '';
    public $numero_identifiant = '';
    public $nom_point_collecte = '';
    public $zone = '';
    public $adresse = '';
    public $telephone = '';
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'nullable|string|max:20',
        'password' => 'required|string|min:6',
        'numero_identifiant' => 'required|string|unique:caissiers,numero_identifiant',
        'nom_point_collecte' => 'required|string|max:255',
        'zone' => 'required|string|max:255',
        'adresse' => 'required|string|max:255',
        'telephone' => 'required|string|max:20',
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

        $user->assignRole('cashier');

        Caissier::create([
            'user_id' => $user->id,
            'numero_identifiant' => $this->numero_identifiant,
            'nom_point_collecte' => $this->nom_point_collecte,
            'zone' => $this->zone,
            'adresse' => $this->adresse,
            'telephone' => $this->telephone,
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Caissier cree avec succes.');
        return redirect()->route('admin.caissiers.index');
    }

    public function render()
    {
        return view('livewire.admin.caissiers.create');
    }
}
