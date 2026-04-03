<?php

namespace App\Livewire\Admin\Collecteurs;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Collecteur;
use App\Models\Payment;
use App\Models\TransactionMobile;
use App\Models\Collecte;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

#[Layout('components.dashlite-layout')]
class SoldesOverview extends Component
{
    use WithPagination;

    public $search = '';
    public $filterZone = '';
    public $dateDebut = '';
    public $dateFin = '';
    public int $perPage = 15;

    // Totaux globaux
    public $totalSoldeCaisse = 0;
    public $totalPartProprietaire = 0;
    public $totalPartOkami = 0;
    public $totalPaiementsPeriode = 0;

    public function mount()
    {
        $this->dateDebut = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateFin = Carbon::now()->format('Y-m-d');
        $this->computeTotaux();
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterZone() { $this->resetPage(); }

    public function updated($property)
    {
        if (in_array($property, ['dateDebut', 'dateFin', 'filterZone', 'search'])) {
            $this->computeTotaux();
        }
    }

    private function computeTotaux()
    {
        $query = Collecteur::query()
            ->when($this->filterZone, fn($q) => $q->where('zone_affectation', $this->filterZone))
            ->when($this->search, fn($q) => $q->whereHas('user', function($q2) {
                $q2->where('name', 'like', '%' . $this->search . '%');
            }));

        $this->totalSoldeCaisse = (clone $query)->sum('solde_caisse');
        $this->totalPartProprietaire = (clone $query)->sum('solde_part_proprietaire');
        $this->totalPartOkami = (clone $query)->sum('solde_part_okami');

        // Total paiements sur la période
        $collecteurIds = (clone $query)->pluck('user_id');
        $this->totalPaiementsPeriode = Payment::whereIn('traite_par', $collecteurIds)
            ->where('statut', 'paye')
            ->whereBetween('date_paiement', [$this->dateDebut, $this->dateFin])
            ->sum('total_paye');
    }

    private function getBaseQuery()
    {
        return Collecteur::with(['user'])
            ->when($this->search, function ($q) {
                $q->whereHas('user', function ($q2) {
                    $q2->where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('email', 'like', '%' . $this->search . '%');
                })->orWhere('numero_identifiant', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterZone, function ($q) {
                $q->where('zone_affectation', $this->filterZone);
            })
            ->where('is_active', true)
            ->orderByDesc('solde_caisse');
    }

    private function getCollecteurDepenses($collecteurUserId)
    {
        return Payment::where('traite_par', $collecteurUserId)
            ->where('statut', 'paye')
            ->whereBetween('date_paiement', [$this->dateDebut, $this->dateFin])
            ->sum('total_paye');
    }

    private function getCollecteurCollectes($collecteurId)
    {
        return Collecte::whereHas('tournee', function ($q) use ($collecteurId) {
            $q->where('collecteur_id', $collecteurId);
        })
        ->where('statut', 'reussie')
        ->whereBetween('created_at', [$this->dateDebut . ' 00:00:00', $this->dateFin . ' 23:59:59'])
        ->sum('montant_collecte');
    }

    public function exportPdf()
    {
        $collecteurs = $this->getBaseQuery()->get();

        $data = $collecteurs->map(function ($c) {
            return [
                'nom' => $c->user->name ?? 'N/A',
                'identifiant' => $c->numero_identifiant ?? 'N/A',
                'zone' => $c->zone_affectation ?? 'N/A',
                'solde_caisse' => $c->solde_caisse ?? 0,
                'part_proprietaire' => $c->solde_part_proprietaire ?? 0,
                'part_okami' => $c->solde_part_okami ?? 0,
                'depenses_periode' => $this->getCollecteurDepenses($c->user_id),
                'collectes_periode' => $this->getCollecteurCollectes($c->id),
            ];
        });

        $pdf = Pdf::loadView('pdf.admin.collecteurs-soldes', [
            'collecteurs' => $data,
            'totalSoldeCaisse' => $this->totalSoldeCaisse,
            'totalPartProprietaire' => $this->totalPartProprietaire,
            'totalPartOkami' => $this->totalPartOkami,
            'totalPaiements' => $this->totalPaiementsPeriode,
            'dateDebut' => $this->dateDebut,
            'dateFin' => $this->dateFin,
            'title' => 'Soldes & Dépenses des Collecteurs',
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'collecteurs_soldes_' . now()->format('Y-m-d') . '.pdf'
        );
    }

    public function render()
    {
        $collecteurs = $this->getBaseQuery()->paginate($this->perPage);
        $zones = Collecteur::distinct()->pluck('zone_affectation')->filter();

        // Enrichir chaque collecteur avec ses dépenses de la période
        $collecteursAvecDepenses = $collecteurs->through(function ($c) {
            $c->depenses_periode = $this->getCollecteurDepenses($c->user_id);
            $c->collectes_periode = $this->getCollecteurCollectes($c->id);
            return $c;
        });

        return view('livewire.admin.collecteurs.soldes-overview', [
            'collecteurs' => $collecteursAvecDepenses,
            'zones' => $zones,
        ]);
    }
}

