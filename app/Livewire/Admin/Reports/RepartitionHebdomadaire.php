<?php

namespace App\Livewire\Admin\Reports;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Services\RepartitionService;
use App\Models\Moto;
use App\Models\Proprietaire;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class RepartitionHebdomadaire extends Component
{
    public $semaineSelectionnee;
    public $semaines = [];

    public function mount()
    {
        $this->loadSemaines();
        $this->semaineSelectionnee = 0; // Semaine courante
    }

    public function loadSemaines()
    {
        $semaines = RepartitionService::getSemainesDuMois();
        $this->semaines = collect($semaines)->map(function ($s, $index) {
            return [
                'index' => $index,
                'debut' => $s['debut']->format('Y-m-d'),
                'fin' => $s['fin']->format('Y-m-d'),
                'label' => $s['label'],
            ];
        })->toArray();
    }

    public function getDateDebut(): Carbon
    {
        if (isset($this->semaines[$this->semaineSelectionnee])) {
            return Carbon::parse($this->semaines[$this->semaineSelectionnee]['debut']);
        }
        return Carbon::now()->startOfWeek();
    }

    public function exportPdf()
    {
        $dateDebut = $this->getDateDebut();
        $resume = RepartitionService::getResumeHebdomadaireGlobal($dateDebut);

        // Détails par propriétaire
        $proprietaires = Proprietaire::with('user', 'motos')->get();
        $detailsProprietaires = [];

        foreach ($proprietaires as $prop) {
            if ($prop->motos()->where('statut', 'actif')->count() > 0) {
                $detailsProprietaires[] = RepartitionService::getRepartitionHebdomadaireProprietaire($prop, $dateDebut->copy());
            }
        }

        $pdf = Pdf::loadView('pdf.reports.repartition-hebdomadaire', [
            'resume' => $resume,
            'detailsProprietaires' => $detailsProprietaires,
            'dateExport' => now()->format('d/m/Y H:i'),
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'repartition_hebdomadaire_' . $dateDebut->format('Y-m-d') . '.pdf');
    }

    public function render()
    {
        $dateDebut = $this->getDateDebut();
        $resume = RepartitionService::getResumeHebdomadaireGlobal($dateDebut);

        // Détails par propriétaire
        $proprietaires = Proprietaire::with('user', 'motos')->get();
        $detailsProprietaires = [];

        foreach ($proprietaires as $prop) {
            if ($prop->motos()->where('statut', 'actif')->count() > 0) {
                $detailsProprietaires[] = RepartitionService::getRepartitionHebdomadaireProprietaire($prop, $dateDebut->copy());
            }
        }

        return view('livewire.admin.reports.repartition-hebdomadaire', [
            'resume' => $resume,
            'detailsProprietaires' => $detailsProprietaires,
            'constantes' => [
                'jours_semaine' => RepartitionService::JOURS_SEMAINE,
                'jours_proprietaire' => RepartitionService::JOURS_PROPRIETAIRE,
                'jours_okami' => RepartitionService::JOURS_OKAMI,
            ],
        ]);
    }
}

