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
    public $date_versement = '';

    // Type de versement: 'journalier' ou 'arrieres'
    public $type_versement = 'journalier';

    public $motardSelectionne = null;
    public $montantJournalierAttendu = 0;
    public $soldeActuel = 0;
    public $arrieresCumules = 0;

    // Infos sur le jour sélectionné
    public $versementExistantJour = null;
    public $montantDejaVerseJour = 0;
    public $montantRestantJour = 0;

    // Vérification dimanche
    public $estDimanche = false;

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
            'type_versement' => 'required|in:journalier,arrieres',
            'date_versement' => 'required|date',
            'notes' => 'nullable|string',
        ];

        return $rules;
    }

    protected $messages = [
        'motard_id.required' => 'Veuillez sélectionner un motard.',
        'montant.required' => 'Le montant est obligatoire.',
        'montant.min' => 'Le montant doit être supérieur à 0.',
        'mode_paiement.required' => 'Veuillez choisir un mode de paiement.',
        'date_versement.required' => 'Veuillez sélectionner la date du versement.',
    ];

    public function mount()
    {
        $caissier = auth()->user()->caissier;
        $this->soldeActuel = $caissier?->solde_actuel ?? 0;
        $this->date_versement = Carbon::today()->format('Y-m-d');
    }

    public function updatedMotardId($value)
    {
        if ($value) {
            $this->motardSelectionne = Motard::with(['user', 'moto'])->find($value);
            $this->montantJournalierAttendu = $this->motardSelectionne?->moto?->montant_journalier_attendu
                ?? SystemSetting::getMontantJournalierDefaut();

            // Charger les arriérés détaillés
            $this->loadArrieresDetails();

            // Vérifier le versement existant pour le jour sélectionné
            $this->verifierVersementExistant();

            // Vérifier si c'est un dimanche
            if ($this->date_versement) {
                $this->estDimanche = Carbon::parse($this->date_versement)->isSunday();
            }

            // Pré-remplir le montant avec le montant journalier (sauf si dimanche)
            if ($this->type_versement === 'journalier' && empty($this->montant) && !$this->estDimanche) {
                $this->montant = $this->montantRestantJour > 0 ? $this->montantRestantJour : $this->montantJournalierAttendu;
            } elseif ($this->estDimanche && $this->arrieresCumules > 0) {
                // Si dimanche avec arriérés, basculer automatiquement
                $this->type_versement = 'arrieres';
                $this->montant = $this->arrieresCumules;
            }
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
        } elseif ($value === 'journalier') {
            $this->verifierVersementExistant();
            $this->montant = $this->montantRestantJour > 0 ? $this->montantRestantJour : $this->montantJournalierAttendu;
        }
        $this->updateRepartitionPreview();
    }

    public function updatedDateVersement($value)
    {
        // Vérifier si la date est un dimanche
        if ($value) {
            $this->estDimanche = Carbon::parse($value)->isSunday();

            // Si c'est dimanche et qu'on est en mode journalier, basculer vers arriérés si disponible
            if ($this->estDimanche && $this->type_versement === 'journalier') {
                if ($this->arrieresCumules > 0) {
                    $this->type_versement = 'arrieres';
                    $this->montant = $this->arrieresCumules;
                }
            }
        } else {
            $this->estDimanche = false;
        }

        if ($this->motardSelectionne) {
            $this->verifierVersementExistant();
            $this->updateRepartitionPreview();
        }
    }

    /**
     * Charger les détails des arriérés par jour
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
            ->orderBy('date_versement', 'desc')
            ->get();

        $this->arrieresDetails = $versementsAvecArrieres->map(function ($v) {
            return [
                'id' => $v->id,
                'date' => $v->date_versement?->format('d/m/Y'),
                'montant_attendu' => $v->montant_attendu,
                'montant_verse' => $v->montant,
                'arrieres' => $v->arrieres,
            ];
        })->toArray();

        $this->arrieresCumules = $versementsAvecArrieres->sum('arrieres');
    }

    /**
     * Vérifier si un versement existe déjà pour ce jour
     */
    protected function verifierVersementExistant()
    {
        $this->versementExistantJour = null;
        $this->montantDejaVerseJour = 0;
        $this->montantRestantJour = $this->montantJournalierAttendu;

        if (!$this->motardSelectionne || !$this->date_versement) {
            return;
        }

        $dateVersement = Carbon::parse($this->date_versement);

        // Chercher un versement existant pour ce jour (versement journalier uniquement, pas arrieres_only)
        $versementExistant = Versement::where('motard_id', $this->motardSelectionne->id)
            ->whereDate('date_versement', $dateVersement)
            ->whereNull('semaine_debut') // Versement journalier uniquement
            ->where(function ($query) {
                $query->where('type', 'journalier')
                      ->orWhereNull('type'); // Pour compatibilité avec anciens enregistrements
            })
            ->first();

        if ($versementExistant) {
            $this->versementExistantJour = $versementExistant;
            $this->montantDejaVerseJour = $versementExistant->montant;
            $this->montantRestantJour = max(0, $this->montantJournalierAttendu - $versementExistant->montant);

            // Si le jour est complètement payé, basculer automatiquement vers arriérés
            if ($this->montantRestantJour <= 0 && $this->arrieresCumules > 0) {
                $this->type_versement = 'arrieres';
                $this->montant = $this->arrieresCumules;
            }
        }
    }

    public function updatedMontant($value)
    {
        $this->updateRepartitionPreview();
    }

    protected function resetMotardData()
    {
        $this->motardSelectionne = null;
        $this->montantJournalierAttendu = 0;
        $this->arrieresCumules = 0;
        $this->arrieresDetails = [];
        $this->partProprietairePreview = 0;
        $this->partOkamiPreview = 0;
        $this->versementExistantJour = null;
        $this->montantDejaVerseJour = 0;
        $this->montantRestantJour = 0;
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
    public function remplirMontantJournalier()
    {
        $this->montant = $this->montantRestantJour > 0 ? $this->montantRestantJour : $this->montantJournalierAttendu;
        $this->updateRepartitionPreview();
    }

    public function remplirMontantArrieres()
    {
        $this->montant = $this->arrieresCumules;
        $this->updateRepartitionPreview();
    }

    public function remplirTotalDu()
    {
        $this->montant = $this->montantRestantJour + $this->arrieresCumules;
        $this->updateRepartitionPreview();
    }

    public function enregistrer()
    {
        $this->validate();

        $caissier = auth()->user()->caissier;

        // Vérifier que le caissier existe
        if (!$caissier) {
            session()->flash('error', 'Erreur: Votre compte n\'est pas associé à un caissier.');
            return;
        }

        // Vérifier si c'est un dimanche pour les versements journaliers
        $dateVersement = Carbon::parse($this->date_versement);
        if ($dateVersement->isSunday() && $this->type_versement === 'journalier') {
            session()->flash('error', 'Les versements journaliers ne sont pas autorisés le dimanche. Seuls les remboursements d\'arriérés sont acceptés.');
            return;
        }

        $motard = Motard::with('moto')->find($this->motard_id);

        // Vérifier que le motard existe
        if (!$motard) {
            session()->flash('error', 'Erreur: Motard introuvable.');
            return;
        }

        $moto = $motard->moto;

        // Vérifier que la moto existe
        if (!$moto) {
            session()->flash('error', 'Erreur: Ce motard n\'a pas de moto assignée.');
            return;
        }

        $montantVerse = (float) $this->montant;

        // Vérifier que le montant est valide
        if ($montantVerse <= 0) {
            $this->addError('montant', 'Le montant doit être supérieur à 0.');
            return;
        }

        // Calculer la répartition
        $partProprietaire = RepartitionService::getPartProprietaire($montantVerse);
        $partOkami = RepartitionService::getPartOkami($montantVerse);

        if ($this->type_versement === 'arrieres') {
            // Versement pour rembourser les arriérés uniquement
            return $this->enregistrerVersementArrieres($caissier, $motard, $moto, $montantVerse, $partProprietaire, $partOkami);
        }

        // Versement journalier
        return $this->enregistrerVersementJournalier($caissier, $motard, $moto, $montantVerse, $partProprietaire, $partOkami);
    }

    /**
     * Enregistrer un versement journalier
     */
    protected function enregistrerVersementJournalier($caissier, $motard, $moto, $montantVerse, $partProprietaire, $partOkami)
    {
        $dateVersement = Carbon::parse($this->date_versement);
        $montantJournalierAttendu = $moto?->montant_journalier_attendu ?? SystemSetting::getMontantJournalierDefaut();

        // Si un versement existe déjà pour ce jour, ne pas permettre un nouveau - rediriger vers la liste
        if ($this->versementExistantJour) {
            session()->flash('error', 'Un versement existe déjà pour ce motard à cette date. Veuillez utiliser le bouton "Compléter" dans la liste des versements.');
            return redirect()->route('cashier.versements.index');
        }

        // Déterminer le statut et les arriérés
        $arrieresDuJour = 0;
        $notesSupplementaires = '';

        if ($montantVerse >= $montantJournalierAttendu) {
            $statut = 'payé';
            $excedent = $montantVerse - $montantJournalierAttendu;
            if ($excedent > 0 && $this->arrieresCumules > 0) {
                $remboursementArrieres = min($excedent, $this->arrieresCumules);
                $notesSupplementaires = "Excédent de " . number_format($excedent) . " FC → " . number_format($remboursementArrieres) . " FC pour arriérés.";
                $this->rembourserArrieres($motard, $remboursementArrieres);
            }
        } else {
            $statut = 'partiellement_payé';
            $arrieresDuJour = $montantJournalierAttendu - $montantVerse;
            $notesSupplementaires = "Arriéré du jour: " . number_format($arrieresDuJour) . " FC";
        }

        $versement = Versement::create([
            'motard_id' => $motard->id,
            'moto_id' => $moto?->id,
            'caissier_id' => $caissier->id,
            'montant' => $montantVerse,
            'montant_attendu' => $montantJournalierAttendu,
            'arrieres' => $arrieresDuJour,
            'mode_paiement' => $this->mode_paiement,
            'type' => 'journalier', // Versement journalier
            'statut' => $statut,
            'date_versement' => $dateVersement,
            'part_proprietaire' => $partProprietaire,
            'part_okami' => $partOkami,
            'validated_by_caissier_at' => Carbon::now(),
            'notes' => trim(($this->notes ? $this->notes . "\n" : '') . $notesSupplementaires),
        ]);

        $caissier->increment('solde_actuel', $montantVerse);

        session()->flash('success', 'Versement journalier de ' . number_format($montantVerse) . ' FC enregistré pour le ' . $dateVersement->format('d/m/Y') . '.');
        session()->flash('dernierVersementId', $versement->id);

        return redirect()->route('cashier.versements.index');
    }

    /**
     * Compléter un versement existant (ajout au même jour)
     */
    protected function completerVersementExistant($caissier, $montantVerse, $partProprietaire, $partOkami, $motard)
    {
        $versement = $this->versementExistantJour;
        $montantJournalierAttendu = $motard->moto?->montant_journalier_attendu ?? SystemSetting::getMontantJournalierDefaut();

        $nouveauMontant = $versement->montant + $montantVerse;
        $nouveauxArrieres = max(0, $montantJournalierAttendu - $nouveauMontant);

        // Déterminer le nouveau statut
        if ($nouveauMontant >= $montantJournalierAttendu) {
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
        $excedent = $nouveauMontant - $montantJournalierAttendu;
        if ($excedent > 0 && $this->arrieresCumules > 0) {
            $this->rembourserArrieres($motard, min($excedent, $this->arrieresCumules));
        }

        session()->flash('success', 'Complément de ' . number_format($montantVerse) . ' FC ajouté. Total du jour: ' . number_format($nouveauMontant) . ' FC');
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

        // Limiter le montant au total des arriérés disponibles
        $montantEffectif = min($montantVerse, $this->arrieresCumules);

        // Rembourser les arriérés
        $this->rembourserArrieres($motard, $montantEffectif);

        // Créer un enregistrement de versement pour traçabilité avec type arrieres_only
        $versement = Versement::create([
            'motard_id' => $motard->id,
            'moto_id' => $moto?->id,
            'caissier_id' => $caissier->id,
            'montant' => $montantEffectif,
            'montant_attendu' => 0,
            'arrieres' => 0,
            'mode_paiement' => $this->mode_paiement,
            'type' => 'arrieres_only', // Marquer comme versement d'arriérés uniquement
            'statut' => 'payé',
            'date_versement' => Carbon::parse($this->date_versement),
            'part_proprietaire' => $partProprietaire,
            'part_okami' => $partOkami,
            'validated_by_caissier_at' => Carbon::now(),
            'notes' => "Remboursement arriérés" . ($this->notes ? ": " . $this->notes : ''),
        ]);

        $caissier->increment('solde_actuel', $montantEffectif);

        session()->flash('success', 'Remboursement de ' . number_format($montantEffectif) . ' FC effectué sur les arriérés.');
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
