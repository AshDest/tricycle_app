<?php

namespace App\Livewire\Admin\Caissiers;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\Caissier;
use App\Models\Zone;
use Illuminate\Support\Facades\Hash;

#[Layout('components.dashlite-layout')]
class Create extends Component
{
    public $name = '';
    public $email = '';
    public $phone = '';
    public $password = '';
    public $numero_identifiant = '';
    public $nom_point_collecte = '';
    public $zone_id = '';
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
        'zone_id' => 'required|exists:zones,id',
        'adresse' => 'required|string|max:255',
        'telephone' => 'required|string|max:20',
    ];

    protected $messages = [
        'zone_id.required' => 'Veuillez sélectionner une zone.',
        'zone_id.exists' => 'La zone sélectionnée est invalide.',
    ];

    public function save()
    {
        $this->validate();

        // Récupérer le nom de la zone
        $zone = Zone::find($this->zone_id);

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
            'zone' => $zone->nom ?? $zone->name ?? '',
            'adresse' => $this->adresse,
            'telephone' => $this->telephone,
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Caissier créé avec succès.');
        return redirect()->route('admin.caissiers.index');
    }

    public function render()
    {
        $zones = Zone::orderBy('nom')->get();

        return view('livewire.admin.caissiers.create', compact('zones'));
    }
}
