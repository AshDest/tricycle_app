<?php

namespace App\Livewire\Driver;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Versement;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Dashboard extends Component
{
    public $motard = null;
    public $moto = null;

    // Stats individuelles (pas de tableaux)
    public $versementAujourdhui = 0;
    public $montantAttendu = 0;
    public $totalMois = 0;
    public $totalAttenduMois = 0;
    public $joursPayes = 0;
    public $joursTravailles = 0;
    public $joursEnRetard = 0;
    public $joursPartiels = 0;
    public $arrieres = 0;
    public $arrieresMois = 0;
    public $tauxPaiement = 0;
    public $statutDuJour = 'non_effectue';
    public $statutArriere = 'ok';
    public $derniersVersements = [];

    public function mount()
    {
        $user = auth()->user();
        $this->motard = $user->motard;

        if ($this->motard) {
            $today = Carbon::today();
            $startOfMonth = Carbon::now()->startOfMonth();

            // Moto actuelle
            $this->moto = $this->motard->moto;
            $this->montantAttendu = $this->moto->montant_journalier_attendu ?? 5000;

            // Versement aujourd'hui
            $versementJour = Versement::where('motard_id', $this->motard->id)
                ->whereDate('date_versement', $today)
                ->first();

            if ($versementJour) {
                $this->versementAujourdhui = $versementJour->montant ?? 0;
                $this->statutDuJour = $versementJour->statut ?? 'non_effectue';
            }

            // Versements du mois
            $versementsMois = Versement::where('motard_id', $this->motard->id)
                ->whereDate('date_versement', '>=', $startOfMonth)
                ->get();

            // Total du mois (versé vs attendu)
            $this->totalMois = $versementsMois->sum('montant') ?? 0;
            $this->totalAttenduMois = $versementsMois->sum('montant_attendu') ?? 0;

            // Statistiques par statut
            $this->joursPayes = $versementsMois->whereIn('statut', ['paye', 'payé'])->count();
            $this->joursPartiels = $versementsMois->whereIn('statut', ['partiel', 'partiellement_payé'])->count();
            $this->joursEnRetard = $versementsMois->whereIn('statut', ['en_retard', 'non_effectue', 'non_effectué'])->count();
            $this->joursTravailles = $today->day;

            // Arriérés du mois
            $this->arrieresMois = $versementsMois->sum(function ($v) {
                return max(0, ($v->montant_attendu ?? 0) - ($v->montant ?? 0));
            });

            // Arriérés cumulés totaux
            $this->arrieres = $this->motard->getTotalArrieres();

            // Statut d'arriéré
            $this->statutArriere = $this->motard->statut_arriere;

            // Taux de paiement
            $this->tauxPaiement = $this->motard->taux_paiement;

            // Derniers versements (historique)
            $this->derniersVersements = Versement::where('motard_id', $this->motard->id)
                ->with('moto')
                ->orderBy('date_versement', 'desc')
                ->take(10)
                ->get();
        }
    }

    public function render()
    {
        return view('livewire.driver.dashboard');
    }
}
