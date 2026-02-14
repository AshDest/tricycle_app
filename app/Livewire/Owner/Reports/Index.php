<?php

namespace App\Livewire\Owner\Reports;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.owner.reports.index');
    }
}
