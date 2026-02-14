<?php

namespace App\Livewire\Admin\Reports;

use Livewire\Component;
use Carbon\Carbon;

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
        return view('livewire.admin.reports.monthly');
    }
}
