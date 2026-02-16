<?php

namespace App\Livewire\Supervisor\Motards;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Motard;
use App\Models\Zone;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

#[Layout('components.dashlite-layout')]
class Edit extends Component
{
    public Motard $motard;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $numero_identifiant = '';
    public string $licence_numero = '';
    public string $zone_affectation = '';
    public bool $is_active = true;

    public function mount(Motard $motard): void
    {
        $this->motard = $motard->load('user');

        $this->name = $motard->user->name ?? '';
        $this->email = $motard->user->email ?? '';
        $this->phone = $motard->telephone ?? '';
        $this->numero_identifiant = $motard->numero_identifiant ?? '';
        $this->licence_numero = $motard->licence_numero ?? '';
        $this->zone_affectation = $motard->zone_affectation ?? '';
        $this->is_active = $motard->is_active ?? true;
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->motard->user_id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'numero_identifiant' => 'required|string|unique:motards,numero_identifiant,' . $this->motard->id,
            'licence_numero' => 'nullable|string|max:50',
            'zone_affectation' => 'required|string|max:255',
        ];
    }

    protected $messages = [
        'name.required' => 'Le nom est obligatoire.',
        'email.required' => 'L\'email est obligatoire.',
        'email.unique' => 'Cet email est déjà utilisé.',
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

            // Mise à jour de l'utilisateur
            $userData = [
                'name' => $this->name,
                'email' => $this->email,
            ];

            if (!empty($this->password)) {
                $userData['password'] = Hash::make($this->password);
            }

            $this->motard->user->update($userData);

            // Mise à jour du motard
            $this->motard->update([
                'numero_identifiant' => $this->numero_identifiant,
                'telephone' => $this->phone ?: null,
                'licence_numero' => $this->licence_numero ?: null,
                'zone_affectation' => $this->zone_affectation,
                'is_active' => $this->is_active,
            ]);

            DB::commit();

            session()->flash('success', 'Motard mis à jour avec succès.');
            return redirect()->route('supervisor.motards.index');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $zones = Zone::orderBy('nom')->get();
        return view('livewire.supervisor.motards.edit', compact('zones'));
    }
}

