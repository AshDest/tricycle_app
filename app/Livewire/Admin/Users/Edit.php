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

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'phone' => 'nullable|string|max:20',
        'role' => 'required|string',
    ];

    public function mount(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->role = $user->roles()->first()?->name ?? '';
    }

    public function save()
    {
        $this->validate();

        $this->user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);

        $this->user->syncRoles([$this->role]);

        session()->flash('success', 'Utilisateur mise a jour avec succes.');
        return redirect()->route('admin.users.index');
    }

    public function render()
    {
        $roles = \Spatie\Permission\Models\Role::all();
        return view('livewire.admin.users.edit', compact('roles'));
    }
}
