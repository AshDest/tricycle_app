<?php
namespace App\Livewire\Collector\Commissions;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\CommissionMobileMensuelle;
use App\Models\AuditBeneficeCommission;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination, WithFileUploads;
    public $filterAnnee = '';
    public $filterStatut = '';
    public int $perPage = 12;
    // Modal création
    public $showCreateModal = false;
    public $annee = '';
    public $mois = '';
    public $montant_total = ''; // Montant saisi en USD
    public $preuve_paiement;
    public $commentaire = '';
    // Stats
    public $totalCommissions = 0;
    public $totalPartNth = 0;
    public $totalPartOkami = 0;
    protected function rules()
    {
        return [
            'annee' => 'required|integer|min:2020|max:' . (now()->year + 1),
            'mois' => 'required|integer|min:1|max:12',
            'montant_total' => 'required|numeric|min:0.01',
            'preuve_paiement' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'commentaire' => 'nullable|string|max:1000',
        ];
    }
    protected $messages = [
        'preuve_paiement.required' => 'La preuve de paiement est obligatoire.',
        'preuve_paiement.mimes' => 'Le fichier doit être une image (JPG, PNG) ou un PDF.',
        'preuve_paiement.max' => 'Le fichier ne doit pas dépasser 5 Mo.',
    ];
    public function mount()
    {
        $this->filterAnnee = now()->year;
        $this->annee = now()->year;
        $this->mois = now()->month;
        $this->computeStats();
    }
    public function updated($property)
    {
        if (in_array($property, ['filterAnnee', 'filterStatut'])) {
            $this->computeStats();
            $this->resetPage();
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
        $query = CommissionMobileMensuelle::where('collecteur_id', $collecteur->id)
            ->where('statut', 'valide');
        if ($this->filterAnnee) {
            $query->where('annee', $this->filterAnnee);
        }
        $this->totalCommissions = (clone $query)->sum('montant_total');
        $this->totalPartNth = (clone $query)->sum('part_nth');
        $this->totalPartOkami = (clone $query)->sum('part_okami');
    }
    private function getBaseQuery()
    {
        $collecteur = $this->getCollecteur();
        if (!$collecteur) {
            return CommissionMobileMensuelle::whereRaw('1 = 0');
        }
        return CommissionMobileMensuelle::where('collecteur_id', $collecteur->id)
            ->when($this->filterAnnee, fn($q) => $q->where('annee', $this->filterAnnee))
            ->when($this->filterStatut, fn($q) => $q->where('statut', $this->filterStatut))
            ->orderBy('annee', 'desc')
            ->orderBy('mois', 'desc');
    }
    public function ouvrirModal()
    {
        $this->reset(['montant_total', 'preuve_paiement', 'commentaire']);
        $this->annee = now()->year;
        $this->mois = now()->month;
        $this->showCreateModal = true;
    }
    public function fermerModal()
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }
    public function enregistrerCommission()
    {
        $this->validate();
        $collecteur = $this->getCollecteur();
        if (!$collecteur) {
            session()->flash('error', 'Collecteur non trouvé.');
            return;
        }
        // Vérifier si une commission existe déjà pour ce mois
        if (CommissionMobileMensuelle::existePourMois($collecteur->id, $this->annee, $this->mois)) {
            $this->addError('mois', 'Une commission existe déjà pour ce mois.');
            return;
        }
        try {
            // Enregistrer le fichier
            $path = $this->preuve_paiement->store('commissions/' . $this->annee, 'public');

            // Convertir USD en FC pour le stockage
            $tauxUsd = \App\Models\SystemSetting::getTauxUsdCdf();
            $montantFc = round($this->montant_total * $tauxUsd, 2);

            $commission = CommissionMobileMensuelle::create([
                'collecteur_id' => $collecteur->id,
                'annee' => $this->annee,
                'mois' => $this->mois,
                'montant_total' => $montantFc,
                'preuve_paiement' => $path,
                'commentaire' => $this->commentaire,
                'statut' => 'en_attente',
            ]);
            // Audit
            AuditBeneficeCommission::enregistrer(
                'commissions_mobile_mensuelles',
                $commission->id,
                'creation',
                null,
                $commission->toArray()
            );
            $this->fermerModal();
            $this->computeStats();
            session()->flash('success', 'Commission de ' . number_format($this->montant_total, 2) . ' $ enregistrée. En attente de validation.');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }
    public function getPreviewRepartition()
    {
        if (!$this->montant_total || !is_numeric($this->montant_total)) {
            return ['nth' => 0, 'okami' => 0];
        }
        // Preview en USD (le montant_total est saisi en USD)
        return [
            'nth' => round($this->montant_total * 0.7, 2),
            'okami' => round($this->montant_total * 0.3, 2),
        ];
    }
    public function exportPdf()
    {
        $commissions = $this->getBaseQuery()->get();
        $collecteur = $this->getCollecteur();
        $pdf = Pdf::loadView('pdf.lists.commissions-mensuelles', [
            'commissions' => $commissions,
            'collecteur' => $collecteur,
            'annee' => $this->filterAnnee,
            'stats' => [
                'totalCommissions' => $this->totalCommissions,
                'totalPartNth' => $this->totalPartNth,
                'totalPartOkami' => $this->totalPartOkami,
            ],
            'title' => 'Commissions Mobile Money - ' . $this->filterAnnee,
        ]);
        return response()->streamDownload(fn() => print($pdf->output()),
            'commissions_mobile_' . $this->filterAnnee . '.pdf');
    }
    public function render()
    {
        $preview = $this->getPreviewRepartition();
        return view('livewire.collector.commissions.index', [
            'commissions' => $this->getBaseQuery()->paginate($this->perPage),
            'moisList' => CommissionMobileMensuelle::getMois(),
            'annees' => range(now()->year, 2020),
            'previewNth' => $preview['nth'],
            'previewOkami' => $preview['okami'],
        ]);
    }
}
