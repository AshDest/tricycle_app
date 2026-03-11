<?php

namespace App\Livewire\Supervisor\Recompenses;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Recompense;

/**
 * Liste des récompenses - Vue OKAMI
 */
#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $statutFilter = '';
    public $typeFilter = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Recompense::with(['motard.user', 'remisPar']);

        if ($this->search) {
            $query->whereHas('motard.user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statutFilter) {
            $query->where('statut', $this->statutFilter);
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        $recompenses = $query->orderByDesc('created_at')->paginate(15);

        // Statistiques
        $stats = [
            'total' => Recompense::count(),
            'attribuees' => Recompense::where('statut', 'attribue')->count(),
            'remises' => Recompense::where('statut', 'remis')->count(),
            'montant_primes' => Recompense::whereNotNull('montant_prime')->sum('montant_prime'),
        ];

        return view('livewire.supervisor.recompenses.index', [
            'recompenses' => $recompenses,
            'stats' => $stats,
            'types' => Recompense::getTypes(),
            'statuts' => Recompense::getStatuts(),
        ]);
    }
}

