<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;

#[Layout('components.dashlite-layout')]
class Edit extends Component
{
    public $user;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $role = '';
    public $isSuperAdmin = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'phone' => 'nullable|string|max:20',
        'role' => 'required|string|not_in:super-admin',
    ];

    protected $messages = [
        'role.not_in' => 'Vous ne pouvez pas attribuer le rôle Super Admin.',
    ];

    public function mount(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->role = $user->roles()->first()?->name ?? '';

        // Vérifier si c'est un super-admin
        $this->isSuperAdmin = $user->hasRole('super-admin');

        // Empêcher l'édition d'un super-admin
        if ($this->isSuperAdmin) {
            session()->flash('error', 'Vous ne pouvez pas modifier un Super Admin.');
            return redirect()->route('admin.users.index');
        }
    }

    public function save()
    {
        // Double vérification de sécurité
        if ($this->isSuperAdmin || $this->role === 'super-admin') {
            session()->flash('error', 'Opération non autorisée.');
            return redirect()->route('admin.users.index');
        }

        $this->validate();

        $this->user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);

        $this->user->syncRoles([$this->role]);

        session()->flash('success', 'Utilisateur mis à jour avec succès.');
        return redirect()->route('admin.users.index');
    }

    public function render()
    {
        // Exclure le rôle super-admin de la liste
        $roles = \Spatie\Permission\Models\Role::where('name', '!=', 'super-admin')->get();
        return view('livewire.admin.users.edit', compact('roles'));
    }
}
