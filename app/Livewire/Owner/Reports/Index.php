<?php

namespace App\Livewire\Owner\Reports;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Versement;
use App\Models\Moto;
use App\Models\Payment;
use App\Models\Proprietaire;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    public $proprietaireId;
    public $mois;
    public $annee;

    // Données du relevé
    public $totalVersements = 0;
    public $totalAttendu = 0;
    public $totalArrieres = 0;
    public $paiementsRecus = 0;
    public $soldeDisponible = 0;

    public function mount()
    {
        $proprietaire = auth()->user()->proprietaire;
        $this->proprietaireId = $proprietaire?->id;
        $this->mois = now()->month;
        $this->annee = now()->year;
    }

    public function getProprietaire()
    {
        if (!$this->proprietaireId) {
            return null;
        }
        return Proprietaire::with('user')->find($this->proprietaireId);
    }

    public function getVersementsParMoto()
    {
        $proprietaire = $this->getProprietaire();

        if (!$proprietaire) {
            return [];
        }

        $motos = $proprietaire->motos()->with('motard.user')->get();
        $motoIds = $motos->pluck('id');

        $dateDebut = Carbon::createFromDate($this->annee, $this->mois, 1)->startOfMonth();
        $dateFin = Carbon::createFromDate($this->annee, $this->mois, 1)->endOfMonth();
        $joursOuvrables = $dateFin->day;

        $versementsParMoto = [];
        $this->totalVersements = 0;
        $this->totalAttendu = 0;
        $this->totalArrieres = 0;

        foreach ($motos as $moto) {
            $versements = Versement::where('moto_id', $moto->id)
                ->whereBetween('date_versement', [$dateDebut, $dateFin])
                ->get();

            $totalMoto = $versements->sum('montant');

            if ($versements->count() > 0) {
                $attenduMoto = $versements->sum('montant_attendu');
            } else {
                $tarifJournalier = $moto->montant_journalier_attendu ?? 0;
                $attenduMoto = $tarifJournalier * $joursOuvrables;
            }

            $arrieresMoto = max(0, $attenduMoto - $totalMoto);
            $nbVersements = $versements->count();

            $versementsParMoto[] = [
                'moto' => $moto,
                'total' => $totalMoto,
                'attendu' => $attenduMoto,
                'arrieres' => $arrieresMoto,
                'nb_versements' => $nbVersements,
            ];

            $this->totalVersements += $totalMoto;
            $this->totalAttendu += $attenduMoto;
            $this->totalArrieres += $arrieresMoto;
        }

        // Paiements reçus ce mois
        $this->paiementsRecus = $proprietaire->payments()
            ->whereIn('statut', ['paye', 'payé', 'valide'])
            ->whereMonth('date_paiement', $this->mois)
            ->whereYear('date_paiement', $this->annee)
            ->sum('total_paye');

        // Solde disponible global
        $totalVersementsTous = Versement::whereIn('moto_id', $motoIds)->sum('montant');
        $totalPaiementsTous = $proprietaire->payments()
            ->whereIn('statut', ['paye', 'payé', 'valide'])
            ->sum('total_paye');
        $this->soldeDisponible = max(0, $totalVersementsTous - $totalPaiementsTous);

        return $versementsParMoto;
    }

    public function exportPdf()
    {
        $proprietaire = $this->getProprietaire();
        $versementsParMoto = $this->getVersementsParMoto();

        $dateDebut = Carbon::createFromDate($this->annee, $this->mois, 1);
        $moisNom = $dateDebut->translatedFormat('F Y');

        $pdf = Pdf::loadView('pdf.owner.releve-mensuel', [
            'proprietaire' => $proprietaire,
            'versementsParMoto' => $versementsParMoto,
            'totalVersements' => $this->totalVersements,
            'totalAttendu' => $this->totalAttendu,
            'totalArrieres' => $this->totalArrieres,
            'paiementsRecus' => $this->paiementsRecus,
            'soldeDisponible' => $this->soldeDisponible,
            'mois' => $moisNom,
            'periode' => $dateDebut->format('m/Y'),
        ]);

        $pdf->setPaper('A4', 'portrait');

        $filename = 'releve_' . $proprietaire->id . '_' . $this->annee . '_' . str_pad($this->mois, 2, '0', STR_PAD_LEFT) . '.pdf';

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    public function render()
    {
        $moisOptions = [];
        for ($i = 1; $i <= 12; $i++) {
            $moisOptions[$i] = Carbon::createFromDate(null, $i, 1)->translatedFormat('F');
        }

        $anneeOptions = [];
        $anneeActuelle = now()->year;
        for ($i = $anneeActuelle - 2; $i <= $anneeActuelle; $i++) {
            $anneeOptions[$i] = $i;
        }

        $proprietaire = $this->getProprietaire();
        $versementsParMoto = $this->getVersementsParMoto();

        return view('livewire.owner.reports.index', [
            'moisOptions' => $moisOptions,
            'anneeOptions' => $anneeOptions,
            'versementsParMoto' => $versementsParMoto,
            'proprietaire' => $proprietaire,
        ]);
    }
}
