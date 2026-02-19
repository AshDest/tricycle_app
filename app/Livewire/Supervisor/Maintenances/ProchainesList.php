<?php

namespace App\Livewire\Supervisor\Maintenances;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Moto;
use App\Models\Maintenance;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class ProchainesList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatut = '';
    public string $filterUrgence = ''; // all, urgent, en_retard
    public int $perPage = 15;

    protected $queryString = ['search', 'filterStatut', 'filterUrgence'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterStatut', 'filterUrgence']);
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
            ->when($this->filterUrgence === 'urgent', function ($q) {
                $q->whereHas('maintenances', function($q2) {
                    $q2->whereNotNull('prochain_entretien')
                       ->where('prochain_entretien', '<=', Carbon::today()->addDays(7))
                       ->where('prochain_entretien', '>=', Carbon::today());
                });
            })
            ->when($this->filterUrgence === 'en_retard', function ($q) {
                $q->whereHas('maintenances', function($q2) {
                    $q2->whereNotNull('prochain_entretien')
                       ->where('prochain_entretien', '<', Carbon::today());
                });
            })
            ->when($this->filterUrgence === 'planifie', function ($q) {
                $q->whereHas('maintenances', function($q2) {
                    $q2->whereNotNull('prochain_entretien');
                });
            })
            ->orderBy('plaque_immatriculation');
    }

    protected function prepareMotosData($motos)
    {
        return $motos->map(function($moto) {
            $prochaineMaintenance = $moto->maintenances
                ->whereNotNull('prochain_entretien')
                ->sortBy('prochain_entretien')
                ->first();

            $derniereMaintenance = $moto->maintenances
                ->whereNotNull('date_intervention')
                ->sortByDesc('date_intervention')
                ->first();

            $joursRestants = null;
            if ($prochaineMaintenance && $prochaineMaintenance->prochain_entretien) {
                $joursRestants = Carbon::today()->diffInDays(Carbon::parse($prochaineMaintenance->prochain_entretien), false);
            }

            return [
                'moto' => $moto,
                'prochaine_maintenance' => $prochaineMaintenance,
                'derniere_maintenance' => $derniereMaintenance,
                'jours_restants' => $joursRestants,
            ];
        });
    }

    public function exportPdf()
    {
        $motos = $this->getMotosQuery()->get();
        $motosData = $this->prepareMotosData($motos);

        // Statistiques
        $stats = [
            'total' => $motos->count(),
            'avec_maintenance_prevue' => $motosData->filter(fn($m) => $m['prochaine_maintenance'])->count(),
            'urgentes' => $motosData->filter(fn($m) => $m['jours_restants'] !== null && $m['jours_restants'] >= 0 && $m['jours_restants'] <= 7)->count(),
            'en_retard' => $motosData->filter(fn($m) => $m['jours_restants'] !== null && $m['jours_restants'] < 0)->count(),
        ];

        $pdf = Pdf::loadView('pdf.motos-maintenance', [
            'motos' => $motosData,
            'stats' => $stats,
            'date' => Carbon::now()->format('d/m/Y H:i'),
            'filtres' => [
                'search' => $this->search,
                'statut' => $this->filterStatut,
                'maintenance' => $this->filterUrgence,
            ],
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'motos_prochaines_maintenances_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel()
    {
        $motos = $this->getMotosQuery()->get();
        $motosData = $this->prepareMotosData($motos);

        $filename = 'motos_prochaines_maintenances_' . Carbon::now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function() use ($motosData) {
            $handle = fopen('php://output', 'w');

            // BOM pour Excel UTF-8
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // En-têtes
            fputcsv($handle, [
                'Plaque',
                'Matricule',
                'Propriétaire',
                'Motard',
                'Statut Moto',
                'Dernière Maintenance',
                'Type Dernière',
                'Prochaine Maintenance',
                'Jours Restants',
                'Urgence',
                'Tarif Journalier (FC)'
            ], ';');

            foreach ($motosData as $item) {
                $urgence = '-';
                if ($item['jours_restants'] !== null) {
                    if ($item['jours_restants'] < 0) {
                        $urgence = 'EN RETARD';
                    } elseif ($item['jours_restants'] <= 7) {
                        $urgence = 'URGENT';
                    } else {
                        $urgence = 'Normal';
                    }
                }

                fputcsv($handle, [
                    $item['moto']->plaque_immatriculation,
                    $item['moto']->numero_matricule ?? '-',
                    $item['moto']->proprietaire->user->name ?? 'N/A',
                    $item['moto']->motard->user->name ?? 'Non assigné',
                    ucfirst($item['moto']->statut),
                    $item['derniere_maintenance']?->date_intervention?->format('d/m/Y') ?? 'Aucune',
                    $item['derniere_maintenance'] ? ucfirst($item['derniere_maintenance']->type) : '-',
                    $item['prochaine_maintenance']?->prochain_entretien?->format('d/m/Y') ?? 'Non planifiée',
                    $item['jours_restants'] !== null ? $item['jours_restants'] : '-',
                    $urgence,
                    number_format($item['moto']->montant_journalier_attendu ?? 0, 0, ',', ' '),
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function render()
    {
        $motos = $this->getMotosQuery()->paginate($this->perPage);

        // Calculer les stats
        $allMotos = Moto::with('maintenances')->get();
        $stats = [
            'total' => $allMotos->count(),
            'avec_maintenance' => $allMotos->filter(function($m) {
                return $m->maintenances->whereNotNull('prochain_entretien')->count() > 0;
            })->count(),
            'urgentes' => $allMotos->filter(function($m) {
                $prochaine = $m->maintenances->whereNotNull('prochain_entretien')
                    ->sortBy('prochain_entretien')->first();
                if (!$prochaine) return false;
                $jours = Carbon::today()->diffInDays(Carbon::parse($prochaine->prochain_entretien), false);
                return $jours >= 0 && $jours <= 7;
            })->count(),
            'en_retard' => $allMotos->filter(function($m) {
                $prochaine = $m->maintenances->whereNotNull('prochain_entretien')
                    ->sortBy('prochain_entretien')->first();
                if (!$prochaine) return false;
                return Carbon::parse($prochaine->prochain_entretien)->isPast();
            })->count(),
        ];

        return view('livewire.supervisor.maintenances.prochaines-list', compact('motos', 'stats'));
    }
}

