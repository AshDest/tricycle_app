<?php

namespace App\Livewire\Owner;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Proprietaire;
use App\Models\Versement;
use App\Models\Payment;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Dashboard extends Component
{
    public $proprietaire;
    public $motos = [];
    public $totalVersements = 0;
    public $versementsCeMois = 0;
    public $totalArrieres = 0;
    public $totalPaye = 0;
    public $paiementsEnAttente = 0;
    public $derniersVersements = [];
    public $derniersPaiements = [];

    public function mount()
    {
        $user = auth()->user();
        $this->proprietaire = $user->proprietaire;

        if ($this->proprietaire) {
            $this->motos = $this->proprietaire->motos()->with('motard.user')->get();
            $motoIds = $this->motos->pluck('id');

            // Versements totaux
            $versements = Versement::whereIn('moto_id', $motoIds)->get();
            $this->totalVersements = $versements->sum('montant');

            // Arriérés = somme des écarts positifs (montant_attendu - montant)
            $this->totalArrieres = $versements->sum(function($v) {
                return max(0, ($v->montant_attendu ?? 0) - ($v->montant ?? 0));
            });

            // Versements ce mois
            $this->versementsCeMois = Versement::whereIn('moto_id', $motoIds)
                ->whereMonth('date_versement', now()->month)
                ->whereYear('date_versement', now()->year)
                ->sum('montant');

            // Paiements reçus
            $this->totalPaye = $this->proprietaire->payments()->whereIn('statut', ['paye', 'payé', 'valide'])->sum('total_paye');
            $this->paiementsEnAttente = $this->proprietaire->payments()->whereIn('statut', ['en_attente', 'demande'])->count();

            // Derniers versements
            $this->derniersVersements = Versement::whereIn('moto_id', $motoIds)
                ->with(['motard.user', 'moto'])
                ->orderBy('date_versement', 'desc')
                ->take(5)
                ->get();

            // Derniers paiements
            $this->derniersPaiements = $this->proprietaire->payments()
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        }
    }

    public function render()
    {
        return view('livewire.owner.dashboard');
    }
}
