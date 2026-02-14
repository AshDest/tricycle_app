<?php

namespace App\Livewire\Admin\Proprietaires;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Proprietaire;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Show extends Component
{
    public Proprietaire $proprietaire;

    // Statistiques
    public int $totalMotos = 0;
    public int $motosActives = 0;
    public float $totalRevenue = 0;
    public int $totalPayments = 0;
    public float $totalPaye = 0;
    public float $arrieres = 0;
    public float $coutMaintenance = 0;
    public float $versementsCeMois = 0;

    public function mount(Proprietaire $proprietaire): void
    {
        $this->proprietaire = $proprietaire->load(['user', 'motos.motard.user', 'payments']);

        $this->computeStats();
    }

    protected function computeStats(): void
    {
        $this->totalMotos = $this->proprietaire->motos->count();
        $this->motosActives = $this->proprietaire->motos->where('statut', 'actif')->count();

        // Revenus depuis les versements (via motos)
        $this->totalRevenue = $this->proprietaire->versements()->sum('montant');

        // Paiements effectués au propriétaire
        $this->totalPayments = $this->proprietaire->payments->count();
        $this->totalPaye = $this->proprietaire->payments->where('statut', 'payé')->sum('total_paye');

        // Arriérés
        $this->arrieres = $this->totalRevenue - $this->totalPaye;
        if ($this->arrieres < 0) {
            $this->arrieres = 0;
        }

        // Coût maintenance
        $this->coutMaintenance = $this->proprietaire->maintenances()->sum('cout_total');

        // Versements ce mois
        $this->versementsCeMois = $this->proprietaire->versements()
            ->whereMonth('date_versement', now()->month)
            ->whereYear('date_versement', now()->year)
            ->sum('montant');
    }

    public function render()
    {
        $motos = $this->proprietaire->motos()
            ->with(['motard.user'])
            ->get();

        $recentPayments = $this->proprietaire->payments()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('livewire.admin.proprietaires.show', [
            'motos' => $motos,
            'recentPayments' => $recentPayments,
        ])->layout('layouts.dashlite', ['title' => 'Détails Propriétaire']);
    }
}
