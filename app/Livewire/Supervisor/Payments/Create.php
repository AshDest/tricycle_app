<?php

namespace App\Livewire\Supervisor\Payments;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Payment;
use App\Models\Proprietaire;
use App\Models\Collecteur;
use App\Models\Versement;
use App\Services\PaymentService;
use Carbon\Carbon;

/**
 * Formulaire de création de demande de paiement par OKAMI
 * Les demandes de paiement sont hebdomadaires (basées sur les versements journaliers de la semaine)
 */
#[Layout('components.dashlite-layout')]
class Create extends Component
{
    // Source de la caisse
    public $source_caisse = 'proprietaire';

    // Sélection de la semaine
    public $semaine_selectionnee = 0;
    public $semaines = [];

    // Pour caisse Propriétaire
    public $proprietaire_id = '';
    public $proprietaireSelectionne = null;
    public $soldeDisponible = 0;
    public $soldeHebdomadaire = 0;
    public $versementsSemaine = [];
    public $totalVersementsSemaine = 0;
    public $partProprietaireSemaine = 0;

    // Pour caisse OKAMI
    public $beneficiaire_nom = '';
    public $beneficiaire_telephone = '';
    public $beneficiaire_motif = '';
    public $soldeOkamiDisponible = 0;
    public $soldeOkamiSemaine = 0;

    // Commun
    public $montant = '';
    public $mode_paiement = 'cash';
    public $numero_compte = '';
    public $notes = '';

    protected function rules()
    {
        $rules = [
            'source_caisse' => 'required|in:proprietaire,okami',
            'semaine_selectionnee' => 'required|integer|min:0',
            'montant' => 'required|numeric|min:1',
            'mode_paiement' => 'required|in:cash,mpesa,airtel_money,orange_money,virement_bancaire',
            'numero_compte' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ];

        if ($this->source_caisse === 'proprietaire') {
            $rules['proprietaire_id'] = 'required|exists:proprietaires,id';
        } else {
            $rules['beneficiaire_nom'] = 'required|string|max:100';
            $rules['beneficiaire_telephone'] = 'nullable|string|max:20';
            $rules['beneficiaire_motif'] = 'required|string|max:500';
        }

        return $rules;
    }

    protected $messages = [
        'proprietaire_id.required' => 'Veuillez sélectionner un propriétaire.',
        'beneficiaire_nom.required' => 'Le nom du bénéficiaire est obligatoire.',
        'beneficiaire_motif.required' => 'Le motif du paiement est obligatoire.',
        'montant.required' => 'Le montant est obligatoire.',
        'montant.min' => 'Le montant doit être supérieur à 0.',
        'mode_paiement.required' => 'Le mode de paiement est obligatoire.',
        'semaine_selectionnee.required' => 'Veuillez sélectionner une semaine.',
    ];

    public function mount()
    {
        $this->loadSemaines();
        $this->calculerSoldeOkami();
    }

    /**
     * Charger les semaines disponibles (semaine courante + 4 semaines précédentes)
     */
    public function loadSemaines()
    {
        $this->semaines = [];
        $today = Carbon::now();

        // Semaine courante - Commence le lundi
        $debutSemaine = $today->copy()->startOfWeek(Carbon::MONDAY);

        // Ajouter la semaine courante et les 4 semaines précédentes
        for ($i = 0; $i < 5; $i++) {
            $debut = $debutSemaine->copy()->subWeeks($i);
            $fin = $debut->copy()->addDays(6); // Dimanche

            $numeroSemaine = $debut->weekOfYear;
            $annee = $debut->year;

            $estSemaineCourante = $i === 0;
            $label = $estSemaineCourante ? 'Semaine courante' : 'Semaine ' . $numeroSemaine;

            $this->semaines[] = [
                'index' => $i,
                'debut' => $debut->format('Y-m-d'),
                'fin' => $fin->format('Y-m-d'),
                'debut_formatted' => $debut->format('d/m'),
                'fin_formatted' => $fin->format('d/m/Y'),
                'numero' => $numeroSemaine,
                'annee' => $annee,
                'est_courante' => $estSemaineCourante,
                'label' => $label . ' du ' . $debut->format('d/m') . ' au ' . $fin->format('d/m/Y'),
            ];
        }
    }

    /**
     * Calculer le solde disponible dans la caisse OKAMI
     */
    private function calculerSoldeOkami()
    {
        // Somme des soldes OKAMI de tous les collecteurs
        $this->soldeOkamiDisponible = Collecteur::sum('solde_part_okami');

        // Calculer aussi pour la semaine sélectionnée
        $this->calculerSoldeOkamiSemaine();
    }

    /**
     * Calculer le solde OKAMI pour la semaine sélectionnée
     */
    private function calculerSoldeOkamiSemaine()
    {
        if (!isset($this->semaines[$this->semaine_selectionnee])) {
            $this->soldeOkamiSemaine = 0;
            return;
        }

        $semaineData = $this->semaines[$this->semaine_selectionnee];
        $debut = Carbon::parse($semaineData['debut']);
        $fin = Carbon::parse($semaineData['fin']);

        // Total des parts OKAMI des versements de la semaine
        $this->soldeOkamiSemaine = Versement::whereBetween('date_versement', [$debut, $fin])
            ->whereIn('statut', ['payé', 'partiellement_payé'])
            ->sum('part_okami');
    }

    /**
     * Quand la semaine sélectionnée change
     */
    public function updatedSemaineSelectionnee($value)
    {
        $this->calculerSoldeOkamiSemaine();

        if ($this->proprietaire_id) {
            $this->calculerSoldeProprietaireSemaine();
        }
    }

    /**
     * Quand la source de caisse change
     */
    public function updatedSourceCaisse($value)
    {
        // Reset les champs
        $this->proprietaire_id = '';
        $this->proprietaireSelectionne = null;
        $this->soldeDisponible = 0;
        $this->soldeHebdomadaire = 0;
        $this->versementsSemaine = [];
        $this->totalVersementsSemaine = 0;
        $this->partProprietaireSemaine = 0;
        $this->beneficiaire_nom = '';
        $this->beneficiaire_telephone = '';
        $this->beneficiaire_motif = '';
        $this->montant = '';
        $this->numero_compte = '';

        if ($value === 'okami') {
            $this->calculerSoldeOkami();
        }
    }

    /**
     * Quand le propriétaire change
     */
    public function updatedProprietaireId($value)
    {
        if ($value) {
            $proprietaire = Proprietaire::with(['user', 'motos'])->find($value);
            $this->proprietaireSelectionne = $proprietaire;

            $paymentService = new PaymentService();
            $this->soldeDisponible = $paymentService->getSoldeDisponibleProprietaire($proprietaire);

            // Calculer le solde de la semaine sélectionnée
            $this->calculerSoldeProprietaireSemaine();

            // Pré-remplir le numéro de compte selon le mode
            $this->updateNumeroCompte();
        } else {
            $this->soldeDisponible = 0;
            $this->soldeHebdomadaire = 0;
            $this->proprietaireSelectionne = null;
            $this->versementsSemaine = [];
            $this->totalVersementsSemaine = 0;
            $this->partProprietaireSemaine = 0;
        }
    }

    /**
     * Calculer le solde du propriétaire pour la semaine sélectionnée
     */
    private function calculerSoldeProprietaireSemaine()
    {
        if (!$this->proprietaireSelectionne || !isset($this->semaines[$this->semaine_selectionnee])) {
            $this->soldeHebdomadaire = 0;
            $this->versementsSemaine = [];
            $this->totalVersementsSemaine = 0;
            $this->partProprietaireSemaine = 0;
            return;
        }

        $semaineData = $this->semaines[$this->semaine_selectionnee];
        $debut = Carbon::parse($semaineData['debut']);
        $fin = Carbon::parse($semaineData['fin']);

        // Récupérer les motos du propriétaire
        $motoIds = $this->proprietaireSelectionne->motos->pluck('id')->toArray();

        // Versements de la semaine pour les motos de ce propriétaire
        $versements = Versement::whereIn('moto_id', $motoIds)
            ->whereBetween('date_versement', [$debut, $fin])
            ->whereIn('statut', ['payé', 'partiellement_payé'])
            ->with(['motard.user', 'moto'])
            ->orderBy('date_versement', 'desc')
            ->get();

        $this->versementsSemaine = $versements->map(function ($v) {
            return [
                'id' => $v->id,
                'date' => $v->date_versement?->format('d/m/Y'),
                'motard' => $v->motard?->user?->name ?? 'N/A',
                'moto' => $v->moto?->plaque_immatriculation ?? 'N/A',
                'montant' => $v->montant,
                'part_proprietaire' => $v->part_proprietaire,
                'part_okami' => $v->part_okami,
            ];
        })->toArray();

        $this->totalVersementsSemaine = $versements->sum('montant');
        $this->partProprietaireSemaine = $versements->sum('part_proprietaire');

        // Vérifier si des paiements ont déjà été faits pour cette période
        $paiementsDejaFaits = Payment::where('proprietaire_id', $this->proprietaireSelectionne->id)
            ->whereBetween('periode_debut', [$debut, $fin])
            ->whereIn('statut', ['en_attente', 'paye'])
            ->sum('total_du');

        $this->soldeHebdomadaire = max(0, $this->partProprietaireSemaine - $paiementsDejaFaits);

        // Pré-remplir le montant avec le solde de la semaine
        if ($this->soldeHebdomadaire > 0 && empty($this->montant)) {
            $this->montant = $this->soldeHebdomadaire;
        }
    }

    /**
     * Mettre à jour le numéro de compte selon le mode de paiement
     */
    public function updatedModePaiement($value)
    {
        $this->updateNumeroCompte();
    }

    private function updateNumeroCompte()
    {
        if ($this->source_caisse === 'proprietaire' && $this->proprietaireSelectionne) {
            $this->numero_compte = $this->proprietaireSelectionne->getNumeroCompte($this->mode_paiement) ?? '';
        } elseif ($this->source_caisse === 'okami' && $this->beneficiaire_telephone) {
            $this->numero_compte = $this->beneficiaire_telephone;
        }
    }

    /**
     * Remplir avec le montant de la semaine
     */
    public function remplirMontantSemaine()
    {
        if ($this->source_caisse === 'proprietaire') {
            $this->montant = $this->soldeHebdomadaire;
        } else {
            $this->montant = $this->soldeOkamiSemaine;
        }
    }

    /**
     * Remplir avec le solde total disponible
     */
    public function remplirMontantTotal()
    {
        if ($this->source_caisse === 'proprietaire') {
            $this->montant = $this->soldeDisponible;
        } else {
            $this->montant = $this->soldeOkamiDisponible;
        }
    }

    /**
     * Soumettre la demande de paiement
     */
    public function submit()
    {
        $this->validate();

        // Vérifier le solde disponible selon la source
        $soldeMax = $this->source_caisse === 'proprietaire' ? $this->soldeDisponible : $this->soldeOkamiDisponible;

        if ($this->montant > $soldeMax) {
            $this->addError('montant', "Le montant demandé dépasse le solde disponible (" . number_format($soldeMax) . " FC).");
            return;
        }

        // Récupérer les données de la semaine
        $semaineData = $this->semaines[$this->semaine_selectionnee] ?? null;
        if (!$semaineData) {
            $this->addError('semaine_selectionnee', 'Semaine invalide.');
            return;
        }

        try {
            $paymentService = new PaymentService();

            if ($this->source_caisse === 'proprietaire') {
                // Paiement depuis caisse Propriétaire
                $paymentService->creerDemandePaiementOKAMI([
                    'proprietaire_id' => $this->proprietaire_id,
                    'source_caisse' => 'proprietaire',
                    'montant' => $this->montant,
                    'mode_paiement' => $this->mode_paiement,
                    'numero_compte' => $this->numero_compte,
                    'notes' => $this->notes,
                    'periode_debut' => $semaineData['debut'],
                    'periode_fin' => $semaineData['fin'],
                    'numero_semaine' => $semaineData['numero'],
                ], auth()->id());

                session()->flash('success', 'Demande de paiement pour la semaine ' . $semaineData['numero'] . ' soumise avec succès.');
            } else {
                // Paiement depuis caisse OKAMI
                $paymentService->creerDemandePaiementDepuisOKAMI([
                    'source_caisse' => 'okami',
                    'beneficiaire_nom' => $this->beneficiaire_nom,
                    'beneficiaire_telephone' => $this->beneficiaire_telephone,
                    'beneficiaire_motif' => $this->beneficiaire_motif,
                    'montant' => $this->montant,
                    'mode_paiement' => $this->mode_paiement,
                    'numero_compte' => $this->numero_compte ?: $this->beneficiaire_telephone,
                    'notes' => $this->notes,
                    'periode_debut' => $semaineData['debut'],
                    'periode_fin' => $semaineData['fin'],
                ], auth()->id());

                session()->flash('success', 'Demande de paiement OKAMI pour la semaine ' . $semaineData['numero'] . ' soumise avec succès.');
            }

            return redirect()->route('supervisor.payments.index');
        } catch (\Exception $e) {
            $this->addError('montant', $e->getMessage());
        }
    }

    public function render()
    {
        $paymentService = new PaymentService();
        $proprietaires = $paymentService->getProprietairesAvecSolde();

        return view('livewire.supervisor.payments.create', [
            'proprietaires' => $proprietaires,
            'modesPaiement' => Payment::getModesPaiement(),
            'sourcesCaisse' => Payment::getSourcesCaisse(),
        ]);
    }
}
