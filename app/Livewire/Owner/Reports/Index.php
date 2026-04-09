<?php

namespace App\Livewire\Owner\Reports;

use Livewire\Component;
use Livewire\Attributes\Layout;
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
    public $totalRecuUsd = 0;
    public $recuMoisUsd = 0;
    public $nbPaiements = 0;

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

    public function getPaiementsMois()
    {
        $proprietaire = $this->getProprietaire();

        if (!$proprietaire) {
            return [];
        }

        $dateDebut = Carbon::createFromDate($this->annee, $this->mois, 1)->startOfMonth();
        $dateFin = Carbon::createFromDate($this->annee, $this->mois, 1)->endOfMonth();

        $paiements = $proprietaire->payments()
            ->whereBetween('date_paiement', [$dateDebut, $dateFin])
            ->orderBy('date_paiement', 'desc')
            ->get();

        $this->recuMoisUsd = $paiements->whereIn('statut', ['paye', 'payé', 'valide'])->sum('montant_usd') ?? 0;
        $this->nbPaiements = $paiements->count();

        // Total global reçu en USD
        $this->totalRecuUsd = $proprietaire->payments()
            ->whereIn('statut', ['paye', 'payé', 'valide'])
            ->sum('montant_usd') ?? 0;

        return $paiements;
    }

    public function exportPdf()
    {
        $proprietaire = $this->getProprietaire();
        $paiements = $this->getPaiementsMois();

        $dateDebut = Carbon::createFromDate($this->annee, $this->mois, 1);
        $moisNom = $dateDebut->translatedFormat('F Y');

        $pdf = Pdf::loadView('pdf.owner.releve-mensuel', [
            'proprietaire' => $proprietaire,
            'paiements' => $paiements,
            'totalRecuUsd' => $this->recuMoisUsd,
            'totalGlobalUsd' => $this->totalRecuUsd,
            'nbPaiements' => $this->nbPaiements,
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
        $paiements = $this->getPaiementsMois();

        return view('livewire.owner.reports.index', [
            'moisOptions' => $moisOptions,
            'anneeOptions' => $anneeOptions,
            'paiements' => $paiements,
            'proprietaire' => $proprietaire,
        ]);
    }
}
