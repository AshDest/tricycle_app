<?php

namespace App\Livewire\Admin\Collecteurs;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Collecteur;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterZone = '';
    public $filterStatut = '';
    public $perPage = 15;

    protected $queryString = ['search', 'filterZone', 'filterStatut'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterZone()
    {
        $this->resetPage();
    }

    public function updatingFilterStatut()
    {
        $this->resetPage();
    }

    public function toggleActive(Collecteur $collecteur)
    {
        $collecteur->update(['is_active' => !$collecteur->is_active]);
    }

    public function delete(Collecteur $collecteur)
    {
        // Soft delete le collecteur
        $collecteur->delete();

        // Optionnel: désactiver l'utilisateur associé
        if ($collecteur->user) {
            $collecteur->user->update(['is_active' => false]);
        }

        session()->flash('success', 'Collecteur supprimé avec succès.');
    }

    protected function getBaseQuery()
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
            ->when($this->filterStatut !== '', function ($q) {
                if ($this->filterStatut === 'actif') {
                    $q->where('is_active', true);
                } elseif ($this->filterStatut === 'inactif') {
                    $q->where('is_active', false);
                }
            })
            ->orderBy('created_at', 'desc');
    }

    public function exportPdf()
    {
        $collecteurs = $this->getBaseQuery()->get();

        $stats = [
            'total' => $collecteurs->count(),
            'actifs' => $collecteurs->where('is_active', true)->count(),
            'zones' => $collecteurs->pluck('zone_affectation')->unique()->filter()->count(),
        ];

        $pdf = Pdf::loadView('pdf.lists.collecteurs', [
            'collecteurs' => $collecteurs,
            'stats' => $stats,
            'title' => 'Liste des Collecteurs',
            'subtitle' => 'Exporté le ' . Carbon::now()->format('d/m/Y à H:i'),
            'filtres' => [
                'search' => $this->search,
                'zone' => $this->filterZone,
            ],
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'collecteurs_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function render()
    {
        $collecteurs = $this->getBaseQuery()->paginate($this->perPage);
        $zones = Collecteur::distinct()->pluck('zone_affectation')->filter();

        return view('livewire.admin.collecteurs.index', compact('collecteurs', 'zones'));
    }
}
