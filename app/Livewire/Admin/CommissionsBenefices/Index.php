<?php
namespace App\Livewire\Admin\CommissionsBenefices;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\CommissionMobileMensuelle;
use App\Models\BeneficeChange;
use App\Models\AuditBeneficeCommission;
use Barryvdh\DomPDF\Facade\Pdf;
#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;
    public $activeTab = 'commissions';
    public $filterStatut = 'en_attente';
    public $filterAnnee = '';
    public $search = '';
    // Modal validation
    public $showValidationModal = false;
    public $itemType = '';
    public $itemId = '';
    public $itemToValidate = null;
    public $actionType = ''; // valider ou rejeter
    public $motifRejet = '';
    public function mount()
    {
        $this->filterAnnee = now()->year;
    }
    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterStatut() { $this->resetPage(); }
    public function updatingFilterAnnee() { $this->resetPage(); }
    private function getCommissionsQuery()
    {
        return CommissionMobileMensuelle::with('collecteur.user')
            ->when($this->filterStatut, fn($q) => $q->where('statut', $this->filterStatut))
            ->when($this->filterAnnee, fn($q) => $q->where('annee', $this->filterAnnee))
            ->when($this->search, fn($q) => $q->whereHas('collecteur.user', fn($q2) => 
                $q2->where('name', 'like', "%{$this->search}%")
            ))
            ->orderBy('created_at', 'desc');
    }
    private function getBeneficesQuery()
    {
        return BeneficeChange::with('collecteur.user')
            ->when($this->filterStatut, fn($q) => $q->where('statut', $this->filterStatut))
            ->when($this->search, fn($q) => $q->whereHas('collecteur.user', fn($q2) => 
                $q2->where('name', 'like', "%{$this->search}%")
            ))
            ->orderBy('created_at', 'desc');
    }
    public function ouvrirValidation($type, $id, $action)
    {
        $this->itemType = $type;
        $this->itemId = $id;
        $this->actionType = $action;
        $this->motifRejet = '';
        if ($type === 'commission') {
            $this->itemToValidate = CommissionMobileMensuelle::with('collecteur.user')->find($id);
        } else {
            $this->itemToValidate = BeneficeChange::with('collecteur.user')->find($id);
        }
        $this->showValidationModal = true;
    }
    public function fermerModal()
    {
        $this->showValidationModal = false;
        $this->itemToValidate = null;
        $this->reset(['itemType', 'itemId', 'actionType', 'motifRejet']);
    }
    public function confirmerAction()
    {
        if (!$this->itemToValidate) {
            session()->flash('error', 'Élément non trouvé.');
            return;
        }
        $anciennesValeurs = $this->itemToValidate->toArray();
        if ($this->actionType === 'valider') {
            $this->itemToValidate->update([
                'statut' => 'valide',
                'valide_par' => auth()->id(),
                'valide_at' => now(),
            ]);
            AuditBeneficeCommission::enregistrer(
                $this->itemType === 'commission' ? 'commissions_mobile_mensuelles' : 'benefices_change',
                $this->itemId,
                'validation',
                $anciennesValeurs,
                $this->itemToValidate->fresh()->toArray()
            );
            session()->flash('success', 'Élément validé avec succès.');
        } else {
            if (empty($this->motifRejet)) {
                $this->addError('motifRejet', 'Le motif de rejet est obligatoire.');
                return;
            }
            $this->itemToValidate->update([
                'statut' => 'rejete',
                'motif_rejet' => $this->motifRejet,
                'valide_par' => auth()->id(),
                'valide_at' => now(),
            ]);
            AuditBeneficeCommission::enregistrer(
                $this->itemType === 'commission' ? 'commissions_mobile_mensuelles' : 'benefices_change',
                $this->itemId,
                'rejet',
                $anciennesValeurs,
                $this->itemToValidate->fresh()->toArray()
            );
            session()->flash('success', 'Élément rejeté.');
        }
        $this->fermerModal();
    }
    public function exportPdf()
    {
        if ($this->activeTab === 'commissions') {
            $data = $this->getCommissionsQuery()->get();
            $pdf = Pdf::loadView('pdf.admin.commissions-benefices', [
                'items' => $data,
                'type' => 'commissions',
                'title' => 'Commissions Mobile Money - Administration',
            ]);
        } else {
            $data = $this->getBeneficesQuery()->get();
            $pdf = Pdf::loadView('pdf.admin.commissions-benefices', [
                'items' => $data,
                'type' => 'benefices',
                'title' => 'Bénéfices de Change - Administration',
            ]);
        }
        return response()->streamDownload(fn() => print($pdf->output()), 
            $this->activeTab . '_' . now()->format('Y-m-d') . '.pdf');
    }
    public function render()
    {
        $commissions = $this->activeTab === 'commissions' 
            ? $this->getCommissionsQuery()->paginate(15) 
            : collect();
        $benefices = $this->activeTab === 'benefices' 
            ? $this->getBeneficesQuery()->paginate(15) 
            : collect();
        // Stats
        $statsCommissions = [
            'en_attente' => CommissionMobileMensuelle::where('statut', 'en_attente')->count(),
            'valide' => CommissionMobileMensuelle::where('statut', 'valide')->count(),
            'total' => CommissionMobileMensuelle::where('statut', 'valide')->sum('montant_total'),
        ];
        $statsBenefices = [
            'en_attente' => BeneficeChange::where('statut', 'en_attente')->count(),
            'valide' => BeneficeChange::where('statut', 'valide')->count(),
            'total' => BeneficeChange::where('statut', 'valide')->sum('benefice'),
        ];
        return view('livewire.admin.commissions-benefices.index', compact(
            'commissions', 'benefices', 'statsCommissions', 'statsBenefices'
        ));
    }
}
