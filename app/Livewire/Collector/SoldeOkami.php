<?php

namespace App\Livewire\Collector;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Versement;
use App\Models\Payment;
use App\Models\Lavage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Vue du solde OKAMI disponible
 * Affiche les statistiques et l'historique des mouvements OKAMI
 */
#[Layout('components.dashlite-layout')]
class SoldeOkami extends Component
{
    public $periodeFilter = 'mois';
    public $dateDebut = '';
    public $dateFin = '';

    public function mount()
    {
        $this->dateDebut = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateFin = Carbon::now()->format('Y-m-d');
    }

    public function updatedPeriodeFilter($value)
    {
        switch ($value) {
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
    }

    private function getOkamiData()
    {
        $collecteur = auth()->user()->collecteur;

        // Solde caisse actuel du collecteur
        $soldeOkamiCollecteur = $collecteur?->solde_caisse ?? 0;

        // Total part OKAMI des versements (caisse unique)
        $totalPartOkamiVersements = Versement::whereBetween('date_versement', [$this->dateDebut, $this->dateFin])
            ->whereIn('statut', ['payé', 'partiellement_payé'])
            ->sum('part_okami');

        // Total part OKAMI des lavages (20% des lavages internes)
        $totalPartOkamiLavages = Lavage::whereBetween('date_lavage', [$this->dateDebut, $this->dateFin])
            ->where('is_externe', false)
            ->where('statut_paiement', 'payé')
            ->sum('part_okami');

        // Total des paiements effectués depuis la caisse OKAMI
        $totalPaiementsOkami = Payment::whereBetween('date_paiement', [$this->dateDebut, $this->dateFin])
            ->where('source_caisse', 'okami')
            ->where('statut', 'paye')
            ->sum('total_paye');

        // Paiements en attente depuis la caisse OKAMI
        $paiementsEnAttenteOkami = Payment::where('source_caisse', 'okami')
            ->where('statut', 'en_attente')
            ->sum('total_du');

        // Calcul du solde global OKAMI (approximatif basé sur les transactions)
        $totalEntreesOkami = $totalPartOkamiVersements + $totalPartOkamiLavages;
        $totalSortiesOkami = $totalPaiementsOkami;
        $soldeNetPeriode = $totalEntreesOkami - $totalSortiesOkami;

        // Derniers mouvements OKAMI
        $derniersVersements = Versement::with(['motard.user', 'moto'])
            ->whereBetween('date_versement', [$this->dateDebut, $this->dateFin])
            ->whereIn('statut', ['payé', 'partiellement_payé'])
            ->where('part_okami', '>', 0)
            ->orderBy('date_versement', 'desc')
            ->limit(10)
            ->get();

        $derniersPaiements = Payment::with(['proprietaire.user', 'demandePar'])
            ->where('source_caisse', 'okami')
            ->where('statut', 'paye')
            ->whereBetween('date_paiement', [$this->dateDebut, $this->dateFin])
            ->orderBy('date_paiement', 'desc')
            ->limit(10)
            ->get();

        // Stats par semaine du mois en cours
        $statsParSemaine = [];
        $debutMois = Carbon::now()->startOfMonth();
        $finMois = Carbon::now()->endOfMonth();
        $semaineCourante = $debutMois->copy();

        while ($semaineCourante->lte($finMois)) {
            $debutSemaine = $semaineCourante->copy()->startOfWeek();
            $finSemaine = $semaineCourante->copy()->endOfWeek();

            if ($finSemaine->gt($finMois)) {
                $finSemaine = $finMois->copy();
            }

            $partVersements = Versement::whereBetween('date_versement', [$debutSemaine, $finSemaine])
                ->whereIn('statut', ['payé', 'partiellement_payé'])
                ->sum('part_okami');

            $partLavages = Lavage::whereBetween('date_lavage', [$debutSemaine, $finSemaine])
                ->where('is_externe', false)
                ->where('statut_paiement', 'payé')
                ->sum('part_okami');

            $statsParSemaine[] = [
                'semaine' => 'Sem. ' . $debutSemaine->weekOfMonth,
                'debut' => $debutSemaine->format('d/m'),
                'fin' => $finSemaine->format('d/m'),
                'versements' => $partVersements,
                'lavages' => $partLavages,
                'total' => $partVersements + $partLavages,
            ];

            $semaineCourante->addWeek();
        }

        return [
            'soldeOkamiCollecteur' => $soldeOkamiCollecteur,
            'totalPartOkamiVersements' => $totalPartOkamiVersements,
            'totalPartOkamiLavages' => $totalPartOkamiLavages,
            'totalPaiementsOkami' => $totalPaiementsOkami,
            'paiementsEnAttenteOkami' => $paiementsEnAttenteOkami,
            'totalEntreesOkami' => $totalEntreesOkami,
            'totalSortiesOkami' => $totalSortiesOkami,
            'soldeNetPeriode' => $soldeNetPeriode,
            'derniersVersements' => $derniersVersements,
            'derniersPaiements' => $derniersPaiements,
            'statsParSemaine' => $statsParSemaine,
        ];
    }

    public function render()
    {
        return view('livewire.collector.solde-okami', $this->getOkamiData());
    }

    /**
     * Exporter le rapport Solde OKAMI en PDF
     */
    public function exporterPdf()
    {
        $data = $this->getOkamiData();
        $data['dateDebut'] = $this->dateDebut;
        $data['dateFin'] = $this->dateFin;
        $data['periodeFilter'] = $this->periodeFilter;
        $data['dateGeneration'] = now();

        $pdf = Pdf::loadView('pdf.solde-okami', $data);
        $pdf->setPaper('a4', 'portrait');

        $filename = 'solde_okami_' . now()->format('Y-m-d_His') . '.pdf';

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }
}

