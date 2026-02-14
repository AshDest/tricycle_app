<?php

namespace App\Livewire\Admin\Settings;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    public $app_name = '';
    public $app_email = '';
    public $app_phone = '';

    public function mount()
    {
        $this->app_name = config('app.name');
        $this->app_email = config('app.email', '');
        $this->app_phone = config('app.phone', '');
    }

    public function save()
    {
        // Handle settings update here
        session()->flash('success', 'Parametres mis a jour avec succes.');
    }

    public function render()
    {
        return view('livewire.admin.settings.index');
    }
}
