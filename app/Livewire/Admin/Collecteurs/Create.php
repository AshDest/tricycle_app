<?php

namespace App\Livewire\Admin\Collecteurs;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\Collecteur;
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
    public $zone_id = '';
    public $telephone = '';
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'nullable|string|max:20',
        'password' => 'required|string|min:6',
        'zone_id' => 'required|exists:zones,id',
        'telephone' => 'required|string|max:20',
    ];

    protected $messages = [
        'name.required' => 'Le nom est obligatoire.',
        'email.required' => 'L\'email est obligatoire.',
        'email.unique' => 'Cet email est déjà utilisé.',
        'password.required' => 'Le mot de passe est obligatoire.',
        'password.min' => 'Le mot de passe doit avoir au moins 6 caractères.',
        'zone_id.required' => 'Veuillez sélectionner une zone.',
        'telephone.required' => 'Le téléphone est obligatoire.',
    ];

    public function mount()
    {
        $this->numero_identifiant = $this->generateNumeroIdentifiant();
    }

    /**
     * Générer un numéro identifiant unique pour le collecteur
     */
    protected function generateNumeroIdentifiant(): string
    {
        $prefix = 'COL';
        $lastCollecteur = Collecteur::withTrashed()
            ->where('numero_identifiant', 'like', $prefix . '-%')
            ->orderByRaw('CAST(SUBSTRING(numero_identifiant, 5) AS UNSIGNED) DESC')
            ->first();

        if ($lastCollecteur) {
            $lastNumber = (int) substr($lastCollecteur->numero_identifiant, 4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Régénérer le numéro d'identifiant
     */
    public function regenerateNumero()
    {
        $this->numero_identifiant = $this->generateNumeroIdentifiant();
    }

    public function save()
    {
        $this->validate();

        // Récupérer le nom de la zone
        $zone = Zone::find($this->zone_id);

        // Vérifier l'unicité du numéro d'identifiant
        if (Collecteur::where('numero_identifiant', $this->numero_identifiant)->exists()) {
            $this->numero_identifiant = $this->generateNumeroIdentifiant();
        }

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
            'zone_affectation' => $zone->nom ?? '',
            'telephone' => $this->telephone,
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Collecteur créé avec succès. Identifiant: ' . $this->numero_identifiant);
        return redirect()->route('admin.collecteurs.index');
    }

    public function render()
    {
        $zones = Zone::orderBy('nom')->get();

        return view('livewire.admin.collecteurs.create', compact('zones'));
    }
}
