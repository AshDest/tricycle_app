<?php

namespace App\Livewire\Supervisor;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Motard;
use App\Models\Versement;
use App\Models\Tournee;
use App\Services\ReportService;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Dashboard extends Component
{
    public $motardsEnRetard = [];
    public $versementsAujourdhui = 0;
    public $versementsAttenduAujourdhui = 0;
    public $arrieresCumules = 0;
    public $versementsAValider = 0;
    public $tourneesAujourdhui = [];
    public $statsParZone = [];

    public function mount()
    {
        $today = Carbon::today();

        // Motards en retard
        $this->motardsEnRetard = Motard::whereHas('versements', function($q) {
            $q->where('statut', 'en_retard');
        })->with('user')->take(10)->get();

        // Versements aujourd'hui
        $versementsToday = Versement::whereDate('date_versement', $today)->get();
        $this->versementsAujourdhui = $versementsToday->sum('montant');
        $this->versementsAttenduAujourdhui = $versementsToday->sum('montant_attendu');

        // Arriérés cumulés
        $this->arrieresCumules = Versement::where('statut', '!=', 'payé')
            ->selectRaw('COALESCE(SUM(montant_attendu - montant), 0) as total')
            ->value('total') ?? 0;

        // Versements à valider (cas douteux)
        $this->versementsAValider = Versement::where('valide_par_okami', false)
            ->whereIn('statut', ['partiellement_payé', 'en_retard'])
            ->count();

        // Tournées du jour
        $this->tourneesAujourdhui = Tournee::whereDate('date', $today)
            ->with('collecteur.user')
            ->get();
    }

    public function render()
    {
        return view('livewire.supervisor.dashboard');
    }
}

