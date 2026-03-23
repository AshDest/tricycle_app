<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

#[Layout('components.dashlite-layout')]
class UsersManager extends Component
{
    use WithPagination;

    // Formulaire de création
    public $showCreateModal = false;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $password = '';
    public $password_confirmation = '';
    public $role = 'admin';

    // Formulaire d'édition
    public $showEditModal = false;
    public $editUserId = null;
    public $editName = '';
    public $editEmail = '';
    public $editPhone = '';
    public $editPassword = '';
    public $editPassword_confirmation = '';
    public $editRole = '';

    // Confirmation suppression
    public $showDeleteModal = false;
    public $deleteUserId = null;
    public $deleteUserName = '';

    // Filtres
    public $search = '';
    public $filterRole = '';

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterRole()
    {
        $this->resetPage();
    }

    // === CRÉATION ===
    public function openCreate()
    {
        $this->reset(['name', 'email', 'phone', 'password', 'password_confirmation', 'role']);
        $this->role = 'admin';
        $this->showCreateModal = true;
    }

    public function create()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|in:admin,supervisor,owner,driver,cashier,collector,cleaner',
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        $user->forceFill([
            'phone' => $this->phone ?: null,
            'email_verified_at' => now(),
        ])->save();

        $user->assignRole($this->role);

        $this->showCreateModal = false;
        $this->reset(['name', 'email', 'phone', 'password', 'password_confirmation', 'role']);
        session()->flash('success', "Utilisateur {$user->name} créé avec le rôle {$this->getRoleLabel($user->roles->first()->name ?? '')}.");
    }

    // === ÉDITION ===
    public function openEdit($userId)
    {
        $user = User::findOrFail($userId);

        // Ne pas éditer un super-admin
        if ($user->hasRole('super-admin')) {
            session()->flash('error', 'Impossible de modifier un Super Admin.');
            return;
        }

        $this->editUserId = $user->id;
        $this->editName = $user->name;
        $this->editEmail = $user->email;
        $this->editPhone = $user->phone ?? '';
        $this->editPassword = '';
        $this->editPassword_confirmation = '';
        $this->editRole = $user->roles->first()->name ?? '';
        $this->showEditModal = true;
    }

    public function update()
    {
        $rules = [
            'editName' => 'required|string|max:255',
            'editEmail' => 'required|email|unique:users,email,' . $this->editUserId,
            'editPhone' => 'nullable|string|max:20',
            'editRole' => 'required|string|in:admin,supervisor,owner,driver,cashier,collector,cleaner',
        ];

        if (!empty($this->editPassword)) {
            $rules['editPassword'] = 'required|string|min:6|confirmed';
        }

        $this->validate($rules);

        $user = User::findOrFail($this->editUserId);

        if ($user->hasRole('super-admin')) {
            session()->flash('error', 'Impossible de modifier un Super Admin.');
            return;
        }

        $user->update([
            'name' => $this->editName,
            'email' => $this->editEmail,
        ]);

        $user->forceFill(['phone' => $this->editPhone ?: null])->save();

        if (!empty($this->editPassword)) {
            $user->update(['password' => Hash::make($this->editPassword)]);
        }

        // Changer le rôle
        $user->syncRoles([$this->editRole]);

        $this->showEditModal = false;
        session()->flash('success', "Utilisateur {$user->name} mis à jour.");
    }

    // === SUPPRESSION ===
    public function confirmDelete($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->hasRole('super-admin')) {
            session()->flash('error', 'Impossible de supprimer un Super Admin.');
            return;
        }

        $this->deleteUserId = $userId;
        $this->deleteUserName = $user->name;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $user = User::findOrFail($this->deleteUserId);

        if ($user->hasRole('super-admin')) {
            session()->flash('error', 'Impossible de supprimer un Super Admin.');
            return;
        }

        $user->delete();
        $this->showDeleteModal = false;
        session()->flash('success', "Utilisateur {$this->deleteUserName} supprimé.");
    }

    // === TOGGLE ACTIF ===
    public function toggleActive($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->hasRole('super-admin')) {
            return;
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activé' : 'désactivé';
        session()->flash('success', "Utilisateur {$user->name} {$status}.");
    }

    // === HELPERS ===
    public function getRoleLabel($role): string
    {
        return match($role) {
            'super-admin' => 'Super Admin',
            'admin' => 'Administrateur',
            'supervisor' => 'Superviseur OKAMI',
            'owner' => 'Propriétaire',
            'driver' => 'Motard',
            'cashier' => 'Caissier',
            'collector' => 'Collecteur',
            'cleaner' => 'Laveur',
            default => ucfirst($role),
        };
    }

    public function getRoleBadge($role): string
    {
        return match($role) {
            'super-admin' => 'danger',
            'admin' => 'primary',
            'supervisor' => 'warning',
            'owner' => 'info',
            'driver' => 'success',
            'cashier' => 'secondary',
            'collector' => 'dark',
            'cleaner' => 'teal',
            default => 'light',
        };
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('name', 'like', "%{$this->search}%")
                       ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterRole, function ($q) {
                $q->whereHas('roles', fn($r) => $r->where('name', $this->filterRole));
            })
            ->with('roles')
            ->orderByDesc('created_at')
            ->paginate(15);

        $roles = Role::where('name', '!=', 'super-admin')->orderBy('name')->get();
        $totalUsers = User::count();
        $totalAdmins = User::role('admin')->count();

        return view('livewire.super-admin.users-manager', compact('users', 'roles', 'totalUsers', 'totalAdmins'));
    }
}

