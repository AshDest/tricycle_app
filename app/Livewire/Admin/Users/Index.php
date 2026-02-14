<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterRole = '';
    public $perPage = 15;

    protected $queryString = ['search', 'filterRole'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterRole()
    {
        $this->resetPage();
    }

    public function delete(User $user)
    {
        $user->delete();
        session()->flash('success', 'Utilisateur supprime avec succes.');
    }

    public function render()
    {
        $users = User::when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterRole, function ($q) {
                $q->role($this->filterRole);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        $roles = \Spatie\Permission\Models\Role::all();

        return view('livewire.admin.users.index', compact('users', 'roles'));
    }
}
