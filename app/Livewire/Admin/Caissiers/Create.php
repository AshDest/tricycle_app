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
        'nom_point_collecte' => 'required|string|max:255',
        'zone_id' => 'required|exists:zones,id',
        'adresse' => 'required|string|max:255',
        'telephone' => 'required|string|max:20',
    ];

    protected $messages = [
        'zone_id.required' => 'Veuillez sélectionner une zone.',
        'zone_id.exists' => 'La zone sélectionnée est invalide.',
    ];

    /**
     * Générer un numéro identifiant unique pour le caissier
     */
    protected function generateNumeroIdentifiant(): string
    {
        $prefix = 'CAI';
        $lastCaissier = Caissier::withTrashed()
            ->where('numero_identifiant', 'like', $prefix . '-%')
            ->orderByRaw('CAST(SUBSTRING(numero_identifiant, 5) AS UNSIGNED) DESC')
            ->first();

        if ($lastCaissier) {
            $lastNumber = (int) substr($lastCaissier->numero_identifiant, 4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function save()
    {
        $this->validate();

        // Récupérer le nom de la zone
        $zone = Zone::find($this->zone_id);

        // Générer le numéro identifiant automatiquement
        $numeroIdentifiant = $this->generateNumeroIdentifiant();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => Hash::make($this->password),
        ]);

        $user->assignRole('cashier');

        Caissier::create([
            'user_id' => $user->id,
            'numero_identifiant' => $numeroIdentifiant,
            'nom_point_collecte' => $this->nom_point_collecte,
            'zone' => $zone->nom ?? '',
            'adresse' => $this->adresse,
            'telephone' => $this->telephone,
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Caissier créé avec succès. Identifiant: ' . $numeroIdentifiant);
        return redirect()->route('admin.caissiers.index');
    }

    public function render()
    {
        $zones = Zone::orderBy('nom')->get();

        return view('livewire.admin.caissiers.create', compact('zones'));
    }
}
