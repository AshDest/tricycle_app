<?php

namespace App\Livewire\Supervisor\Reports;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Monthly extends Component
{
    public $month;
    public $year;

    public function mount()
    {
        $now = Carbon::now();
        $this->month = $now->format('m');
        $this->year = $now->format('Y');
    }

    public function render()
    {
        return view('livewire.supervisor.reports.monthly');
    }
}
