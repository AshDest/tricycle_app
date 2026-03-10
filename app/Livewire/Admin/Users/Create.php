<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

#[Layout('components.dashlite-layout')]
class Create extends Component
{
    public $name = '';
    public $email = '';
    public $phone = '';
    public $password = '';
    public $password_confirmation = '';
    public $role = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'nullable|string|max:20',
        'password' => 'required|string|min:6|confirmed',
        'role' => 'required|string|not_in:super-admin',
    ];

    protected $messages = [
        'role.not_in' => 'Vous ne pouvez pas créer un utilisateur avec le rôle Super Admin.',
    ];

    public function save()
    {
        $this->validate();

        // Double vérification de sécurité
        if ($this->role === 'super-admin') {
            session()->flash('error', 'Création d\'un Super Admin non autorisée.');
            return;
        }

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => Hash::make($this->password),
        ]);

        $user->assignRole($this->role);

        session()->flash('success', 'Utilisateur créé avec succès.');
        return redirect()->route('admin.users.index');
    }

    public function render()
    {
        // Exclure le rôle super-admin de la liste
        $roles = \Spatie\Permission\Models\Role::where('name', '!=', 'super-admin')->get();
        return view('livewire.admin.users.create', compact('roles'));
    }
}
