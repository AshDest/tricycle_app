<?php

namespace App\Livewire\Supervisor\Proprietaires;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\Proprietaire;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

#[Layout('components.dashlite-layout')]
class Create extends Component
{
    // Informations utilisateur
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';

    // Informations propriétaire
    public string $raison_sociale = '';
    public string $contact_phone = '';
    public string $adresse = '';

    // Comptes Mobile Money
    public string $numero_compte_mpesa = '';
    public string $numero_compte_airtel = '';
    public string $numero_compte_orange = '';

    // Compte bancaire
    public string $numero_compte_bancaire = '';
    public string $banque_nom = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'raison_sociale' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:500',
            'numero_compte_mpesa' => 'nullable|string|max:50',
            'numero_compte_airtel' => 'nullable|string|max:50',
            'numero_compte_orange' => 'nullable|string|max:50',
            'numero_compte_bancaire' => 'nullable|string|max:50',
            'banque_nom' => 'nullable|string|max:255',
        ];
    }

    protected $messages = [
        'name.required' => 'Le nom est obligatoire.',
        'email.required' => 'L\'email est obligatoire.',
        'email.email' => 'L\'email doit être une adresse valide.',
        'email.unique' => 'Cet email est déjà utilisé.',
        'password.required' => 'Le mot de passe est obligatoire.',
        'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        'password.confirmed' => 'Les mots de passe ne correspondent pas.',
    ];

    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // Créer l'utilisateur
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);

            // Assigner le rôle owner
            $user->assignRole('owner');

            // Créer le profil propriétaire
            Proprietaire::create([
                'user_id' => $user->id,
                'raison_sociale' => $this->raison_sociale ?: null,
                'telephone' => $this->contact_phone ?: $this->phone ?: null,
                'adresse' => $this->adresse ?: null,
                'numero_compte_mpesa' => $this->numero_compte_mpesa ?: null,
                'numero_compte_airtel' => $this->numero_compte_airtel ?: null,
                'numero_compte_orange' => $this->numero_compte_orange ?: null,
                'numero_compte_bancaire' => $this->numero_compte_bancaire ?: null,
                'banque_nom' => $this->banque_nom ?: null,
                'is_active' => true,
            ]);

            DB::commit();

            session()->flash('success', 'Propriétaire créé avec succès.');
            return redirect()->route('supervisor.proprietaires.index');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.supervisor.proprietaires.create');
    }
}

