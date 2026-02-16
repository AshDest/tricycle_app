<?php

namespace App\Livewire\Admin\Reports;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Versement;
use App\Models\Collecte;
use App\Models\Payment;
use App\Models\Motard;
use App\Models\Tournee;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Weekly extends Component
{
    public $week_of;
    public $startOfWeek;
    public $endOfWeek;

    // Stats
    public $totalVersements = 0;
    public $totalAttendu = 0;
    public $totalCollecte = 0;
    public $totalPaiements = 0;
    public $motardsEnRetard = 0;
    public $versementsParJour = [];
    public $comparaisonSemainePrecedente = 0;

    public function mount()
    {
        $this->week_of = Carbon::today()->format('Y-m-d');
        $this->loadStats();
    }

    public function updatedWeekOf()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $date = Carbon::parse($this->week_of);
        $this->startOfWeek = $date->copy()->startOfWeek();
        $this->endOfWeek = $date->copy()->endOfWeek();

        // Versements de la semaine
        $versements = Versement::whereBetween('date_versement', [$this->startOfWeek, $this->endOfWeek])->get();
        $this->totalVersements = $versements->sum('montant');
        $this->totalAttendu = $versements->sum('montant_attendu');

        // Collectes
        $this->totalCollecte = Collecte::whereBetween('created_at', [$this->startOfWeek, $this->endOfWeek])
            ->sum('montant_collecte');

        // Paiements propriétaires
        $this->totalPaiements = Payment::whereBetween('date_paiement', [$this->startOfWeek, $this->endOfWeek])
            ->whereIn('statut', ['paye', 'valide'])
            ->sum('total_paye');

        // Motards en retard
        $this->motardsEnRetard = Versement::whereBetween('date_versement', [$this->startOfWeek, $this->endOfWeek])
            ->where('statut', 'en_retard')
            ->distinct('motard_id')
            ->count('motard_id');

        // Versements par jour de la semaine
        $this->versementsParJour = [];
        for ($i = 0; $i < 7; $i++) {
            $jour = $this->startOfWeek->copy()->addDays($i);
            $this->versementsParJour[] = [
                'jour' => $jour->translatedFormat('D'),
                'date' => $jour->format('d/m'),
                'montant' => Versement::whereDate('date_versement', $jour)->sum('montant'),
                'attendu' => Versement::whereDate('date_versement', $jour)->sum('montant_attendu'),
            ];
        }

        // Comparaison avec semaine précédente
        $startPrev = $this->startOfWeek->copy()->subWeek();
        $endPrev = $this->endOfWeek->copy()->subWeek();
        $totalPrev = Versement::whereBetween('date_versement', [$startPrev, $endPrev])->sum('montant');

        if ($totalPrev > 0) {
            $this->comparaisonSemainePrecedente = round((($this->totalVersements - $totalPrev) / $totalPrev) * 100, 1);
        } else {
            $this->comparaisonSemainePrecedente = 0;
        }
    }

    public function render()
    {
        return view('livewire.admin.reports.weekly');
    }
}
