<?php

namespace App\Livewire\Collector\Tournee;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Tournee;
use App\Models\Caissier;
use App\Models\Collecte;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $filterStatut = '';
    public $filterDate = '';

    // Stats
    public $tourneesAConfirmer = 0;
    public $tourneesEnCours = 0;
    public $tourneesTerminees = 0;

    protected $queryString = ['filterStatut', 'filterDate'];

    public function updatingFilterStatut() { $this->resetPage(); }
    public function updatingFilterDate() { $this->resetPage(); }

    /**
     * Confirmer une tournée planifiée
     */
    public function confirmerTournee($tourneeId)
    {
        $collecteur = auth()->user()->collecteur;
        $tournee = Tournee::where('id', $tourneeId)
            ->where('collecteur_id', $collecteur->id)
            ->where('statut', 'planifiee')
            ->firstOrFail();

        $tournee->update([
            'statut' => 'confirmee',
            'presence_confirmee' => true,
            'presence_confirmee_at' => now(),
        ]);

        session()->flash('success', 'Tournée confirmée avec succès. Les caissiers en seront informés.');
    }

    /**
     * Démarrer une tournée confirmée
     */
    public function demarrerTournee($tourneeId)
    {
        $collecteur = auth()->user()->collecteur;
        $tournee = Tournee::where('id', $tourneeId)
            ->where('collecteur_id', $collecteur->id)
            ->where('statut', 'confirmee')
            ->firstOrFail();

        $tournee->update([
            'statut' => 'en_cours',
            'heure_debut_reelle' => now(),
        ]);

        session()->flash('success', 'Tournée démarrée. Bonne collecte !');
    }

    /**
     * Terminer une tournée en cours
     */
    public function terminerTournee($tourneeId)
    {
        $collecteur = auth()->user()->collecteur;
        $tournee = Tournee::where('id', $tourneeId)
            ->where('collecteur_id', $collecteur->id)
            ->where('statut', 'en_cours')
            ->with('collectes')
            ->firstOrFail();

        // Calculer les totaux
        $totalEncaisse = $tournee->collectes->sum('montant_collecte');
        $totalAttendu = $tournee->collectes->sum('montant_attendu');

        $tournee->update([
            'statut' => 'terminee',
            'heure_fin_reelle' => now(),
            'total_encaisse' => $totalEncaisse,
            'total_attendu' => $totalAttendu,
            'ecart_total' => $totalEncaisse - $totalAttendu,
        ]);

        session()->flash('success', 'Tournée terminée. Total encaissé: ' . number_format($totalEncaisse) . ' FC');
    }

    public function render()
    {
        $collecteur = auth()->user()->collecteur;
        $collecteurId = $collecteur?->id;

        // Stats
        $this->tourneesAConfirmer = Tournee::where('collecteur_id', $collecteurId)
            ->where('statut', 'planifiee')
            ->whereDate('date', '>=', Carbon::today())
            ->count();

        $this->tourneesEnCours = Tournee::where('collecteur_id', $collecteurId)
            ->whereIn('statut', ['confirmee', 'en_cours'])
            ->count();

        $this->tourneesTerminees = Tournee::where('collecteur_id', $collecteurId)
            ->where('statut', 'terminee')
            ->whereMonth('date', Carbon::now()->month)
            ->count();

        // Query des tournées
        $tournees = Tournee::where('collecteur_id', $collecteurId)
            ->withCount('collectes')
            ->withSum('collectes', 'montant_attendu')
            ->withSum('collectes', 'montant_collecte')
            ->when($this->filterStatut, fn($q) => $q->where('statut', $this->filterStatut))
            ->when($this->filterDate, fn($q) => $q->whereDate('date', $this->filterDate))
            ->orderByRaw("FIELD(statut, 'en_cours', 'confirmee', 'planifiee', 'terminee', 'annulee')")
            ->orderBy('date', 'desc')
            ->paginate(10);

        return view('livewire.collector.tournee.index', compact('tournees'));
    }
}
