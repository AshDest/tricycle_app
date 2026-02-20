<?php

namespace App\Livewire\Supervisor\Motos;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Moto;
use App\Models\Proprietaire;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatut = '';
    public string $filterProprietaire = '';
    public string $filterContrat = '';
    public string $dateDebut = '';
    public string $dateFin = '';
    public int $perPage = 15;

    public ?int $confirmingDelete = null;

    // Pour le renouvellement de contrat
    public ?int $renewingContrat = null;
    public string $newContratDebut = '';
    public string $newContratFin = '';
    public string $newContratNotes = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatut' => ['except' => ''],
        'filterProprietaire' => ['except' => ''],
        'filterContrat' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterStatut', 'filterProprietaire', 'filterContrat', 'dateDebut', 'dateFin']);
        $this->resetPage();
    }

    public function toggleStatut(int $id)
    {
        $moto = Moto::findOrFail($id);

        // Vérifier si le contrat est actif avant de permettre l'activation
        if ($moto->statut !== 'actif' && !$moto->contrat_actif) {
            session()->flash('error', 'Impossible d\'activer cette moto : le contrat n\'est pas actif ou n\'est pas défini.');
            return;
        }

        // Les statuts valides sont: actif, suspendu, maintenance
        $newStatut = $moto->statut === 'actif' ? 'suspendu' : 'actif';
        $moto->update(['statut' => $newStatut]);
        session()->flash('success', 'Statut de la moto mis à jour.');
    }

    // ========== RENOUVELLEMENT DE CONTRAT ==========

    public function openRenewContrat(int $id)
    {
        $moto = Moto::findOrFail($id);
        $this->renewingContrat = $id;

        // Pré-remplir avec les dates suivantes logiques
        if ($moto->contrat_fin) {
            $this->newContratDebut = $moto->contrat_fin->addDay()->format('Y-m-d');
        } else {
            $this->newContratDebut = Carbon::today()->format('Y-m-d');
        }
        $this->newContratFin = Carbon::parse($this->newContratDebut)->addYear()->format('Y-m-d');
        $this->newContratNotes = '';
    }

    public function cancelRenewContrat()
    {
        $this->renewingContrat = null;
        $this->newContratDebut = '';
        $this->newContratFin = '';
        $this->newContratNotes = '';
    }

    public function renewContrat()
    {
        $this->validate([
            'newContratDebut' => 'required|date',
            'newContratFin' => 'required|date|after:newContratDebut',
        ], [
            'newContratDebut.required' => 'La date de début est obligatoire.',
            'newContratFin.required' => 'La date de fin est obligatoire.',
            'newContratFin.after' => 'La date de fin doit être après la date de début.',
        ]);

        $moto = Moto::findOrFail($this->renewingContrat);
        $moto->renouvelerContrat(
            Carbon::parse($this->newContratDebut),
            Carbon::parse($this->newContratFin),
            $this->newContratNotes ?: null
        );

        session()->flash('success', 'Contrat renouvelé avec succès.');
        $this->cancelRenewContrat();
    }

    // ========== FIN RENOUVELLEMENT ==========

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
        $moto = Moto::findOrFail($id);

        if ($moto->motardActuel) {
            session()->flash('error', 'Impossible de supprimer cette moto car elle est assignée à un motard.');
            $this->confirmingDelete = null;
            return;
        }

        $moto->delete();
        session()->flash('success', 'Moto supprimée avec succès.');
        $this->confirmingDelete = null;
    }

    public function export(string $format = 'csv')
    {
        $filename = 'motos_' . now()->format('Y-m-d_His');

        return response()->streamDownload(function () {
            $this->exportCsv($this->getFilteredQuery()->get());
        }, $filename . '.csv');
    }

    public function exportPdf()
    {
        $motos = $this->getFilteredQuery()->get();

        $stats = [
            'total' => $motos->count(),
            'actives' => $motos->where('statut', 'actif')->count(),
            'inactives' => $motos->where('statut', 'inactif')->count(),
            'en_maintenance' => $motos->where('statut', 'en_maintenance')->count(),
        ];

        $pdf = Pdf::loadView('pdf.lists.motos', [
            'motos' => $motos,
            'stats' => $stats,
            'title' => 'Liste des Motos - OKAMI',
            'subtitle' => 'Exporté le ' . Carbon::now()->format('d/m/Y à H:i'),
            'filtres' => [
                'search' => $this->search,
                'statut' => $this->filterStatut,
            ],
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'motos_okami_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    protected function exportCsv($motos)
    {
        $handle = fopen('php://output', 'w');

        fputcsv($handle, [
            'ID', 'Plaque', 'Châssis', 'Propriétaire', 'Motard Assigné',
            'Statut', 'Contrat Début', 'Contrat Fin', 'Statut Contrat', 'Date Création'
        ]);

        foreach ($motos as $moto) {
            fputcsv($handle, [
                $moto->id,
                $moto->plaque_immatriculation ?? 'N/A',
                $moto->numero_chassis ?? 'N/A',
                $moto->proprietaire->user->name ?? 'N/A',
                $moto->motardActuel->user->name ?? 'Aucun',
                $moto->statut ?? 'N/A',
                $moto->contrat_debut?->format('d/m/Y') ?? 'N/A',
                $moto->contrat_fin?->format('d/m/Y') ?? 'N/A',
                $moto->statut_contrat ?? 'N/A',
                $moto->created_at->format('d/m/Y H:i'),
            ]);
        }

        fclose($handle);
    }

    protected function getFilteredQuery()
    {
        return Moto::with(['proprietaire.user', 'motardActuel.user'])
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('plaque_immatriculation', 'like', '%' . $this->search . '%')
                          ->orWhere('numero_chassis', 'like', '%' . $this->search . '%')
                          ->orWhere('contrat_numero', 'like', '%' . $this->search . '%')
                          ->orWhereHas('proprietaire.user', function ($q2) {
                              $q2->where('name', 'like', '%' . $this->search . '%');
                          });
                });
            })
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->when($this->filterProprietaire, function ($q) {
                $q->where('proprietaire_id', $this->filterProprietaire);
            })
            ->when($this->filterContrat, function ($q) {
                switch ($this->filterContrat) {
                    case 'actif':
                        $q->contratActif();
                        break;
                    case 'expire':
                        $q->contratExpire();
                        break;
                    case 'bientot_expire':
                        $q->contratBientotExpire(30);
                        break;
                    case 'sans_contrat':
                        $q->sansContrat();
                        break;
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
        $motos = $this->getFilteredQuery()->paginate($this->perPage);
        $proprietaires = Proprietaire::with('user')->get();

        $stats = [
            'total' => Moto::count(),
            'actives' => Moto::where('statut', 'actif')->count(),
            'contratsActifs' => Moto::contratActif()->count(),
            'contratsExpires' => Moto::contratExpire()->count(),
            'contratsBientotExpires' => Moto::contratBientotExpire(30)->count(),
            'sansContrat' => Moto::sansContrat()->count(),
        ];

        return view('livewire.supervisor.motos.index', compact('motos', 'proprietaires', 'stats'));
    }
}

