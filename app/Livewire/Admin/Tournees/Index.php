<?php

namespace App\Livewire\Admin\Tournees;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Tournee;
use App\Models\Collecte;
use App\Models\Collecteur;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterZone = '';
    public $filterStatut = '';
    public $filterDate = '';
    public $filterCollecteur = '';
    public $perPage = 15;

    // Stats
    public $tourneesAujourdhui = 0;
    public $tourneesEnCours = 0;
    public $tourneesTerminees = 0;
    public $totalCollecteJour = 0;

    protected $queryString = ['search', 'filterZone', 'filterStatut', 'filterDate', 'filterCollecteur'];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterZone() { $this->resetPage(); }
    public function updatingFilterStatut() { $this->resetPage(); }
    public function updatingFilterDate() { $this->resetPage(); }
    public function updatingFilterCollecteur() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'filterZone', 'filterStatut', 'filterDate', 'filterCollecteur']);
        $this->resetPage();
    }

    public function delete(Tournee $tournee)
    {
        $tournee->delete();
        session()->flash('success', 'Tournée supprimée avec succès.');
    }

    public function changerStatut(Tournee $tournee, string $nouveauStatut)
    {
        $tournee->update(['statut' => $nouveauStatut]);
        session()->flash('success', 'Statut mis à jour.');
    }

    /**
     * Démarrer une tournée planifiée
     * Rafraîchit le montant_attendu de chaque collecte avec le solde actuel du caissier
     */
    public function demarrer(int $tourneeId)
    {
        $tournee = Tournee::with('collectes.caissier')->findOrFail($tourneeId);

        if (!in_array($tournee->statut, ['planifiee', 'confirmee'])) {
            session()->flash('error', 'Cette tournée ne peut pas être démarrée.');
            return;
        }

        DB::transaction(function () use ($tournee) {
            // Rafraîchir le montant_attendu de chaque collecte avec le solde réel actuel du caissier
            foreach ($tournee->collectes as $collecte) {
                if ($collecte->caissier && $collecte->statut === 'en_attente') {
                    $soldeActuel = $collecte->caissier->solde_actuel ?? 0;
                    $collecte->update([
                        'montant_attendu' => $soldeActuel,
                        'heure_arrivee' => now(),
                    ]);
                }
            }

            $tournee->update([
                'statut' => 'en_cours',
                'heure_debut_reelle' => now(),
            ]);
        });

        session()->flash('success', 'Tournée démarrée. Les montants attendus ont été mis à jour.');
    }

    /**
     * Terminer une tournée en cours
     * Auto-collecte: transfère automatiquement l'argent des caissiers vers le collecteur
     */
    public function terminer(int $tourneeId)
    {
        $tournee = Tournee::with(['collectes.caissier', 'collecteur'])->findOrFail($tourneeId);

        if ($tournee->statut !== 'en_cours') {
            session()->flash('error', 'Cette tournée ne peut pas être terminée.');
            return;
        }

        $totalEncaisse = 0;
        $totalAttendu = 0;
        $collecteur = $tournee->collecteur;

        DB::transaction(function () use ($tournee, $collecteur, &$totalEncaisse, &$totalAttendu) {
            foreach ($tournee->collectes as $collecte) {
                $caissier = $collecte->caissier;

                if ($collecte->statut === 'en_attente' && $caissier) {
                    // Auto-collecte: le caissier n'a pas déposé via la page Dépôt
                    // On prend tout son solde actuel
                    $soldeActuel = (float) ($caissier->solde_actuel ?? 0);
                    $montantAttendu = max($soldeActuel, (float) $collecte->montant_attendu);

                    if ($soldeActuel > 0) {
                        // Mettre à jour la collecte
                        $collecte->update([
                            'montant_attendu' => $montantAttendu,
                            'montant_collecte' => $soldeActuel,
                            'ecart' => $soldeActuel - $montantAttendu,
                            'statut' => 'reussie',
                            'valide_par_collecteur' => true,
                            'valide_collecteur_at' => now(),
                            'heure_depart' => now(),
                            'mode_paiement' => 'cash',
                            'notes_collecteur' => 'Auto-collecté à la terminaison de la tournée',
                        ]);

                        // Déduire du solde du caissier
                        $caissier->decrement('solde_actuel', $soldeActuel);

                        // Ajouter à la caisse du collecteur
                        if ($collecteur) {
                            $collecteur->ajouterMontantAvecRepartition($soldeActuel);
                        }

                        $totalEncaisse += $soldeActuel;
                        $totalAttendu += $montantAttendu;
                    } else {
                        // Caissier avec solde 0 → marquer comme échouée
                        $collecte->update([
                            'montant_attendu' => $montantAttendu,
                            'montant_collecte' => 0,
                            'statut' => 'echouee',
                            'heure_depart' => now(),
                            'notes_collecteur' => 'Aucun solde disponible chez le caissier',
                        ]);
                        $totalAttendu += $montantAttendu;
                    }
                } else {
                    // Collecte déjà traitée (dépôt fait par le caissier)
                    $totalEncaisse += (float) ($collecte->montant_collecte ?? 0);
                    $totalAttendu += (float) ($collecte->montant_attendu ?? 0);

                    // Si le dépôt a été fait mais pas encore validé par le collecteur
                    if ($collecte->statut === 'reussie' && !$collecte->valide_par_collecteur) {
                        $montant = (float) $collecte->montant_collecte;
                        $collecte->update([
                            'valide_par_collecteur' => true,
                            'valide_collecteur_at' => now(),
                        ]);
                        if ($collecteur && $montant > 0) {
                            $collecteur->ajouterMontantAvecRepartition($montant);
                        }
                    }
                }
            }

            $tournee->update([
                'statut' => 'terminee',
                'heure_fin_reelle' => now(),
                'total_encaisse' => $totalEncaisse,
                'total_attendu' => $totalAttendu,
                'ecart_total' => $totalEncaisse - $totalAttendu,
            ]);
        });

        session()->flash('success', 'Tournée terminée. Total encaissé: ' . number_format($totalEncaisse) . ' FC sur ' . number_format($totalAttendu) . ' FC attendus.');
    }

    /**
     * Annuler une tournée
     */
    public function annuler(int $tourneeId)
    {
        $tournee = Tournee::findOrFail($tourneeId);

        if (in_array($tournee->statut, ['terminee', 'annulee'])) {
            session()->flash('error', 'Cette tournée ne peut pas être annulée.');
            return;
        }

        $tournee->update(['statut' => 'annulee']);

        session()->flash('success', 'Tournée annulée.');
    }

    protected function getBaseQuery()
    {
        return Tournee::with(['collecteur.user'])
            ->withCount('collectes')
            ->withSum('collectes', 'montant_collecte')
            ->withSum('collectes', 'montant_attendu')
            ->when($this->search, function ($q) {
                $q->whereHas('collecteur.user', fn($q2) => $q2->where('name', 'like', '%'.$this->search.'%'))
                  ->orWhere('zone', 'like', '%'.$this->search.'%');
            })
            ->when($this->filterCollecteur, fn($q) => $q->where('collecteur_id', $this->filterCollecteur))
            ->when($this->filterZone, fn($q) => $q->where('zone', $this->filterZone))
            ->when($this->filterStatut, fn($q) => $q->where('statut', $this->filterStatut))
            ->when($this->filterDate, fn($q) => $q->whereDate('date', $this->filterDate))
            ->orderBy('date', 'desc');
    }

    public function exportPdf()
    {
        $tournees = $this->getBaseQuery()->get();

        $stats = [
            'total' => $tournees->count(),
            'terminees' => $tournees->where('statut', 'terminee')->count(),
            'en_cours' => $tournees->where('statut', 'en_cours')->count(),
            'planifiees' => $tournees->whereIn('statut', ['planifiee', 'confirmee'])->count(),
        ];

        $pdf = Pdf::loadView('pdf.lists.tournees', [
            'tournees' => $tournees,
            'stats' => $stats,
            'title' => 'Liste des Tournées',
            'subtitle' => 'Exporté le ' . Carbon::now()->format('d/m/Y à H:i'),
            'filtres' => [
                'search' => $this->search,
                'date' => $this->filterDate,
                'statut' => $this->filterStatut,
            ],
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'tournees_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function render()
    {
        $aujourdhui = Carbon::today();

        // Calculer les stats du jour
        $this->tourneesAujourdhui = Tournee::whereDate('date', $aujourdhui)->count();
        $this->tourneesEnCours = Tournee::whereDate('date', $aujourdhui)->where('statut', 'en_cours')->count();
        $this->tourneesTerminees = Tournee::whereDate('date', $aujourdhui)->where('statut', 'terminee')->count();
        $this->totalCollecteJour = Collecte::whereHas('tournee', fn($q) => $q->whereDate('date', $aujourdhui))
            ->sum('montant_collecte');

        $tournees = $this->getBaseQuery()->paginate($this->perPage);
        $zones = Tournee::distinct()->pluck('zone')->filter();
        $collecteurs = Collecteur::with('user')->where('is_active', true)->get();

        return view('livewire.admin.tournees.index', compact('tournees', 'zones', 'collecteurs'));
    }
}
