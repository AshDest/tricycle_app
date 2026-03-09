<?php
namespace App\Livewire\Collector\BeneficesChange;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\BeneficeChange;
use App\Models\AuditBeneficeCommission;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;
    public $filterType = '';
    public $filterStatut = '';
    public $dateDebut = '';
    public $dateFin = '';
    public int $perPage = 15;
    // Modal création
    public $showCreateModal = false;
    public $type_saisie = 'journalier';
    public $date_operation = '';
    public $periode_debut = '';
    public $periode_fin = '';
    public $montant_recu_caissier = '';
    public $solde_general_caisse = '';
    public $benefice = '';
    public $commentaire = '';
    // Stats
    public $totalBeneficeJournalier = 0;
    public $totalBeneficeHebdo = 0;
    public $totalBeneficeMensuel = 0;
    public $totalBeneficePeriode = 0;
    protected function rules()
    {
        $rules = [
            'type_saisie' => 'required|in:journalier,hebdomadaire,mensuel',
            'benefice' => 'required|numeric|min:0',
            'montant_recu_caissier' => 'nullable|numeric|min:0',
            'solde_general_caisse' => 'nullable|numeric|min:0',
            'commentaire' => 'nullable|string|max:1000',
        ];
        if ($this->type_saisie === 'journalier') {
            $rules['date_operation'] = 'required|date';
        } else {
            $rules['periode_debut'] = 'required|date';
            $rules['periode_fin'] = 'required|date|after_or_equal:periode_debut';
        }
        return $rules;
    }
    public function mount()
    {
        $this->dateDebut = now()->startOfMonth()->format('Y-m-d');
        $this->dateFin = now()->format('Y-m-d');
        $this->date_operation = now()->format('Y-m-d');
        $this->periode_debut = now()->startOfWeek()->format('Y-m-d');
        $this->periode_fin = now()->endOfWeek()->format('Y-m-d');
        $this->computeStats();
    }
    public function updated($property)
    {
        if (in_array($property, ['filterType', 'filterStatut', 'dateDebut', 'dateFin'])) {
            $this->computeStats();
            $this->resetPage();
        }
    }
    public function updatedTypeSaisie($value)
    {
        if ($value === 'journalier') {
            $this->date_operation = now()->format('Y-m-d');
        } elseif ($value === 'hebdomadaire') {
            $this->periode_debut = now()->startOfWeek()->format('Y-m-d');
            $this->periode_fin = now()->endOfWeek()->format('Y-m-d');
        } else {
            $this->periode_debut = now()->startOfMonth()->format('Y-m-d');
            $this->periode_fin = now()->endOfMonth()->format('Y-m-d');
        }
    }
    private function getCollecteur()
    {
        return auth()->user()->collecteur;
    }
    private function computeStats()
    {
        $collecteur = $this->getCollecteur();
        if (!$collecteur) return;
        $baseQuery = BeneficeChange::where('collecteur_id', $collecteur->id)
            ->where('statut', 'valide');
        if ($this->dateDebut) {
            $baseQuery->where('date_operation', '>=', $this->dateDebut);
        }
        if ($this->dateFin) {
            $baseQuery->where('date_operation', '<=', $this->dateFin);
        }
        $this->totalBeneficeJournalier = (clone $baseQuery)->where('type_saisie', 'journalier')->sum('benefice');
        $this->totalBeneficeHebdo = (clone $baseQuery)->where('type_saisie', 'hebdomadaire')->sum('benefice');
        $this->totalBeneficeMensuel = (clone $baseQuery)->where('type_saisie', 'mensuel')->sum('benefice');
        $this->totalBeneficePeriode = (clone $baseQuery)->sum('benefice');
    }
    private function getBaseQuery()
    {
        $collecteur = $this->getCollecteur();
        if (!$collecteur) {
            return BeneficeChange::whereRaw('1 = 0');
        }
        return BeneficeChange::where('collecteur_id', $collecteur->id)
            ->when($this->filterType, fn($q) => $q->where('type_saisie', $this->filterType))
            ->when($this->filterStatut, fn($q) => $q->where('statut', $this->filterStatut))
            ->when($this->dateDebut, fn($q) => $q->where('date_operation', '>=', $this->dateDebut))
            ->when($this->dateFin, fn($q) => $q->where('date_operation', '<=', $this->dateFin))
            ->orderBy('date_operation', 'desc');
    }
    public function ouvrirModal()
    {
        $this->reset(['montant_recu_caissier', 'solde_general_caisse', 'benefice', 'commentaire']);
        $this->type_saisie = 'journalier';
        $this->date_operation = now()->format('Y-m-d');
        $this->periode_debut = now()->startOfWeek()->format('Y-m-d');
        $this->periode_fin = now()->endOfWeek()->format('Y-m-d');
        $this->showCreateModal = true;
    }
    public function fermerModal()
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }
    public function calculerBeneficeAuto()
    {
        $collecteur = $this->getCollecteur();
        if (!$collecteur) return;
        if ($this->type_saisie === 'hebdomadaire') {
            $this->benefice = BeneficeChange::calculerBeneficeHebdomadaire(
                $collecteur->id,
                Carbon::parse($this->periode_debut),
                Carbon::parse($this->periode_fin)
            );
        } elseif ($this->type_saisie === 'mensuel') {
            $date = Carbon::parse($this->periode_debut);
            $this->benefice = BeneficeChange::calculerBeneficeMensuel(
                $collecteur->id,
                $date->year,
                $date->month
            );
        }
    }
    public function enregistrerBenefice()
    {
        $this->validate();
        $collecteur = $this->getCollecteur();
        if (!$collecteur) {
            session()->flash('error', 'Collecteur non trouvé.');
            return;
        }
        try {
            $data = [
                'collecteur_id' => $collecteur->id,
                'type_saisie' => $this->type_saisie,
                'benefice' => $this->benefice,
                'montant_recu_caissier' => $this->montant_recu_caissier ?: null,
                'solde_general_caisse' => $this->solde_general_caisse ?: null,
                'commentaire' => $this->commentaire,
                'statut' => 'en_attente',
            ];
            if ($this->type_saisie === 'journalier') {
                $data['date_operation'] = $this->date_operation;
            } else {
                $data['date_operation'] = $this->periode_debut;
                $data['periode_debut'] = $this->periode_debut;
                $data['periode_fin'] = $this->periode_fin;
            }
            $benefice = BeneficeChange::create($data);
            // Audit
            AuditBeneficeCommission::enregistrer(
                'benefices_change',
                $benefice->id,
                'creation',
                null,
                $benefice->toArray()
            );
            $this->fermerModal();
            $this->computeStats();
            session()->flash('success', 'Bénéfice de ' . number_format($this->benefice) . ' FC enregistré avec succès.');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }
    public function exportPdf()
    {
        $benefices = $this->getBaseQuery()->get();
        $collecteur = $this->getCollecteur();
        $pdf = Pdf::loadView('pdf.lists.benefices-change', [
            'benefices' => $benefices,
            'collecteur' => $collecteur,
            'periode' => $this->dateDebut . ' - ' . $this->dateFin,
            'stats' => [
                'totalJournalier' => $this->totalBeneficeJournalier,
                'totalHebdo' => $this->totalBeneficeHebdo,
                'totalMensuel' => $this->totalBeneficeMensuel,
                'totalPeriode' => $this->totalBeneficePeriode,
            ],
            'title' => 'Bénéfices de Change',
        ]);
        return response()->streamDownload(fn() => print($pdf->output()), 
            'benefices_change_' . now()->format('Y-m-d') . '.pdf');
    }
    public function exportExcel()
    {
        // Pour l'export Excel, on retourne un CSV simple
        $benefices = $this->getBaseQuery()->get();
        $csv = "Référence;Type;Date;Montant Caissier;Solde Caisse;Bénéfice;Statut\n";
        foreach ($benefices as $b) {
            $csv .= "{$b->numero_reference};{$b->type_saisie_label};{$b->periode_label};";
            $csv .= number_format($b->montant_recu_caissier ?? 0, 0, ',', ' ') . ";";
            $csv .= number_format($b->solde_general_caisse ?? 0, 0, ',', ' ') . ";";
            $csv .= number_format($b->benefice, 0, ',', ' ') . ";{$b->statut_label}\n";
        }
        return response()->streamDownload(function() use ($csv) {
            echo "\xEF\xBB\xBF" . $csv; // BOM UTF-8
        }, 'benefices_change_' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
    public function render()
    {
        return view('livewire.collector.benefices-change.index', [
            'benefices' => $this->getBaseQuery()->paginate($this->perPage),
            'typesSaisie' => BeneficeChange::getTypesSaisie(),
        ]);
    }
}
