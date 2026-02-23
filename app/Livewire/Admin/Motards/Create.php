<?php

namespace App\Livewire\Admin\Motards;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\Motard;
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
    public $licence_numero = '';
    public $zone_affectation = '';
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'nullable|string|max:20',
        'password' => 'required|string|min:6',
        'licence_numero' => 'nullable|string',
        'zone_affectation' => 'required|string|max:255',
    ];

    public function mount()
    {
        $this->numero_identifiant = $this->generateNumeroIdentifiant();
    }

    /**
     * Génère un numéro d'identifiant unique pour le motard
     * Format: MTD-XXXX (ex: MTD-0001)
     */
    protected function generateNumeroIdentifiant(): string
    {
        $prefix = 'MTD';

        $lastMotard = Motard::withTrashed()
            ->where('numero_identifiant', 'like', $prefix . '-%')
            ->orderByRaw('CAST(SUBSTRING(numero_identifiant, 5) AS UNSIGNED) DESC')
            ->first();

        if ($lastMotard) {
            $lastNumber = (int) substr($lastMotard->numero_identifiant, 4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%04d', $prefix, $newNumber);
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

        // Vérifier l'unicité du numéro d'identifiant
        if (Motard::where('numero_identifiant', $this->numero_identifiant)->exists()) {
            $this->numero_identifiant = $this->generateNumeroIdentifiant();
        }

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

        session()->flash('success', 'Motard créé avec succès.');
        return redirect()->route('admin.motards.index');
    }

    public function render()
    {
        $zones = Zone::orderBy('nom')->get();
        return view('livewire.admin.motards.create', compact('zones'));
    }
}
