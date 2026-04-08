<?php

namespace App\Livewire\Admin\Payments;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Payment;
use App\Models\Proprietaire;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatut = '';
    public $filterMode = '';
    public $filterProprietaire = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 15;

    protected $queryString = ['search', 'filterStatut', 'filterMode', 'filterProprietaire', 'dateFrom', 'dateTo'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatut()
    {
        $this->resetPage();
    }

    public function updatingFilterMode()
    {
        $this->resetPage();
    }

    public function updatingFilterProprietaire()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterStatut', 'filterMode', 'filterProprietaire', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function approuver(int $id)
    {
        $payment = Payment::findOrFail($id);

        $payment->update([
            'statut' => 'paye',
            'date_paiement' => Carbon::now(),
            'valide_par' => auth()->id(),
            'valide_at' => Carbon::now(),
        ]);

        session()->flash('success', 'Paiement approuvé avec succès.');
    }

    public function rejeter(int $id)
    {
        $payment = Payment::findOrFail($id);

        $payment->update([
            'statut' => 'rejete',
            'valide_par' => auth()->id(),
            'valide_at' => Carbon::now(),
        ]);

        session()->flash('success', 'Paiement rejeté.');
    }

    public function delete(Payment $payment)
    {
        $payment->delete();
        session()->flash('success', 'Paiement supprimé avec succès.');
    }

    protected function getBaseQuery()
    {
        return Payment::with(['proprietaire.user', 'traitePar'])
            ->when($this->search, function ($q) {
                $q->where(function($q2) {
                    $q2->whereHas('proprietaire.user', function ($q3) {
                        $q3->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('reference_paiement', 'like', '%' . $this->search . '%')
                    ->orWhere('beneficiaire_nom', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->when($this->filterMode, function ($q) {
                $q->where('mode_paiement', $this->filterMode);
            })
            ->when($this->filterProprietaire, function ($q) {
                $q->where('proprietaire_id', $this->filterProprietaire);
            })
            ->when($this->dateFrom, function ($q) {
                $q->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($q) {
                $q->whereDate('created_at', '<=', $this->dateTo);
            })
            ->orderBy('created_at', 'desc');
    }

    public function exportPdf()
    {
        $payments = $this->getBaseQuery()->get();

        $stats = [
            'total' => $payments->count(),
            'total_montant' => $payments->sum('total_du'),
            'payes' => $payments->where('statut', 'paye')->count(),
            'en_attente' => $payments->whereIn('statut', ['en_attente', 'demande'])->count(),
        ];

        $pdf = Pdf::loadView('pdf.lists.payments', [
            'payments' => $payments,
            'stats' => $stats,
            'title' => 'Liste des Paiements Propriétaires',
            'subtitle' => 'Exporté le ' . Carbon::now()->format('d/m/Y à H:i'),
            'filtres' => [
                'search' => $this->search,
                'statut' => $this->filterStatut,
                'date_from' => $this->dateFrom,
                'date_to' => $this->dateTo,
            ],
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'payments_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function render()
    {
        $payments = $this->getBaseQuery()->paginate($this->perPage);

        $proprietaires = Proprietaire::with('user')->get();
        $totalEnAttente = Payment::whereIn('statut', ['en_attente', 'demande'])->count();
        $totalPaye = Payment::where('statut', 'paye')->sum('total_paye');
        $totalArrieres = Payment::whereIn('statut', ['en_attente', 'demande'])->sum('total_du');
        $nombrePaiements = Payment::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('livewire.admin.payments.index', compact(
            'payments',
            'proprietaires',
            'totalEnAttente',
            'totalPaye',
            'totalArrieres',
            'nombrePaiements'
        ));
    }
}
