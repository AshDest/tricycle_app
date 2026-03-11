<?php

namespace App\Livewire\Supervisor\Recompenses;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\PerformanceMotard;
use App\Services\PerformanceService;

/**
 * Classement des performances - Vue OKAMI
 */
#[Layout('components.dashlite-layout')]
class Classement extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $mois;
    public $annee;
    public $search = '';
    public $badgeFilter = '';

    public function mount()
    {
        $this->mois = now()->month;
        $this->annee = now()->year;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    private function getNomMois(): string
    {
        $mois = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];
        return $mois[$this->mois] ?? '';
    }

    public function render()
    {
        $service = new PerformanceService();

        $query = PerformanceMotard::where('mois', $this->mois)
            ->where('annee', $this->annee)
            ->with('motard.user');

        if ($this->search) {
            $query->whereHas('motard.user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->badgeFilter) {
            $query->where('badge', $this->badgeFilter);
        }

        $performances = $query->orderBy('rang_mensuel')->paginate(15);
        $statistiques = $service->getStatistiquesGlobales($this->mois, $this->annee);
        $topMotards = $service->getTopMotards(5, $this->mois, $this->annee);

        return view('livewire.supervisor.recompenses.classement', [
            'performances' => $performances,
            'statistiques' => $statistiques,
            'topMotards' => $topMotards,
            'moisOptions' => $this->getMoisOptions(),
            'anneeOptions' => $this->getAnneeOptions(),
        ]);
    }

    private function getMoisOptions(): array
    {
        return [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];
    }

    private function getAnneeOptions(): array
    {
        $anneeActuelle = now()->year;
        return range($anneeActuelle - 2, $anneeActuelle);
    }
}

