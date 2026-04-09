<?php

namespace App\Livewire\Owner;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Proprietaire;
use App\Models\Payment;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Dashboard extends Component
{
    public $proprietaire;
    public $motos = [];

    // Statistiques
    public $totalMotos = 0;
    public $motosActives = 0;
    public $totalRecuUsd = 0;
    public $recuMoisUsd = 0;
    public $paiementsEnAttente = 0;

    // Listes
    public $derniersPaiements = [];

    public function mount()
    {
        $user = auth()->user();
        $this->proprietaire = $user->proprietaire;

        if (!$this->proprietaire) {
            return;
        }

        // Charger les motos
        $this->motos = $this->proprietaire->motos()->with('motard.user')->get();

        // Statistiques motos
        $this->totalMotos = $this->motos->count();
        $this->motosActives = $this->motos->where('statut', 'actif')->count();

        // Total reçu en USD (tous les paiements payés/validés)
        $this->totalRecuUsd = $this->proprietaire->payments()
            ->whereIn('statut', ['paye', 'payé', 'valide'])
            ->sum('montant_usd') ?? 0;

        // Reçu ce mois en USD
        $this->recuMoisUsd = $this->proprietaire->payments()
            ->whereIn('statut', ['paye', 'payé', 'valide'])
            ->whereMonth('date_paiement', now()->month)
            ->whereYear('date_paiement', now()->year)
            ->sum('montant_usd') ?? 0;

        // Paiements en attente
        $this->paiementsEnAttente = $this->proprietaire->payments()
            ->whereIn('statut', ['en_attente', 'demande'])
            ->count();


        // Derniers paiements (5)
        $this->derniersPaiements = $this->proprietaire->payments()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.owner.dashboard');
    }
}
