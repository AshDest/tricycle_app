<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use App\Models\User;

class Show extends Component
{
    public $user;

    public function mount(User $user)
    {
        $this->user = $user;
    }

    public function render()
    {
        return view('livewire.admin.users.show');
    }
}
