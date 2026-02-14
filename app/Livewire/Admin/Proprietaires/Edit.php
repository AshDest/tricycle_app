<?php

namespace App\Livewire\Admin\Proprietaires;

use Livewire\Component;
use App\Models\User;
use App\Models\Proprietaire;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Edit extends Component
{
    public Proprietaire $proprietaire;

    // Informations utilisateur
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';

    // Informations propriétaire
    public string $raison_sociale = '';
    public string $contact_phone = '';
    public string $contact_email = '';
    public string $adresse = '';

    // Comptes Mobile Money
    public string $numero_compte_mpesa = '';
    public string $numero_compte_airtel = '';
    public string $numero_compte_orange = '';

    // Compte bancaire
    public string $numero_compte_bancaire = '';
    public string $banque_nom = '';

    public function mount(Proprietaire $proprietaire): void
    {
        $this->proprietaire = $proprietaire->load('user');

        // Remplir les champs utilisateur
        $this->name = $proprietaire->user->name ?? '';
        $this->email = $proprietaire->user->email ?? '';

        // Remplir les champs propriétaire
        $this->raison_sociale = $proprietaire->raison_sociale ?? '';
        $this->contact_phone = $proprietaire->telephone ?? '';
        $this->adresse = $proprietaire->adresse ?? '';
        $this->numero_compte_mpesa = $proprietaire->numero_compte_mpesa ?? '';
        $this->numero_compte_airtel = $proprietaire->numero_compte_airtel ?? '';
        $this->numero_compte_orange = $proprietaire->numero_compte_orange ?? '';
        $this->numero_compte_bancaire = $proprietaire->numero_compte_bancaire ?? '';
        $this->banque_nom = $proprietaire->banque_nom ?? '';
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->proprietaire->user_id,
            'password' => 'nullable|string|min:8|confirmed',
            'raison_sociale' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
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
        'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        'contact_email.email' => 'L\'email de contact doit être une adresse valide.',
    ];

    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // Mettre à jour l'utilisateur
            $userData = [
                'name' => $this->name,
                'email' => $this->email,
            ];

            if (!empty($this->password)) {
                $userData['password'] = Hash::make($this->password);
            }

            $this->proprietaire->user->update($userData);

            // Mettre à jour le profil propriétaire
            $this->proprietaire->update([
                'raison_sociale' => $this->raison_sociale ?: null,
                'telephone' => $this->contact_phone ?: null,
                'adresse' => $this->adresse ?: null,
                'numero_compte_mpesa' => $this->numero_compte_mpesa ?: null,
                'numero_compte_airtel' => $this->numero_compte_airtel ?: null,
                'numero_compte_orange' => $this->numero_compte_orange ?: null,
                'numero_compte_bancaire' => $this->numero_compte_bancaire ?: null,
                'banque_nom' => $this->banque_nom ?: null,
            ]);

            DB::commit();

            session()->flash('success', 'Propriétaire mis à jour avec succès.');
            return redirect()->route('admin.proprietaires.show', $this->proprietaire);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.proprietaires.edit')
            ->layout('layouts.dashlite', ['title' => 'Modifier Propriétaire']);
    }
}
