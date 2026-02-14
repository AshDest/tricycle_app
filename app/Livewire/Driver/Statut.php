<?php

namespace App\Livewire\Driver;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Motard;
use App\Models\Versement;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Statut extends Component
{
    public $motard;
    public $moto;
    public $statutJour = 'non_effectué';
    public $montantAttendu = 0;
    public $montantVerse = 0;
    public $resteAPayer = 0;
    public $modePaiement = null;
    public $joursPayes = 0;
    public $joursEnRetard = 0;
    public $arrieresCumules = 0;

    public function mount()
    {
        $user = auth()->user();
        $this->motard = $user->motard;

        if ($this->motard) {
            $this->moto = $this->motard->moto;
            $today = Carbon::today();
            $startOfMonth = Carbon::now()->startOfMonth();

            // Montant attendu (du moto ou par défaut)
            $this->montantAttendu = $this->moto->montant_journalier_attendu ?? 5000;

            // Versement du jour
            $versementJour = Versement::where('motard_id', $this->motard->id)
                ->whereDate('date_versement', $today)
                ->first();

            if ($versementJour) {
                $this->montantVerse = $versementJour->montant ?? 0;
                $this->statutJour = $versementJour->statut ?? 'non_effectué';
                $this->modePaiement = $versementJour->mode_paiement;
            }

            $this->resteAPayer = max(0, $this->montantAttendu - $this->montantVerse);

            // Stats du mois
            $versementsMois = Versement::where('motard_id', $this->motard->id)
                ->whereDate('date_versement', '>=', $startOfMonth)
                ->get();

            $this->joursPayes = $versementsMois->where('statut', 'payé')->count();
            $this->joursEnRetard = $versementsMois->whereIn('statut', ['en_retard', 'non_effectué'])->count();

            // Arriérés cumulés
            $this->arrieresCumules = Versement::where('motard_id', $this->motard->id)
                ->whereIn('statut', ['en_retard', 'partiellement_payé', 'non_effectué'])
                ->selectRaw('COALESCE(SUM(montant_attendu - COALESCE(montant, 0)), 0) as total')
                ->value('total') ?? 0;
        }
    }

    public function render()
    {
        return view('livewire.driver.statut');
    }
}
