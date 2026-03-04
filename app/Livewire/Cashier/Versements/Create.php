<?php

namespace App\Livewire\Cashier\Versements;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Versement;
use App\Models\Motard;
use App\Models\SystemSetting;
use App\Services\RepartitionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Create extends Component
{
    public $motard_id = '';
    public $montant = '';
    public $mode_paiement = 'cash';
    public $notes = '';

    // Type de versement: 'semaine' ou 'arrieres'
    public $type_versement = 'semaine';

    // Sélection de la semaine
    public $semaine_selectionnee = '';
    public $semaines = [];

    public $motardSelectionne = null;
    public $montantAttendu = 0;
    public $montantHebdomadaireAttendu = 0;
    public $montantJournalier = 0;
    public $joursEcoules = 6;
    public $soldeActuel = 0;
    public $arrieresCumules = 0;
    public $tauxPaiement = 0;

    // Infos sur la semaine sélectionnée
    public $semaineDejaVersee = false;
    public $versementExistant = null;
    public $montantDejaVerse = 0;
    public $montantRestantSemaine = 0;

    // Historique des arriérés détaillés
    public $arrieresDetails = [];

    // Répartition prévisionnelle
    public $partProprietairePreview = 0;
    public $partOkamiPreview = 0;

    // Pour le téléchargement du reçu
    public $dernierVersementId = null;

    protected function rules()
    {
        $rules = [
            'motard_id' => 'required|exists:motards,id',
            'montant' => 'required|numeric|min:1',
            'mode_paiement' => 'required|in:cash,mobile_money,depot',
            'type_versement' => 'required|in:semaine,arrieres',
            'notes' => 'nullable|string',
        ];

        if ($this->type_versement === 'semaine') {
            $rules['semaine_selectionnee'] = 'required';
        }

        return $rules;
    }

    protected $messages = [
        'motard_id.required' => 'Veuillez sélectionner un motard.',
        'montant.required' => 'Le montant est obligatoire.',
        'montant.min' => 'Le montant doit être supérieur à 0.',
        'mode_paiement.required' => 'Veuillez choisir un mode de paiement.',
        'semaine_selectionnee.required' => 'Veuillez sélectionner la semaine concernée.',
    ];

    public function mount()
    {
        $caissier = auth()->user()->caissier;
        $this->soldeActuel = $caissier->solde_actuel ?? 0;
        $this->loadSemaines();

        // Sélectionner la semaine courante par défaut
        if (count($this->semaines) > 0) {
            $this->semaine_selectionnee = 0;
        }
    }

    /**
     * Charger les semaines disponibles selon le calendrier civil
     * Semaine de travail = Lundi à Samedi (6 jours)
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
            $fin = $debut->copy()->addDays(5); // Samedi (6 jours: Lun-Sam)

            // Calculer les jours travaillés dans cette semaine
            $joursEcoules = $this->getJoursTravaillesEcoules($debut, $fin, $today);
            $estSemaineCourante = $i === 0;

            // Numéro de semaine selon ISO
            $numeroSemaine = $debut->weekOfYear;
            $annee = $debut->year;

            // Libellé de la semaine
            if ($estSemaineCourante) {
                $label = 'Semaine courante';
                if ($joursEcoules < 6) {
                    $label .= ' (' . $joursEcoules . '/6 jours)';
                }
            } else {
                $label = 'Semaine ' . $numeroSemaine;
            }

            $this->semaines[] = [
                'index' => $i,
                'debut' => $debut->format('Y-m-d'),
                'fin' => $fin->format('Y-m-d'),
                'debut_formatted' => $debut->format('d/m'),
                'fin_formatted' => $fin->format('d/m/Y'),
                'numero' => $numeroSemaine,
                'annee' => $annee,
                'jours_ecoules' => $joursEcoules,
                'est_courante' => $estSemaineCourante,
                'est_complete' => $joursEcoules === 6 || $fin->isPast(),
                'label' => $label . ' du ' . $debut->format('d/m') . ' au ' . $fin->format('d/m/Y'),
            ];
        }
    }

    /**
     * Calculer le nombre de jours de travail écoulés dans une semaine
     */
    protected function getJoursTravaillesEcoules(Carbon $debut, Carbon $fin, Carbon $today): int
    {
        if ($today->isAfter($fin)) {
            return 6;
        }

        if ($today->isBefore($debut)) {
            return 0;
        }

        $joursEcoules = $today->diffInDays($debut) + 1;
        return min(6, max(0, $joursEcoules));
    }

    public function updatedMotardId($value)
    {
        if ($value) {
            $this->motardSelectionne = Motard::with(['user', 'moto'])->find($value);
            $this->montantJournalier = $this->motardSelectionne?->moto?->montant_journalier_attendu ?? SystemSetting::getMontantJournalierDefaut();
            $this->tauxPaiement = $this->motardSelectionne?->taux_paiement ?? 100;

            // Charger les arriérés détaillés
            $this->loadArrieresDetails();

            // Recalculer les montants avec la semaine sélectionnée
            $this->calculerMontantsSelonSemaine();
            $this->verifierVersementExistant();
        } else {
            $this->resetMotardData();
        }
        $this->updateRepartitionPreview();
    }

    public function updatedTypeVersement($value)
    {
        if ($value === 'arrieres' && $this->motardSelectionne) {
            $this->loadArrieresDetails();
            // Pré-remplir avec le total des arriérés si versement arriérés
            $this->montant = $this->arrieresCumules;
        } elseif ($value === 'semaine') {
            $this->verifierVersementExistant();
        }
        $this->updateRepartitionPreview();
    }

    public function updatedSemaineSelectionnee($value)
    {
        if ($this->motardSelectionne) {
            $this->calculerMontantsSelonSemaine();
            $this->verifierVersementExistant();
            $this->updateRepartitionPreview();
        }
    }

    /**
     * Charger les détails des arriérés par semaine
     */
    protected function loadArrieresDetails()
    {
        if (!$this->motardSelectionne) {
            $this->arrieresDetails = [];
            $this->arrieresCumules = 0;
            return;
        }

        $versementsAvecArrieres = Versement::where('motard_id', $this->motardSelectionne->id)
            ->where('arrieres', '>', 0)
            ->orderBy('semaine_debut', 'desc')
            ->get();

        $this->arrieresDetails = $versementsAvecArrieres->map(function ($v) {
            return [
                'id' => $v->id,
                'semaine_numero' => $v->numero_semaine,
                'semaine_debut' => $v->semaine_debut?->format('d/m'),
                'semaine_fin' => $v->semaine_fin?->format('d/m/Y'),
                'montant_attendu' => $v->montant_attendu,
                'montant_verse' => $v->montant,
                'arrieres' => $v->arrieres,
                'date_versement' => $v->date_versement?->format('d/m/Y'),
            ];
        })->toArray();

        $this->arrieresCumules = $versementsAvecArrieres->sum('arrieres');
    }

    /**
     * Vérifier si un versement existe déjà pour cette semaine
     */
    protected function verifierVersementExistant()
    {
        $this->semaineDejaVersee = false;
        $this->versementExistant = null;
        $this->montantDejaVerse = 0;
        $this->montantRestantSemaine = $this->montantHebdomadaireAttendu;

        if (!$this->motardSelectionne || !isset($this->semaines[$this->semaine_selectionnee])) {
            return;
        }

        $semaineData = $this->semaines[$this->semaine_selectionnee];
        $semaineDebut = Carbon::parse($semaineData['debut']);
        $semaineFin = Carbon::parse($semaineData['fin']);

        // Chercher un versement existant pour cette semaine
        $versementExistant = Versement::where('motard_id', $this->motardSelectionne->id)
            ->where(function ($q) use ($semaineDebut, $semaineFin) {
                $q->where('semaine_debut', $semaineDebut->format('Y-m-d'))
                  ->orWhereBetween('date_versement', [$semaineDebut, $semaineFin]);
            })
            ->first();

        if ($versementExistant) {
            $this->semaineDejaVersee = true;
            $this->versementExistant = $versementExistant;
            $this->montantDejaVerse = $versementExistant->montant;
            $this->montantRestantSemaine = max(0, $this->montantHebdomadaireAttendu - $versementExistant->montant);

            // Si la semaine est complètement payée, basculer automatiquement vers arriérés
            if ($this->montantRestantSemaine <= 0 && $this->arrieresCumules > 0) {
                $this->type_versement = 'arrieres';
            }
        }
    }

    /**
     * Calculer les montants attendus selon la semaine civile sélectionnée
     */
    protected function calculerMontantsSelonSemaine()
    {
        if (!$this->motardSelectionne || !isset($this->semaines[$this->semaine_selectionnee])) {
            return;
        }

        $semaineData = $this->semaines[$this->semaine_selectionnee];
        $this->joursEcoules = $semaineData['jours_ecoules'];
        $this->montantAttendu = $this->montantJournalier;
        $this->montantHebdomadaireAttendu = $this->montantJournalier * RepartitionService::JOURS_SEMAINE;
    }

    public function updatedMontant($value)
    {
        $this->updateRepartitionPreview();
    }

    protected function resetMotardData()
    {
        $this->motardSelectionne = null;
        $this->montantAttendu = 0;
        $this->montantHebdomadaireAttendu = 0;
        $this->montantJournalier = 0;
        $this->joursEcoules = 6;
        $this->arrieresCumules = 0;
        $this->arrieresDetails = [];
        $this->tauxPaiement = 0;
        $this->partProprietairePreview = 0;
        $this->partOkamiPreview = 0;
        $this->semaineDejaVersee = false;
        $this->versementExistant = null;
        $this->montantDejaVerse = 0;
        $this->montantRestantSemaine = 0;
    }

    protected function updateRepartitionPreview()
    {
        $montant = (float) $this->montant;
        if ($montant > 0) {
            $this->partProprietairePreview = RepartitionService::getPartProprietaire($montant);
            $this->partOkamiPreview = RepartitionService::getPartOkami($montant);
        } else {
            $this->partProprietairePreview = 0;
            $this->partOkamiPreview = 0;
        }
    }

    /**
     * Remplir automatiquement le montant selon le type
     */
    public function remplirMontantSemaine()
    {
        $this->montant = $this->montantRestantSemaine;
        $this->updateRepartitionPreview();
    }

    public function remplirMontantArrieres()
    {
        $this->montant = $this->arrieresCumules;
        $this->updateRepartitionPreview();
    }

    public function remplirTotalDu()
    {
        $this->montant = $this->montantRestantSemaine + $this->arrieresCumules;
        $this->updateRepartitionPreview();
    }

    public function enregistrer()
    {
        $this->validate();

        $caissier = auth()->user()->caissier;
        $motard = Motard::with('moto')->find($this->motard_id);
        $moto = $motard->moto;
        $montantVerse = (float) $this->montant;

        // Calculer la répartition
        $partProprietaire = RepartitionService::getPartProprietaire($montantVerse);
        $partOkami = RepartitionService::getPartOkami($montantVerse);

        if ($this->type_versement === 'arrieres') {
            // Versement pour rembourser les arriérés uniquement
            return $this->enregistrerVersementArrieres($caissier, $motard, $moto, $montantVerse, $partProprietaire, $partOkami);
        }

        // Versement pour une semaine
        return $this->enregistrerVersementSemaine($caissier, $motard, $moto, $montantVerse, $partProprietaire, $partOkami);
    }

    /**
     * Enregistrer un versement pour une semaine
     */
    protected function enregistrerVersementSemaine($caissier, $motard, $moto, $montantVerse, $partProprietaire, $partOkami)
    {
        $semaineData = $this->semaines[$this->semaine_selectionnee] ?? null;
        if (!$semaineData) {
            session()->flash('error', 'Semaine invalide.');
            return;
        }

        $semaineDebut = Carbon::parse($semaineData['debut']);
        $semaineFin = Carbon::parse($semaineData['fin']);
        $numeroSemaine = $semaineData['numero'];

        $montantJournalier = $moto?->montant_journalier_attendu ?? SystemSetting::getMontantJournalierDefaut();
        $montantHebdomadaireAttendu = $montantJournalier * RepartitionService::JOURS_SEMAINE;

        // Si un versement existe déjà pour cette semaine, le compléter
        if ($this->semaineDejaVersee && $this->versementExistant) {
            return $this->completerVersementExistant($caissier, $montantVerse, $partProprietaire, $partOkami, $motard);
        }

        // Déterminer le statut et les arriérés
        $arrieresDuJour = 0;
        $notesSupplementaires = '';

        if ($montantVerse >= $montantHebdomadaireAttendu) {
            $statut = 'payé';
            $excedent = $montantVerse - $montantHebdomadaireAttendu;
            if ($excedent > 0 && $this->arrieresCumules > 0) {
                $remboursementArrieres = min($excedent, $this->arrieresCumules);
                $notesSupplementaires = "Excédent de " . number_format($excedent) . " FC → " . number_format($remboursementArrieres) . " FC pour arriérés.";
                $this->rembourserArrieres($motard, $remboursementArrieres);
            }
        } else {
            $statut = 'partiellement_payé';
            $arrieresDuJour = $montantHebdomadaireAttendu - $montantVerse;
            $notesSupplementaires = "Arriéré semaine: " . number_format($arrieresDuJour) . " FC";
        }

        $versement = Versement::create([
            'motard_id' => $motard->id,
            'moto_id' => $moto?->id,
            'caissier_id' => $caissier->id,
            'montant' => $montantVerse,
            'montant_attendu' => $montantHebdomadaireAttendu,
            'arrieres' => $arrieresDuJour,
            'mode_paiement' => $this->mode_paiement,
            'statut' => $statut,
            'date_versement' => Carbon::today(),
            'semaine_debut' => $semaineDebut,
            'semaine_fin' => $semaineFin,
            'numero_semaine' => $numeroSemaine,
            'part_proprietaire' => $partProprietaire,
            'part_okami' => $partOkami,
            'validated_by_caissier_at' => Carbon::now(),
            'notes' => trim(($this->notes ? $this->notes . "\n" : '') . $notesSupplementaires),
        ]);

        $caissier->increment('solde_actuel', $montantVerse);

        session()->flash('success', 'Versement de ' . number_format($montantVerse) . ' FC enregistré pour la semaine ' . $numeroSemaine . '.');
        session()->flash('dernierVersementId', $versement->id);

        return redirect()->route('cashier.versements.index');
    }

    /**
     * Compléter un versement existant (ajout à une semaine déjà versée)
     */
    protected function completerVersementExistant($caissier, $montantVerse, $partProprietaire, $partOkami, $motard)
    {
        $versement = $this->versementExistant;
        $montantJournalier = $motard->moto?->montant_journalier_attendu ?? SystemSetting::getMontantJournalierDefaut();
        $montantHebdomadaireAttendu = $montantJournalier * RepartitionService::JOURS_SEMAINE;

        $nouveauMontant = $versement->montant + $montantVerse;
        $nouveauxArrieres = max(0, $montantHebdomadaireAttendu - $nouveauMontant);

        // Déterminer le nouveau statut
        if ($nouveauMontant >= $montantHebdomadaireAttendu) {
            $nouveauStatut = 'payé';
        } else {
            $nouveauStatut = 'partiellement_payé';
        }

        // Recalculer la répartition totale
        $nouvPartProprietaire = RepartitionService::getPartProprietaire($nouveauMontant);
        $nouvPartOkami = RepartitionService::getPartOkami($nouveauMontant);

        $noteComplement = "[Complément de " . number_format($montantVerse) . " FC le " . now()->format('d/m/Y H:i') . "]";

        $versement->update([
            'montant' => $nouveauMontant,
            'arrieres' => $nouveauxArrieres,
            'statut' => $nouveauStatut,
            'part_proprietaire' => $nouvPartProprietaire,
            'part_okami' => $nouvPartOkami,
            'notes' => ($versement->notes ? $versement->notes . "\n" : '') . $noteComplement . ($this->notes ? "\n" . $this->notes : ''),
        ]);

        $caissier->increment('solde_actuel', $montantVerse);

        // Si excédent, rembourser les arriérés
        $excedent = $nouveauMontant - $montantHebdomadaireAttendu;
        if ($excedent > 0 && $this->arrieresCumules > 0) {
            $this->rembourserArrieres($motard, min($excedent, $this->arrieresCumules));
        }

        session()->flash('success', 'Complément de ' . number_format($montantVerse) . ' FC ajouté à la semaine ' . $versement->numero_semaine . '. Total: ' . number_format($nouveauMontant) . ' FC');
        session()->flash('dernierVersementId', $versement->id);

        return redirect()->route('cashier.versements.index');
    }

    /**
     * Enregistrer un versement uniquement pour les arriérés
     */
    protected function enregistrerVersementArrieres($caissier, $motard, $moto, $montantVerse, $partProprietaire, $partOkami)
    {
        if ($this->arrieresCumules <= 0) {
            session()->flash('error', 'Aucun arriéré à rembourser pour ce motard.');
            return;
        }

        // Rembourser les arriérés
        $this->rembourserArrieres($motard, $montantVerse);

        // Créer un enregistrement de versement pour traçabilité
        $versement = Versement::create([
            'motard_id' => $motard->id,
            'moto_id' => $moto?->id,
            'caissier_id' => $caissier->id,
            'montant' => $montantVerse,
            'montant_attendu' => 0,
            'arrieres' => 0,
            'mode_paiement' => $this->mode_paiement,
            'statut' => 'payé',
            'date_versement' => Carbon::today(),
            'semaine_debut' => null,
            'semaine_fin' => null,
            'numero_semaine' => null,
            'part_proprietaire' => $partProprietaire,
            'part_okami' => $partOkami,
            'validated_by_caissier_at' => Carbon::now(),
            'notes' => "Remboursement arriérés" . ($this->notes ? ": " . $this->notes : ''),
        ]);

        $caissier->increment('solde_actuel', $montantVerse);

        session()->flash('success', 'Remboursement de ' . number_format($montantVerse) . ' FC effectué sur les arriérés.');
        session()->flash('dernierVersementId', $versement->id);

        return redirect()->route('cashier.versements.index');
    }

    /**
     * Rembourser les arriérés des anciens versements
     */
    protected function rembourserArrieres(Motard $motard, float $montantRemboursement)
    {
        if ($montantRemboursement <= 0) return;

        $versementsAvecArrieres = Versement::where('motard_id', $motard->id)
            ->where('arrieres', '>', 0)
            ->orderBy('date_versement', 'asc')
            ->get();

        $restant = $montantRemboursement;

        foreach ($versementsAvecArrieres as $versement) {
            if ($restant <= 0) break;

            $arriereActuel = $versement->arrieres;
            $remboursement = min($restant, $arriereActuel);

            $nouveauMontant = $versement->montant + $remboursement;
            $nouveauxArrieres = $arriereActuel - $remboursement;
            $nouveauStatut = $nouveauxArrieres <= 0 ? 'payé' : 'partiellement_payé';

            $partProprietaire = RepartitionService::getPartProprietaire($nouveauMontant);
            $partOkami = RepartitionService::getPartOkami($nouveauMontant);

            $versement->update([
                'montant' => $nouveauMontant,
                'arrieres' => max(0, $nouveauxArrieres),
                'statut' => $nouveauStatut,
                'part_proprietaire' => $partProprietaire,
                'part_okami' => $partOkami,
                'notes' => ($versement->notes ? $versement->notes . "\n" : '') .
                           "[Remboursement de " . number_format($remboursement) . " FC le " . now()->format('d/m/Y') . "]",
            ]);

            $restant -= $remboursement;
        }
    }

    public function telechargerRecu($versementId)
    {
        $versement = Versement::with(['motard.user', 'moto', 'caissier.user'])->findOrFail($versementId);

        $pdf = Pdf::loadView('pdf.recu-versement', compact('versement'));
        $pdf->setPaper([0, 0, 226.77, 566.93], 'portrait');

        $filename = 'recu_versement_' . $versement->id . '_' . now()->format('YmdHis') . '.pdf';

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    public function render()
    {
        $motards = Motard::with(['user', 'moto'])
            ->where('is_active', true)
            ->whereHas('moto')
            ->get();

        return view('livewire.cashier.versements.create', compact('motards'));
    }
}
