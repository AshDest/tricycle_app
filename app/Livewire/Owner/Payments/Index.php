<?php

namespace App\Livewire\Owner\Payments;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Payment;
use App\Models\Versement;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatut = '';
    public $filterPeriode = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 15;

    // Stats
    public $totalRecu = 0;
    public $soldeDisponible = 0;
    public $enAttente = 0;
    public $arrieres = 0;

    protected $queryString = ['search', 'filterStatut', 'filterPeriode', 'dateFrom', 'dateTo'];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterStatut() { $this->resetPage(); }
    public function updatingFilterPeriode() { $this->resetPage(); }
    public function updatingDateFrom() { $this->resetPage(); }
    public function updatingDateTo() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'filterStatut', 'filterPeriode', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function demanderRetrait()
    {
        // Pour l'instant, afficher un message
        // Dans une version future, on pourrait ouvrir un modal ou rediriger vers un formulaire
        session()->flash('info', 'La fonctionnalité de demande de retrait sera bientôt disponible. Veuillez contacter l\'administration.');
    }

    public function telechargerRecu($paymentId)
    {
        // Logique pour télécharger le reçu PDF
        session()->flash('info', 'Téléchargement du reçu en cours...');
    }

    public function render()
    {
        $proprietaire = auth()->user()->proprietaire;
        $proprietaire_id = $proprietaire?->id;

        // Calculer les statistiques
        if ($proprietaire) {
            $this->totalRecu = Payment::where('proprietaire_id', $proprietaire_id)
                ->where('statut', 'paye')
                ->sum('total_paye') ?? 0;

            $this->enAttente = Payment::where('proprietaire_id', $proprietaire_id)
                ->where('statut', 'en_attente')
                ->sum('total_du') ?? 0;

            // Solde disponible = versements des motos - paiements reçus
            $totalVersements = $proprietaire->versements()
                ->where('versements.statut', 'payé')
                ->sum('versements.montant') ?? 0;

            $this->soldeDisponible = max(0, $totalVersements - $this->totalRecu);

            // Arriérés = versements en retard des motards
            $this->arrieres = $proprietaire->versements()
                ->whereIn('versements.statut', ['en_retard', 'partiellement_payé'])
                ->selectRaw('COALESCE(SUM(versements.montant_attendu - COALESCE(versements.montant, 0)), 0) as total')
                ->value('total') ?? 0;
        }

        $payments = Payment::where('proprietaire_id', $proprietaire_id)
            ->when($this->filterStatut, fn($q) => $q->where('statut', $this->filterStatut))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.owner.payments.index', compact('payments'));
    }
}
