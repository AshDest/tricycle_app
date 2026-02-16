<?php

namespace App\Livewire\Supervisor\Validation;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Versement;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterType = '';
    public $filterStatut = '';
    public $perPage = 15;

    // Stats
    public $versementsEnAttente = 0;

    protected $queryString = ['search', 'filterType', 'filterStatut'];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterType() { $this->resetPage(); }
    public function updatingFilterStatut() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'filterType', 'filterStatut']);
        $this->resetPage();
    }

    public function validerVersement($versementId)
    {
        $versement = Versement::findOrFail($versementId);
        $versement->update([
            'statut' => 'payé',
            'valide_par' => auth()->id(),
            'valide_at' => now(),
            'notes_validation' => 'Validé par OKAMI',
        ]);
        session()->flash('success', 'Versement validé avec succès.');
    }

    public function invaliderVersement($versementId)
    {
        $versement = Versement::findOrFail($versementId);
        $versement->update([
            'statut' => 'invalide',
            'valide_par' => auth()->id(),
            'valide_at' => now(),
            'notes_validation' => 'Invalidé par OKAMI',
        ]);
        session()->flash('success', 'Versement invalidé.');
    }

    public function render()
    {
        // Versements douteux : non confirmés, montants incorrects, litigieux
        $query = Versement::with(['motard.user', 'moto', 'caissier'])
            ->where(function($q) {
                $q->whereIn('statut', ['en_attente_validation', 'litigieux', 'partiellement_payé'])
                  ->orWhereRaw('montant != montant_attendu');
            })
            ->when($this->search, function ($q) {
                $q->whereHas('motard.user', fn($q2) => $q2->where('name', 'like', '%'.$this->search.'%'))
                  ->orWhereHas('moto', fn($q2) => $q2->where('plaque_immatriculation', 'like', '%'.$this->search.'%'));
            })
            ->when($this->filterStatut, fn($q) => $q->where('statut', $this->filterStatut));

        $this->versementsEnAttente = (clone $query)->count();

        $versements = $query->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.supervisor.validation.index', compact('versements'));
    }
}
