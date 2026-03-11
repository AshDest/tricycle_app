<?php

namespace App\Livewire\Admin\Recompenses;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Recompense;
use App\Models\Motard;

/**
 * Liste et gestion des récompenses
 */
#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $statutFilter = '';
    public $typeFilter = '';
    public $categorieFilter = '';

    // Modal de remise
    public $showRemiseModal = false;
    public $recompenseId;
    public $notesRemise = '';

    protected $queryString = ['search', 'statutFilter', 'typeFilter'];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    /**
     * Marquer une récompense comme remise
     */
    public function ouvrirModalRemise($id)
    {
        $this->recompenseId = $id;
        $this->notesRemise = '';
        $this->showRemiseModal = true;
    }

    public function confirmerRemise()
    {
        $recompense = Recompense::findOrFail($this->recompenseId);
        $recompense->update([
            'statut' => 'remis',
            'date_remise' => now(),
            'remis_par' => auth()->id(),
            'notes' => $this->notesRemise ?: $recompense->notes,
        ]);

        $this->showRemiseModal = false;
        session()->flash('success', 'Récompense marquée comme remise.');
    }

    /**
     * Annuler une récompense
     */
    public function annuler($id)
    {
        $recompense = Recompense::findOrFail($id);
        $recompense->update(['statut' => 'annule']);
        session()->flash('success', 'Récompense annulée.');
    }

    /**
     * Supprimer une récompense
     */
    public function supprimer($id)
    {
        Recompense::findOrFail($id)->delete();
        session()->flash('success', 'Récompense supprimée.');
    }

    public function render()
    {
        $query = Recompense::with(['motard.user', 'remisPar']);

        if ($this->search) {
            $query->whereHas('motard.user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statutFilter) {
            $query->where('statut', $this->statutFilter);
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        if ($this->categorieFilter) {
            $query->where('categorie', $this->categorieFilter);
        }

        $recompenses = $query->orderByDesc('created_at')->paginate(15);

        // Statistiques
        $stats = [
            'total' => Recompense::count(),
            'attribuees' => Recompense::where('statut', 'attribue')->count(),
            'remises' => Recompense::where('statut', 'remis')->count(),
            'montant_primes' => Recompense::whereNotNull('montant_prime')->sum('montant_prime'),
        ];

        return view('livewire.admin.recompenses.index', [
            'recompenses' => $recompenses,
            'stats' => $stats,
            'types' => Recompense::getTypes(),
            'categories' => Recompense::getCategories(),
            'statuts' => Recompense::getStatuts(),
        ]);
    }
}

