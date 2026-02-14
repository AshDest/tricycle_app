<?php

namespace App\Livewire\Admin\Zones;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Zone;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatut = '';
    public $perPage = 15;

    protected $queryString = ['search', 'filterStatut'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatut()
    {
        $this->resetPage();
    }

    public function toggleActive(Zone $zone)
    {
        $zone->update(['is_active' => !$zone->is_active]);
    }

    public function delete(Zone $zone)
    {
        $zone->delete();
        session()->flash('success', 'Zone supprimee avec succes.');
    }

    public function render()
    {
        $zones = Zone::when($this->search, function ($q) {
                $q->where('nom', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterStatut !== '', function ($q) {
                if ($this->filterStatut === 'actif') {
                    $q->where('is_active', true);
                } elseif ($this->filterStatut === 'inactif') {
                    $q->where('is_active', false);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.zones.index', compact('zones'));
    }
}
