<?php

namespace App\Livewire\Supervisor\Reports;

use Livewire\Component;
use Carbon\Carbon;

class Daily extends Component
{
    public $date;

    public function mount()
    {
        $this->date = Carbon::today()->format('Y-m-d');
    }

    public function render()
    {
        return view('livewire.supervisor.reports.daily');
    }
}
