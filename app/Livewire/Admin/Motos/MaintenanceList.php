<?php

namespace App\Livewire\Admin\Motos;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Moto;
use App\Models\Maintenance;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class MaintenanceList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatut = '';
    public string $filterMaintenance = ''; // all, with_maintenance, without_maintenance, urgent
    public int $perPage = 15;

    protected $queryString = ['search', 'filterStatut', 'filterMaintenance'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterStatut', 'filterMaintenance']);
        $this->resetPage();
    }

    public function getMotosQuery()
    {
        return Moto::with(['proprietaire.user', 'motard.user', 'maintenances' => function($q) {
                $q->orderBy('prochain_entretien', 'asc');
            }])
            ->when($this->search, function ($q) {
                $q->where(function($q2) {
                    $q2->where('plaque_immatriculation', 'like', '%' . $this->search . '%')
                       ->orWhere('numero_matricule', 'like', '%' . $this->search . '%')
                       ->orWhereHas('proprietaire.user', function ($q3) {
                           $q3->where('name', 'like', '%' . $this->search . '%');
                       })
                       ->orWhereHas('motard.user', function ($q3) {
                           $q3->where('name', 'like', '%' . $this->search . '%');
                       });
                });
            })
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->when($this->filterMaintenance === 'with_maintenance', function ($q) {
                $q->whereHas('maintenances', function($q2) {
                    $q2->whereNotNull('prochain_entretien');
                });
            })
            ->when($this->filterMaintenance === 'without_maintenance', function ($q) {
                $q->whereDoesntHave('maintenances', function($q2) {
                    $q2->whereNotNull('prochain_entretien');
                });
            })
            ->when($this->filterMaintenance === 'urgent', function ($q) {
                $q->whereHas('maintenances', function($q2) {
                    $q2->whereNotNull('prochain_entretien')
                       ->where('prochain_entretien', '<=', Carbon::today()->addDays(7));
                });
            })
            ->orderBy('plaque_immatriculation');
    }

    public function exportPdf()
    {
        $motos = $this->getMotosQuery()->get();

        // Préparer les données avec la prochaine maintenance
        $motosData = $motos->map(function($moto) {
            $prochaineMaintenance = $moto->maintenances
                ->whereNotNull('prochain_entretien')
                ->where('prochain_entretien', '>=', Carbon::today())
                ->sortBy('prochain_entretien')
                ->first();

            $derniereMaintenance = $moto->maintenances
                ->whereNotNull('date_intervention')
                ->sortByDesc('date_intervention')
                ->first();

            return [
                'moto' => $moto,
                'prochaine_maintenance' => $prochaineMaintenance,
                'derniere_maintenance' => $derniereMaintenance,
                'jours_restants' => $prochaineMaintenance
                    ? Carbon::today()->diffInDays(Carbon::parse($prochaineMaintenance->prochain_entretien), false)
                    : null,
            ];
        });

        // Statistiques
        $stats = [
            'total' => $motos->count(),
            'avec_maintenance_prevue' => $motosData->filter(fn($m) => $m['prochaine_maintenance'])->count(),
            'urgentes' => $motosData->filter(fn($m) => $m['jours_restants'] !== null && $m['jours_restants'] <= 7)->count(),
            'en_retard' => $motosData->filter(fn($m) => $m['jours_restants'] !== null && $m['jours_restants'] < 0)->count(),
        ];

        $pdf = Pdf::loadView('pdf.motos-maintenance', [
            'motos' => $motosData,
            'stats' => $stats,
            'date' => Carbon::now()->format('d/m/Y H:i'),
            'filtres' => [
                'search' => $this->search,
                'statut' => $this->filterStatut,
                'maintenance' => $this->filterMaintenance,
            ],
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'motos_maintenance_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function exportCsv()
    {
        $motos = $this->getMotosQuery()->get();

        $filename = 'motos_maintenance_' . Carbon::now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function() use ($motos) {
            $handle = fopen('php://output', 'w');

            // En-têtes
            fputcsv($handle, [
                'Plaque',
                'Matricule',
                'Propriétaire',
                'Motard',
                'Statut',
                'Dernière Maintenance',
                'Type Dernière',
                'Prochaine Maintenance',
                'Jours Restants',
                'Tarif Journalier'
            ]);

            foreach ($motos as $moto) {
                $prochaineMaintenance = $moto->maintenances
                    ->whereNotNull('prochain_entretien')
                    ->where('prochain_entretien', '>=', Carbon::today())
                    ->sortBy('prochain_entretien')
                    ->first();

                $derniereMaintenance = $moto->maintenances
                    ->whereNotNull('date_intervention')
                    ->sortByDesc('date_intervention')
                    ->first();

                $joursRestants = $prochaineMaintenance
                    ? Carbon::today()->diffInDays(Carbon::parse($prochaineMaintenance->prochain_entretien), false)
                    : null;

                fputcsv($handle, [
                    $moto->plaque_immatriculation,
                    $moto->numero_matricule ?? 'N/A',
                    $moto->proprietaire->user->name ?? 'N/A',
                    $moto->motard->user->name ?? 'Non assigné',
                    ucfirst($moto->statut),
                    $derniereMaintenance?->date_intervention?->format('d/m/Y') ?? 'Aucune',
                    $derniereMaintenance ? ucfirst($derniereMaintenance->type) : '-',
                    $prochaineMaintenance?->prochain_entretien?->format('d/m/Y') ?? 'Non planifiée',
                    $joursRestants !== null ? $joursRestants . ' jours' : '-',
                    number_format($moto->montant_journalier_attendu ?? 0) . ' FC',
                ]);
            }

            fclose($handle);
        }, $filename);
    }

    public function render()
    {
        $motos = $this->getMotosQuery()->paginate($this->perPage);

        // Calculer les stats
        $allMotos = Moto::with('maintenances')->get();
        $stats = [
            'total' => $allMotos->count(),
            'avec_maintenance' => $allMotos->filter(function($m) {
                return $m->maintenances->whereNotNull('prochain_entretien')
                    ->where('prochain_entretien', '>=', Carbon::today())->count() > 0;
            })->count(),
            'urgentes' => $allMotos->filter(function($m) {
                $prochaine = $m->maintenances->whereNotNull('prochain_entretien')
                    ->where('prochain_entretien', '>=', Carbon::today())
                    ->sortBy('prochain_entretien')->first();
                if (!$prochaine) return false;
                return Carbon::parse($prochaine->prochain_entretien)->diffInDays(Carbon::today(), false) <= 7;
            })->count(),
            'en_retard' => $allMotos->filter(function($m) {
                $prochaine = $m->maintenances->whereNotNull('prochain_entretien')
                    ->sortBy('prochain_entretien')->first();
                if (!$prochaine) return false;
                return Carbon::parse($prochaine->prochain_entretien)->isPast();
            })->count(),
        ];

        return view('livewire.admin.motos.maintenance-list', compact('motos', 'stats'));
    }
}

