<?php

namespace App\Livewire\Admin\Cleaners;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Cleaner;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

#[Layout('components.dashlite-layout')]
class Create extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $telephone = '';
    public $zone = '';
    public $adresse = '';
    public $notes = '';

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'telephone' => 'nullable|string|max:20',
            'zone' => 'nullable|string|max:100',
            'adresse' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }

    protected $messages = [
        'name.required' => 'Le nom est obligatoire.',
        'email.required' => 'L\'email est obligatoire.',
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

            // Assigner le rôle cleaner
            $user->assignRole('cleaner');

            // Créer le profil cleaner
            Cleaner::create([
                'user_id' => $user->id,
                'telephone' => $this->telephone,
                'zone' => $this->zone,
                'adresse' => $this->adresse,
                'notes' => $this->notes,
                'is_active' => true,
            ]);

            DB::commit();

            session()->flash('success', 'Laveur créé avec succès.');
            return redirect()->route('admin.cleaners.index');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $zones = Zone::orderBy('nom')->get();

        return view('livewire.admin.cleaners.create', [
            'zones' => $zones,
        ]);
    }
}

