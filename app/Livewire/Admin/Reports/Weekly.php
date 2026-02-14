<?php

namespace App\Livewire\Admin\Reports;

use Livewire\Component;
use Carbon\Carbon;

class Weekly extends Component
{
    public $week_of;

    public function mount()
    {
        $this->week_of = Carbon::today()->format('Y-m-d');
    }

    public function render()
    {
        return view('livewire.admin.reports.weekly');
    }
}
