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
    public $motard_secondaire_id = '';
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

    // Liste des motards secondaires (sans moto assignée)
    public $motardsSecondaires = [];

    // Jours manquants (sans versement) et jours sélectionnés par le caissier
    public $joursManquants = [];
    public $joursSelectionnes = [];

    protected function rules()
    {
        $rules = [
            'motard_id' => 'required|exists:motards,id',
            'motard_secondaire_id' => 'nullable|exists:motards,id',
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

            // Charger les motards secondaires (sans moto assignée, différents du titulaire)
            $this->motardsSecondaires = Motard::with('user')
                ->where('is_active', true)
                ->where('id', '!=', $value)
                ->whereDoesntHave('moto', function ($q) {
                    $q->where('statut', 'actif');
                })
                ->get()
                ->toArray();

            // Reset le motard secondaire
            $this->motard_secondaire_id = '';

            // Charger les arriérés détaillés
            $this->loadArrieresDetails();

            // Charger les jours manquants (sans versement)
            $this->loadJoursManquants();

            // Vérifier le versement existant pour le jour sélectionné
            $this->verifierVersementExistant();

            // Vérifier si c'est un dimanche
            if ($this->date_versement) {
                $this->estDimanche = Carbon::parse($this->date_versement)->isSunday();
            }

            // Si on est en mode journalier, auto-sélectionner aujourd'hui s'il est dans les jours manquants
            if ($this->type_versement === 'journalier') {
                $today = Carbon::today()->format('Y-m-d');
                if (in_array($today, array_column($this->joursManquants, 'date'))) {
                    $this->joursSelectionnes = [$today];
                    $this->date_versement = $today;
                    $this->montant = $this->montantJournalierAttendu;
                } elseif (count($this->joursManquants) > 0) {
                    // Sélectionner le premier jour manquant
                    $premierJour = $this->joursManquants[0]['date'];
                    $this->joursSelectionnes = [$premierJour];
                    $this->date_versement = $premierJour;
                    $this->montant = $this->montantJournalierAttendu;
                } else {
                    $this->joursSelectionnes = [];
                    $this->montant = '';
                }
            }

            if ($this->estDimanche && $this->arrieresCumules > 0) {
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
     * Charger les jours ouvrables sans versement (Lun-Sam) sur les 30 derniers jours
     */
    protected function loadJoursManquants()
    {
        $this->joursManquants = [];
        $this->joursSelectionnes = [];

        if (!$this->motardSelectionne) {
            return;
        }

        $today = Carbon::today();
        // Remonter jusqu'à 30 jours en arrière (ou début du mois précédent)
        $dateDebut = $today->copy()->subDays(30)->startOfDay();

        // Récupérer toutes les dates ayant un versement journalier pour ce motard
        $datesAvecVersement = Versement::where('motard_id', $this->motardSelectionne->id)
            ->whereBetween('date_versement', [$dateDebut, $today])
            ->where(function ($query) {
                $query->where('type', 'journalier')
                      ->orWhereNull('type');
            })
            ->pluck('date_versement')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        // Parcourir chaque jour ouvrable (Lun-Sam) et vérifier s'il manque un versement
        $cursor = $dateDebut->copy();
        $jours = [];

        while ($cursor->lte($today)) {
            // Exclure les dimanches
            if (!$cursor->isSunday()) {
                $dateStr = $cursor->format('Y-m-d');
                if (!in_array($dateStr, $datesAvecVersement)) {
                    $jours[] = [
                        'date' => $dateStr,
                        'date_formatted' => $cursor->translatedFormat('D d/m/Y'),
                        'jour_semaine' => $cursor->translatedFormat('l'),
                        'est_aujourdhui' => $cursor->isToday(),
                        'est_cette_semaine' => $cursor->isCurrentWeek(),
                    ];
                }
            }
            $cursor->addDay();
        }

        // Trier du plus récent au plus ancien
        $this->joursManquants = array_reverse($jours);
    }

    /**
     * Quand les jours sélectionnés changent → recalculer le montant
     */
    public function updatedJoursSelectionnes()
    {
        $nbJours = count($this->joursSelectionnes);
        if ($nbJours > 0 && $this->type_versement === 'journalier') {
            $this->montant = $nbJours * $this->montantJournalierAttendu;
            // Mettre la date_versement sur le premier jour sélectionné (pour l'enregistrement)
            sort($this->joursSelectionnes);
            $this->date_versement = $this->joursSelectionnes[0];
        } elseif ($nbJours === 0) {
            $this->montant = '';
        }
        $this->updateRepartitionPreview();
    }

    /**
     * Sélectionner / désélectionner un jour manquant
     */
    public function toggleJour($date)
    {
        if (in_array($date, $this->joursSelectionnes)) {
            $this->joursSelectionnes = array_values(array_diff($this->joursSelectionnes, [$date]));
        } else {
            $this->joursSelectionnes[] = $date;
        }
        $this->updatedJoursSelectionnes();
    }

    /**
     * Sélectionner tous les jours manquants
     */
    public function selectionnerTousLesJours()
    {
        $this->joursSelectionnes = array_column($this->joursManquants, 'date');
        $this->updatedJoursSelectionnes();
    }

    /**
     * Désélectionner tous les jours
     */
    public function deselectionnerTousLesJours()
    {
        $this->joursSelectionnes = [];
        $this->montant = '';
        $this->updateRepartitionPreview();
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
        $this->motard_secondaire_id = '';
        $this->motardsSecondaires = [];
        $this->joursManquants = [];
        $this->joursSelectionnes = [];
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

        // Plus de scission OKAMI/Propriétaire - tout va dans une caisse unique
        $partProprietaire = $montantVerse;
        $partOkami = 0;

        if ($this->type_versement === 'arrieres') {
            // Versement pour rembourser les arriérés uniquement
            return $this->enregistrerVersementArrieres($caissier, $motard, $moto, $montantVerse, $partProprietaire, $partOkami);
        }

        // Versement journalier - avec gestion multi-jours
        if (count($this->joursSelectionnes) > 1) {
            return $this->enregistrerVersementsMultiJours($caissier, $motard, $moto, $montantVerse);
        }

        // Versement journalier classique (1 seul jour)
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
            'motard_secondaire_id' => $this->motard_secondaire_id ?: null,
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
     * Enregistrer des versements journaliers pour plusieurs jours sélectionnés
     */
    protected function enregistrerVersementsMultiJours($caissier, $motard, $moto, $montantTotal)
    {
        $montantJournalierAttendu = $moto?->montant_journalier_attendu ?? SystemSetting::getMontantJournalierDefaut();
        $nbJours = count($this->joursSelectionnes);
        $montantParJour = $montantJournalierAttendu;

        // Vérifier que le montant total correspond bien
        $montantAttenduTotal = $nbJours * $montantParJour;

        sort($this->joursSelectionnes);
        $dernierVersementId = null;
        $joursEnregistres = 0;

        foreach ($this->joursSelectionnes as $dateStr) {
            $dateVersement = Carbon::parse($dateStr);

            // Vérifier qu'il n'existe pas déjà un versement pour ce jour
            $existant = Versement::where('motard_id', $motard->id)
                ->whereDate('date_versement', $dateVersement)
                ->where(function ($q) {
                    $q->where('type', 'journalier')->orWhereNull('type');
                })
                ->whereNull('semaine_debut')
                ->exists();

            if ($existant) {
                continue; // Ignorer les jours déjà payés
            }

            $versement = Versement::create([
                'motard_id' => $motard->id,
                'motard_secondaire_id' => $this->motard_secondaire_id ?: null,
                'moto_id' => $moto?->id,
                'caissier_id' => $caissier->id,
                'montant' => $montantParJour,
                'montant_attendu' => $montantParJour,
                'arrieres' => 0,
                'mode_paiement' => $this->mode_paiement,
                'type' => 'journalier',
                'statut' => 'payé',
                'date_versement' => $dateVersement,
                'part_proprietaire' => $montantParJour,
                'part_okami' => 0,
                'validated_by_caissier_at' => Carbon::now(),
                'notes' => trim(($this->notes ? $this->notes . "\n" : '') . "Versement groupé ({$nbJours} jours)"),
            ]);

            $dernierVersementId = $versement->id;
            $joursEnregistres++;
        }

        if ($joursEnregistres > 0) {
            $caissier->increment('solde_actuel', $montantTotal);

            $premierJour = Carbon::parse($this->joursSelectionnes[0])->format('d/m/Y');
            $dernierJour = Carbon::parse(end($this->joursSelectionnes))->format('d/m/Y');

            session()->flash('success', "Versement de " . number_format($montantTotal) . " FC enregistré pour {$joursEnregistres} jour(s) ({$premierJour} → {$dernierJour}).");
            session()->flash('dernierVersementId', $dernierVersementId);
        } else {
            session()->flash('error', 'Aucun versement créé - tous les jours sélectionnés ont déjà un versement.');
        }

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

        // Plus de scission - tout va dans une caisse unique
        $nouvPartProprietaire = $nouveauMontant;
        $nouvPartOkami = 0;

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
            'motard_secondaire_id' => $this->motard_secondaire_id ?: null,
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

        // Motards secondaires (sans moto assignée active)
        $motardsSecondairesList = [];
        if ($this->motard_id) {
            $motardsSecondairesList = Motard::with('user')
                ->where('is_active', true)
                ->where('id', '!=', $this->motard_id)
                ->whereDoesntHave('moto', function ($q) {
                    $q->where('statut', 'actif');
                })
                ->get();
        }

        return view('livewire.cashier.versements.create', compact('motards', 'motardsSecondairesList'));
    }
}
