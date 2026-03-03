<?php

namespace App\Livewire\Admin\Cleaners;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Cleaner;
use App\Models\Lavage;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filterStatut = '';
    public $perPage = 15;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatut' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleStatut($cleanerId)
    {
        $cleaner = Cleaner::findOrFail($cleanerId);
        $cleaner->update(['is_active' => !$cleaner->is_active]);
        session()->flash('success', 'Statut mis à jour avec succès.');
    }

    public function supprimer($cleanerId)
    {
        $cleaner = Cleaner::findOrFail($cleanerId);

        // Vérifier s'il a des lavages
        if ($cleaner->lavages()->count() > 0) {
            session()->flash('error', 'Ce laveur a des lavages enregistrés et ne peut pas être supprimé.');
            return;
        }

        $cleaner->user()->delete();
        $cleaner->delete();

        session()->flash('success', 'Laveur supprimé avec succès.');
    }

    public function render()
    {
        $cleaners = Cleaner::with('user')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('identifiant', 'like', '%' . $this->search . '%')
                      ->orWhere('zone', 'like', '%' . $this->search . '%')
                      ->orWhereHas('user', function ($q2) {
                          $q2->where('name', 'like', '%' . $this->search . '%')
                             ->orWhere('email', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->filterStatut === 'actif', function ($query) {
                $query->where('is_active', true);
            })
            ->when($this->filterStatut === 'inactif', function ($query) {
                $query->where('is_active', false);
            })
            ->withCount('lavages')
            ->withSum(['lavages as total_recettes' => function ($query) {
                $query->where('statut_paiement', 'payé');
            }], 'part_cleaner')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        // Statistiques globales
        $stats = [
            'total' => Cleaner::count(),
            'actifs' => Cleaner::where('is_active', true)->count(),
            'lavages_jour' => Lavage::whereDate('date_lavage', today())->count(),
            'ca_jour' => Lavage::whereDate('date_lavage', today())->where('statut_paiement', 'payé')->sum('prix_final'),
            'part_okami_jour' => Lavage::whereDate('date_lavage', today())->where('statut_paiement', 'payé')->sum('part_okami'),
        ];

        return view('livewire.admin.cleaners.index', [
            'cleaners' => $cleaners,
            'stats' => $stats,
        ]);
    }
}

