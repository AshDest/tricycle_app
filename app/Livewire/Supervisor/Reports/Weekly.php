<?php

namespace App\Livewire\Supervisor\Reports;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Weekly extends Component
{
    public $week_of;

    public function mount()
    {
        $this->week_of = Carbon::today()->format('Y-m-d');
    }

    public function render()
    {
        return view('livewire.supervisor.reports.weekly');
    }
}
