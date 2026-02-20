<?php

namespace App\Livewire\Admin\Motos;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Moto;
use App\Models\Proprietaire;
use App\Models\Motard;

#[Layout('components.dashlite-layout')]
class Create extends Component
{
    public $numero_matricule = '';
    public $plaque_immatriculation = '';
    public $numero_chassis = '';
    public $proprietaire_id = '';
    public $motard_id = '';
    public $montant_journalier_attendu = '';
    public $statut = 'actif';
    public $contrat_debut = '';
    public $contrat_fin = '';
    public $contrat_numero = '';

    public function mount()
    {
        // Générer automatiquement le numéro matricule
        $this->numero_matricule = $this->generateNumeroMatricule();
    }

    /**
     * Génère un numéro matricule unique
     * Format: TC-PR-XXXX (TC = Tricycle, PR = Propriétaire, XXXX = Numéro séquentiel)
     */
    protected function generateNumeroMatricule(): string
    {
        $prefix = 'TC';
        $year = date('y'); // Année sur 2 chiffres

        // Trouver le dernier numéro utilisé cette année
        $lastMoto = Moto::withTrashed()
            ->where('numero_matricule', 'like', $prefix . '-' . $year . '-%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(numero_matricule, "-", -1) AS UNSIGNED) DESC')
            ->first();

        if ($lastMoto) {
            // Extraire le numéro séquentiel
            $parts = explode('-', $lastMoto->numero_matricule);
            $lastNumber = (int) end($parts);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        // Format: TC-26-0001
        return sprintf('%s-%s-%04d', $prefix, $year, $newNumber);
    }

    /**
     * Régénérer le numéro matricule
     */
    public function regenerateNumero()
    {
        $this->numero_matricule = $this->generateNumeroMatricule();
    }

    protected function rules()
    {
        return [
            'numero_matricule' => 'required|string|unique:motos,numero_matricule',
            'plaque_immatriculation' => 'required|string|unique:motos,plaque_immatriculation',
            'numero_chassis' => 'nullable|string|unique:motos,numero_chassis',
            'proprietaire_id' => 'required|exists:proprietaires,id',
            'motard_id' => 'nullable|exists:motards,id',
            'montant_journalier_attendu' => 'required|numeric|min:0',
            'statut' => 'required|in:actif,suspendu,maintenance',
            'contrat_debut' => 'nullable|date',
            'contrat_fin' => 'nullable|date|after_or_equal:contrat_debut',
            'contrat_numero' => 'nullable|string|max:50',
        ];
    }

    protected $messages = [
        'numero_matricule.required' => 'Le numéro matricule est obligatoire.',
        'numero_matricule.unique' => 'Ce numéro matricule existe déjà.',
        'plaque_immatriculation.required' => 'La plaque d\'immatriculation est obligatoire.',
        'plaque_immatriculation.unique' => 'Cette plaque d\'immatriculation existe déjà.',
        'numero_chassis.unique' => 'Ce numéro de chassis existe déjà.',
        'proprietaire_id.required' => 'Veuillez sélectionner un propriétaire.',
        'montant_journalier_attendu.required' => 'Le montant journalier est obligatoire.',
        'montant_journalier_attendu.numeric' => 'Le montant doit être un nombre.',
        'contrat_fin.after_or_equal' => 'La date de fin doit être après la date de début.',
    ];

    public function save()
    {
        $this->validate();

        try {
            Moto::create([
                'numero_matricule' => $this->numero_matricule,
                'plaque_immatriculation' => $this->plaque_immatriculation,
                'numero_chassis' => $this->numero_chassis ?: null,
                'proprietaire_id' => $this->proprietaire_id,
                'motard_id' => $this->motard_id ?: null,
                'montant_journalier_attendu' => $this->montant_journalier_attendu,
                'statut' => $this->statut,
                'contrat_debut' => $this->contrat_debut ?: null,
                'contrat_fin' => $this->contrat_fin ?: null,
                'contrat_numero' => $this->contrat_numero ?: null,
            ]);

            session()->flash('success', 'Moto ajoutée avec succès.');
            return redirect()->route('admin.motos.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de l\'enregistrement: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $proprietaires = Proprietaire::with('user')->get();
        $motards = Motard::with('user')->where('is_active', true)->get();

        return view('livewire.admin.motos.create', compact('proprietaires', 'motards'));
    }
}
