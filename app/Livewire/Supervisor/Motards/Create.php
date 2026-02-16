<?php

namespace App\Livewire\Supervisor\Motards;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\Motard;
use App\Models\Zone;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

#[Layout('components.dashlite-layout')]
class Create extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $numero_identifiant = '';
    public string $licence_numero = '';
    public string $zone_affectation = '';
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'numero_identifiant' => 'required|string|unique:motards,numero_identifiant',
            'licence_numero' => 'nullable|string|max:50',
            'zone_affectation' => 'required|string|max:255',
        ];
    }

    protected $messages = [
        'name.required' => 'Le nom est obligatoire.',
        'email.required' => 'L\'email est obligatoire.',
        'email.unique' => 'Cet email est déjà utilisé.',
        'password.required' => 'Le mot de passe est obligatoire.',
        'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        'numero_identifiant.required' => 'Le numéro d\'identifiant est obligatoire.',
        'numero_identifiant.unique' => 'Ce numéro d\'identifiant existe déjà.',
        'zone_affectation.required' => 'La zone d\'affectation est obligatoire.',
    ];

    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);

            $user->assignRole('driver');

            Motard::create([
                'user_id' => $user->id,
                'numero_identifiant' => $this->numero_identifiant,
                'telephone' => $this->phone,
                'licence_numero' => $this->licence_numero ?: null,
                'zone_affectation' => $this->zone_affectation,
                'is_active' => $this->is_active,
            ]);

            DB::commit();

            session()->flash('success', 'Motard créé avec succès.');
            return redirect()->route('supervisor.motards.index');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $zones = Zone::orderBy('nom')->get();
        return view('livewire.supervisor.motards.create', compact('zones'));
    }
}

