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
            $this->totalArrieres = $versements->sum('montant_attendu') - $versements->sum('montant');

            // Versements ce mois
            $this->versementsCeMois = Versement::whereIn('moto_id', $motoIds)
                ->whereMonth('date_versement', now()->month)
                ->whereYear('date_versement', now()->year)
                ->sum('montant');

            // Paiements reçus
            $this->totalPaye = $this->proprietaire->payments()->where('statut', 'payé')->sum('total_paye');
            $this->paiementsEnAttente = $this->proprietaire->payments()->where('statut', 'en_attente')->count();

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
