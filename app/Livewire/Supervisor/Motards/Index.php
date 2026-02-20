<?php

namespace App\Livewire\Supervisor\Motards;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Motard;
use App\Models\Zone;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterZone = '';
    public string $filterStatut = '';
    public string $dateDebut = '';
    public string $dateFin = '';
    public int $perPage = 15;

    // Pour la confirmation de suppression
    public ?int $confirmingDelete = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterZone' => ['except' => ''],
        'filterStatut' => ['except' => ''],
    ];

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

    public function resetFilters()
    {
        $this->reset(['search', 'filterZone', 'filterStatut', 'dateDebut', 'dateFin']);
        $this->resetPage();
    }

    public function toggleActive(int $id)
    {
        $motard = Motard::findOrFail($id);
        $motard->update(['is_active' => !$motard->is_active]);
        session()->flash('success', 'Statut du motard mis à jour.');
    }

    public function confirmDelete(int $id)
    {
        $this->confirmingDelete = $id;
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = null;
    }

    public function delete(int $id)
    {
        $motard = Motard::findOrFail($id);

        // Vérifier s'il a des versements en cours
        if ($motard->versements()->where('statut', 'en_attente')->exists()) {
            session()->flash('error', 'Impossible de supprimer ce motard car il a des versements en attente.');
            $this->confirmingDelete = null;
            return;
        }

        $motard->delete();
        session()->flash('success', 'Motard supprimé avec succès.');
        $this->confirmingDelete = null;
    }

    public function export(string $format = 'xlsx')
    {
        $filename = 'motards_' . now()->format('Y-m-d_His');

        $query = $this->getFilteredQuery();

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($query) {
                $this->exportCsv($query->get());
            }, $filename . '.csv');
        }

        // Export Excel via Maatwebsite si disponible, sinon CSV
        return response()->streamDownload(function () use ($query) {
            $this->exportCsv($query->get());
        }, $filename . '.csv');
    }

    public function exportPdf()
    {
        $motards = $this->getFilteredQuery()->get();

        $stats = [
            'total' => $motards->count(),
            'actifs' => $motards->where('is_active', true)->count(),
            'inactifs' => $motards->where('is_active', false)->count(),
            'zones' => $motards->pluck('zone_affectation')->unique()->filter()->count(),
        ];

        $pdf = Pdf::loadView('pdf.lists.motards', [
            'motards' => $motards,
            'stats' => $stats,
            'title' => 'Liste des Motards - OKAMI',
            'subtitle' => 'Exporté le ' . Carbon::now()->format('d/m/Y à H:i'),
            'filtres' => [
                'search' => $this->search,
                'zone' => $this->filterZone,
                'statut' => $this->filterStatut,
            ],
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'motards_okami_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    protected function exportCsv($motards)
    {
        $handle = fopen('php://output', 'w');

        // En-têtes
        fputcsv($handle, [
            'ID', 'Nom', 'Email', 'Téléphone', 'Numéro Identifiant',
            'Zone', 'Moto Assignée', 'Statut', 'Date Création'
        ]);

        foreach ($motards as $motard) {
            fputcsv($handle, [
                $motard->id,
                $motard->user->name ?? 'N/A',
                $motard->user->email ?? 'N/A',
                $motard->telephone ?? 'N/A',
                $motard->numero_identifiant ?? 'N/A',
                $motard->zone_affectation ?? 'N/A',
                $motard->motoActuelle->plaque_immatriculation ?? 'Aucune',
                $motard->is_active ? 'Actif' : 'Inactif',
                $motard->created_at->format('d/m/Y H:i'),
            ]);
        }

        fclose($handle);
    }

    protected function getFilteredQuery()
    {
        return Motard::with(['user', 'motoActuelle'])
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->whereHas('user', function ($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%')
                           ->orWhere('email', 'like', '%' . $this->search . '%');
                    })->orWhere('numero_identifiant', 'like', '%' . $this->search . '%')
                      ->orWhere('telephone', 'like', '%' . $this->search . '%');
                });
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
            ->when($this->dateDebut, function ($q) {
                $q->whereDate('created_at', '>=', $this->dateDebut);
            })
            ->when($this->dateFin, function ($q) {
                $q->whereDate('created_at', '<=', $this->dateFin);
            })
            ->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $motards = $this->getFilteredQuery()->paginate($this->perPage);
        $zones = Zone::orderBy('nom')->pluck('nom', 'id');
        $zonesAffectation = Motard::distinct()->whereNotNull('zone_affectation')->pluck('zone_affectation');

        // Statistiques
        $stats = [
            'total' => Motard::count(),
            'actifs' => Motard::where('is_active', true)->count(),
            'inactifs' => Motard::where('is_active', false)->count(),
            'avecMoto' => Motard::whereHas('motoActuelle')->count(),
        ];

        return view('livewire.supervisor.motards.index', compact('motards', 'zones', 'zonesAffectation', 'stats'));
    }
}
