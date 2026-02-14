<?php

namespace App\Livewire\Admin\Collecteurs;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\Collecteur;
use Illuminate\Support\Facades\Hash;

#[Layout('components.dashlite-layout')]
class Create extends Component
{
    public $name = '';
    public $email = '';
    public $phone = '';
    public $password = '';
    public $numero_identifiant = '';
    public $zone_affectation = '';
    public $telephone = '';
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'nullable|string|max:20',
        'password' => 'required|string|min:6',
        'numero_identifiant' => 'required|string|unique:collecteurs,numero_identifiant',
        'zone_affectation' => 'required|string|max:255',
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

        $user->assignRole('collector');

        Collecteur::create([
            'user_id' => $user->id,
            'numero_identifiant' => $this->numero_identifiant,
            'zone_affectation' => $this->zone_affectation,
            'telephone' => $this->telephone,
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Collecteur cree avec succes.');
        return redirect()->route('admin.collecteurs.index');
    }

    public function render()
    {
        return view('livewire.admin.collecteurs.create');
    }
}
