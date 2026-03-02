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

    // Répartition prévisionnelle
    public $partProprietairePreview = 0;
    public $partOkamiPreview = 0;

    // Pour le téléchargement du reçu
    public $dernierVersementId = null;

    protected function rules()
    {
        return [
            'motard_id' => 'required|exists:motards,id',
            'montant' => 'required|numeric|min:1',
            'mode_paiement' => 'required|in:cash,mobile_money,depot',
            'semaine_selectionnee' => 'required',
            'notes' => 'nullable|string',
        ];
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

        // Déterminer si on est dans la semaine courante ou si elle est déjà passée
        $samediSemaineCourante = $debutSemaine->copy()->addDays(5); // Samedi

        // Ajouter la semaine courante et les 4 semaines précédentes
        for ($i = 0; $i < 5; $i++) {
            $debut = $debutSemaine->copy()->subWeeks($i);
            $fin = $debut->copy()->addDays(5); // Samedi (6 jours: Lun-Sam)

            // Calculer les jours travaillés dans cette semaine
            $joursEcoules = $this->getJoursTravaillesEcoules($debut, $fin, $today);
            $estSemaineCourante = $i === 0;
            $semaineFuture = $debut->isAfter($today);

            // Numéro de semaine selon ISO
            $numeroSemaine = $debut->weekOfYear;
            $annee = $debut->year;

            // Libellé de la semaine
            if ($estSemaineCourante) {
                $label = 'Semaine courante';
                if ($joursEcoules < 6) {
                    $label .= ' (' . $joursEcoules . '/6 jours écoulés)';
                }
            } else {
                $label = 'Semaine';
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
            return 6; // Semaine complète
        }

        if ($today->isBefore($debut)) {
            return 0; // Semaine future
        }

        // Jours écoulés depuis le lundi
        $joursEcoules = $today->diffInDays($debut) + 1;

        // Ne pas dépasser 6 jours (Lun-Sam)
        return min(6, max(0, $joursEcoules));
    }

    public function updatedMotardId($value)
    {
        if ($value) {
            $this->motardSelectionne = Motard::with(['user', 'moto'])->find($value);
            $this->montantJournalier = $this->motardSelectionne?->moto?->montant_journalier_attendu ?? SystemSetting::getMontantJournalierDefaut();
            $this->arrieresCumules = $this->motardSelectionne?->getTotalArrieres() ?? 0;
            $this->tauxPaiement = $this->motardSelectionne?->taux_paiement ?? 100;

            // Recalculer les montants avec la semaine sélectionnée
            $this->calculerMontantsSelonSemaine();
        } else {
            $this->resetMotardData();
        }
        $this->updateRepartitionPreview();
    }

    public function updatedSemaineSelectionnee($value)
    {
        // Recalculer les montants selon la nouvelle semaine
        if ($this->motardSelectionne) {
            $this->calculerMontantsSelonSemaine();
            $this->updateRepartitionPreview();
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

        // Montant journalier
        $this->montantAttendu = $this->montantJournalier;

        // Montant hebdomadaire complet (6 jours)
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
        $this->tauxPaiement = 0;
        $this->partProprietairePreview = 0;
        $this->partOkamiPreview = 0;
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

    public function enregistrer()
    {
        $this->validate();

        $caissier = auth()->user()->caissier;
        $motard = Motard::with('moto')->find($this->motard_id);
        $moto = $motard->moto;

        // Récupérer les infos de la semaine sélectionnée
        $semaineData = $this->semaines[$this->semaine_selectionnee] ?? null;
        if (!$semaineData) {
            session()->flash('error', 'Semaine invalide.');
            return;
        }

        $semaineDebut = Carbon::parse($semaineData['debut']);
        $semaineFin = Carbon::parse($semaineData['fin']);
        $numeroSemaine = $semaineData['numero'];

        // Montant attendu hebdomadaire (6 jours)
        $montantJournalier = $moto?->montant_journalier_attendu ?? SystemSetting::getMontantJournalierDefaut();
        $montantHebdomadaireAttendu = $montantJournalier * RepartitionService::JOURS_SEMAINE;
        $montantVerse = (float) $this->montant;

        // Calculer la répartition
        $partProprietaire = RepartitionService::getPartProprietaire($montantVerse);
        $partOkami = RepartitionService::getPartOkami($montantVerse);

        // Récupérer les arriérés cumulés du motard
        $arrieresCumules = $motard->getTotalArrieres();

        // Déterminer le statut et les arriérés
        $arrieresDuJour = 0;
        $notesSupplementaires = '';

        if ($montantVerse >= $montantHebdomadaireAttendu) {
            $statut = 'payé';
            $arrieresDuJour = 0;

            $excedent = $montantVerse - $montantHebdomadaireAttendu;
            if ($excedent > 0 && $arrieresCumules > 0) {
                $remboursementArrieres = min($excedent, $arrieresCumules);
                $notesSupplementaires = "Excédent de " . number_format($excedent) . " FC utilisé pour rembourser " . number_format($remboursementArrieres) . " FC d'arriérés.";
                $this->rembourserArrieres($motard, $remboursementArrieres);
            }
        } elseif ($montantVerse > 0) {
            $statut = 'partiellement_payé';
            $arrieresDuJour = $montantHebdomadaireAttendu - $montantVerse;
            $notesSupplementaires = "Versement partiel. Arriéré: " . number_format($arrieresDuJour) . " FC";
        } else {
            $statut = 'non_effectué';
            $arrieresDuJour = $montantHebdomadaireAttendu;
        }

        // Créer le versement
        $versement = Versement::create([
            'motard_id' => $this->motard_id,
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

        // Mettre à jour le solde du caissier
        $caissier->increment('solde_actuel', $montantVerse);

        session()->flash('success', 'Versement de ' . number_format($montantVerse) . ' FC enregistré avec succès pour la semaine ' . $numeroSemaine . '.');
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

            // Recalculer la répartition
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
