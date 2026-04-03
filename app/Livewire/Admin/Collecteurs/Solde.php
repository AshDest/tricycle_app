<?php

namespace App\Livewire\Admin\Collecteurs;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Collecteur;
use App\Models\Collecte;
use App\Models\Payment;
use App\Models\TransactionMobile;
use App\Models\CommissionMobileMensuelle;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

#[Layout('components.dashlite-layout')]
class Solde extends Component
{
    use WithPagination;

    public Collecteur $collecteur;

    public $periodeFilter = 'mois';
    public $dateDebut = '';
    public $dateFin = '';
    public $onglet = 'resume'; // resume, collectes, paiements, transactions, commissions

    // Stats
    public $soldeCaisse = 0;
    public $soldePartOkami = 0;
    public $soldePartProprietaire = 0;
    public $totalCollectePeriode = 0;
    public $totalPaiementsPeriode = 0;
    public $totalTransactionsEnvoi = 0;
    public $totalTransactionsRetrait = 0;
    public $totalCommissions = 0;
    public $nombreCollectes = 0;
    public $nombrePaiements = 0;

    public function mount(Collecteur $collecteur)
    {
        $this->collecteur = $collecteur->load('user');
        $this->dateDebut = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateFin = Carbon::now()->format('Y-m-d');
        $this->computeStats();
    }

    public function updatedPeriodeFilter($value)
    {
        switch ($value) {
            case 'jour':
                $this->dateDebut = Carbon::today()->format('Y-m-d');
                $this->dateFin = Carbon::today()->format('Y-m-d');
                break;
            case 'semaine':
                $this->dateDebut = Carbon::now()->startOfWeek()->format('Y-m-d');
                $this->dateFin = Carbon::now()->format('Y-m-d');
                break;
            case 'mois':
                $this->dateDebut = Carbon::now()->startOfMonth()->format('Y-m-d');
                $this->dateFin = Carbon::now()->format('Y-m-d');
                break;
            case 'annee':
                $this->dateDebut = Carbon::now()->startOfYear()->format('Y-m-d');
                $this->dateFin = Carbon::now()->format('Y-m-d');
                break;
        }
        $this->computeStats();
        $this->resetPage();
    }

    public function updated($property)
    {
        if (in_array($property, ['dateDebut', 'dateFin'])) {
            $this->computeStats();
            $this->resetPage();
        }
    }

    public function setOnglet($onglet)
    {
        $this->onglet = $onglet;
        $this->resetPage();
    }

    private function computeStats()
    {
        // Soldes actuels
        $this->soldeCaisse = $this->collecteur->solde_caisse ?? 0;
        $this->soldePartOkami = $this->collecteur->solde_part_okami ?? 0;
        $this->soldePartProprietaire = $this->collecteur->solde_part_proprietaire ?? 0;

        // Collectes de la période
        $collectesQuery = Collecte::whereHas('tournee', function ($q) {
            $q->where('collecteur_id', $this->collecteur->id);
        })
        ->whereBetween('created_at', [
            $this->dateDebut . ' 00:00:00',
            $this->dateFin . ' 23:59:59'
        ])
        ->where('statut', 'reussie');

        $this->totalCollectePeriode = (clone $collectesQuery)->sum('montant_collecte');
        $this->nombreCollectes = (clone $collectesQuery)->count();

        // Paiements traités par ce collecteur
        $paiementsQuery = Payment::where('traite_par', $this->collecteur->user_id)
            ->where('statut', 'paye')
            ->whereBetween('date_paiement', [$this->dateDebut, $this->dateFin]);

        $this->totalPaiementsPeriode = (clone $paiementsQuery)->sum('total_paye');
        $this->nombrePaiements = (clone $paiementsQuery)->count();

        // Transactions mobile
        $txQuery = TransactionMobile::where('collecteur_id', $this->collecteur->id)
            ->where('statut', 'complete')
            ->whereBetween('date_transaction', [
                $this->dateDebut . ' 00:00:00',
                $this->dateFin . ' 23:59:59'
            ]);

        $this->totalTransactionsEnvoi = (clone $txQuery)->where('type', 'envoi')->sum('montant');
        $this->totalTransactionsRetrait = (clone $txQuery)->where('type', 'retrait')->sum('montant');

        // Commissions
        $this->totalCommissions = CommissionMobileMensuelle::where('collecteur_id', $this->collecteur->id)
            ->where('statut', 'valide')
            ->sum('montant_total');
    }

    private function getCollectes()
    {
        return Collecte::with(['tournee', 'caissier.user'])
            ->whereHas('tournee', function ($q) {
                $q->where('collecteur_id', $this->collecteur->id);
            })
            ->whereBetween('created_at', [
                $this->dateDebut . ' 00:00:00',
                $this->dateFin . ' 23:59:59'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    private function getPaiements()
    {
        return Payment::with(['proprietaire.user', 'demandePar'])
            ->where('traite_par', $this->collecteur->user_id)
            ->where('statut', 'paye')
            ->whereBetween('date_paiement', [$this->dateDebut, $this->dateFin])
            ->orderBy('date_paiement', 'desc')
            ->paginate(15);
    }

    private function getTransactions()
    {
        return TransactionMobile::where('collecteur_id', $this->collecteur->id)
            ->whereBetween('date_transaction', [
                $this->dateDebut . ' 00:00:00',
                $this->dateFin . ' 23:59:59'
            ])
            ->orderBy('date_transaction', 'desc')
            ->paginate(15);
    }

    private function getCommissions()
    {
        return CommissionMobileMensuelle::where('collecteur_id', $this->collecteur->id)
            ->orderBy('annee', 'desc')
            ->orderBy('mois', 'desc')
            ->paginate(15);
    }

    private function getJournalQuotidien()
    {
        // Résumé jour par jour des mouvements
        $jours = [];
        $debut = Carbon::parse($this->dateDebut);
        $fin = Carbon::parse($this->dateFin);

        // Limiter à 31 jours pour ne pas surcharger
        if ($debut->diffInDays($fin) > 31) {
            $debut = $fin->copy()->subDays(31);
        }

        while ($debut->lte($fin)) {
            $dateStr = $debut->format('Y-m-d');

            // Collectes du jour
            $collecteJour = Collecte::whereHas('tournee', function ($q) {
                $q->where('collecteur_id', $this->collecteur->id);
            })
            ->whereDate('created_at', $dateStr)
            ->where('statut', 'reussie')
            ->sum('montant_collecte');

            // Paiements du jour
            $paiementJour = Payment::where('traite_par', $this->collecteur->user_id)
                ->where('statut', 'paye')
                ->whereDate('date_paiement', $dateStr)
                ->sum('total_paye');

            // Transactions mobile du jour (sorties)
            $txEnvoiJour = TransactionMobile::where('collecteur_id', $this->collecteur->id)
                ->where('type', 'envoi')
                ->where('statut', 'complete')
                ->whereDate('date_transaction', $dateStr)
                ->sum('montant');

            // Transactions mobile du jour (entrées)
            $txRetraitJour = TransactionMobile::where('collecteur_id', $this->collecteur->id)
                ->where('type', 'retrait')
                ->where('statut', 'complete')
                ->whereDate('date_transaction', $dateStr)
                ->sum('montant');

            if ($collecteJour > 0 || $paiementJour > 0 || $txEnvoiJour > 0 || $txRetraitJour > 0) {
                $jours[] = [
                    'date' => $dateStr,
                    'date_formatee' => $debut->translatedFormat('l d M Y'),
                    'collectes' => $collecteJour,
                    'paiements' => $paiementJour,
                    'tx_envoi' => $txEnvoiJour,
                    'tx_retrait' => $txRetraitJour,
                    'total_entrees' => $collecteJour + $txRetraitJour,
                    'total_sorties' => $paiementJour + $txEnvoiJour,
                    'solde_jour' => ($collecteJour + $txRetraitJour) - ($paiementJour + $txEnvoiJour),
                ];
            }

            $debut->addDay();
        }

        return array_reverse($jours);
    }

    public function exportPdf()
    {
        $journal = $this->getJournalQuotidien();

        $pdf = Pdf::loadView('pdf.admin.collecteur-solde', [
            'collecteur' => $this->collecteur,
            'journal' => $journal,
            'soldeCaisse' => $this->soldeCaisse,
            'soldePartOkami' => $this->soldePartOkami,
            'soldePartProprietaire' => $this->soldePartProprietaire,
            'totalCollectePeriode' => $this->totalCollectePeriode,
            'totalPaiementsPeriode' => $this->totalPaiementsPeriode,
            'totalTransactionsEnvoi' => $this->totalTransactionsEnvoi,
            'totalTransactionsRetrait' => $this->totalTransactionsRetrait,
            'dateDebut' => $this->dateDebut,
            'dateFin' => $this->dateFin,
            'title' => 'Solde & Dépenses - ' . ($this->collecteur->user->name ?? 'Collecteur'),
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'collecteur_solde_' . $this->collecteur->id . '_' . now()->format('Y-m-d') . '.pdf'
        );
    }

    public function render()
    {
        $data = [
            'journalQuotidien' => $this->getJournalQuotidien(),
        ];

        // Charger les données selon l'onglet actif
        switch ($this->onglet) {
            case 'collectes':
                $data['collectes'] = $this->getCollectes();
                break;
            case 'paiements':
                $data['paiements'] = $this->getPaiements();
                break;
            case 'transactions':
                $data['transactions'] = $this->getTransactions();
                break;
            case 'commissions':
                $data['commissions'] = $this->getCommissions();
                break;
        }

        return view('livewire.admin.collecteurs.solde', $data);
    }
}

