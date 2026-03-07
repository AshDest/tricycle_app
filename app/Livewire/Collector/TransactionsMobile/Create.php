<?php
namespace App\Livewire\Collector\TransactionsMobile;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\TransactionMobile;

#[Layout('components.dashlite-layout')]
class Create extends Component
{
    public $type = 'envoi';
    public $montant = '';
    public $frais = 0;
    public $operateur = 'mpesa';
    public $numero_telephone = '';
    public $nom_beneficiaire = '';
    public $reference_operateur = '';
    public $motif = '';
    public $notes = '';
    protected function rules()
    {
        return [
            'type' => 'required|in:envoi,retrait',
            'montant' => 'required|numeric|min:1',
            'frais' => 'nullable|numeric|min:0',
            'operateur' => 'required|in:mpesa,airtel_money,orange_money,afrimoney',
            'numero_telephone' => 'required|string|max:20',
            'nom_beneficiaire' => 'nullable|string|max:100',
            'reference_operateur' => 'nullable|string|max:50',
            'motif' => 'required|string|max:500',
            'notes' => 'nullable|string|max:500',
        ];
    }
    protected $messages = [
        'montant.required' => 'Le montant est obligatoire.',
        'montant.min' => 'Le montant doit être supérieur à 0.',
        'numero_telephone.required' => 'Le numéro de téléphone est obligatoire.',
        'motif.required' => 'Le motif de la transaction est obligatoire.',
    ];
    public function getMontantNetProperty()
    {
        $montant = floatval($this->montant) ?: 0;
        $frais = floatval($this->frais) ?: 0;
        return $this->type === 'envoi' ? $montant + $frais : $montant - $frais;
    }
    public function submit()
    {
        $this->validate();
        $collecteur = auth()->user()->collecteur;
        if (!$collecteur) {
            session()->flash('error', 'Collecteur non trouvé.');
            return;
        }
        try {
            TransactionMobile::create([
                'collecteur_id' => $collecteur->id,
                'type' => $this->type,
                'montant' => $this->montant,
                'frais' => $this->frais ?: 0,
                'montant_net' => $this->montant_net,
                'operateur' => $this->operateur,
                'numero_telephone' => $this->numero_telephone,
                'nom_beneficiaire' => $this->nom_beneficiaire,
                'reference_operateur' => $this->reference_operateur,
                'motif' => $this->motif,
                'notes' => $this->notes,
                'statut' => 'complete',
                'date_transaction' => now(),
            ]);
            $typeLabel = $this->type === 'envoi' ? 'Envoi' : 'Retrait';
            session()->flash('success', "{$typeLabel} de " . number_format($this->montant) . " FC enregistré avec succès.");
            return redirect()->route('collector.transactions-mobile.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }
    public function render()
    {
        return view('livewire.collector.transactions-mobile.create', [
            'types' => TransactionMobile::getTypes(),
            'operateurs' => TransactionMobile::getOperateurs(),
        ]);
    }
}
