<?php

namespace App\Livewire\Supervisor\Motos;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Moto;
use App\Models\Proprietaire;
use App\Models\Motard;
use Illuminate\Support\Facades\DB;

#[Layout('components.dashlite-layout')]
class Create extends Component
{
    public string $numero_matricule = '';
    public string $plaque_immatriculation = '';
    public string $numero_chassis = '';
    public string $marque = '';
    public string $modele = '';
    public string $annee = '';
    public string $couleur = '';
    public string $proprietaire_id = '';
    public string $motard_id = '';
    public string $statut = 'actif';
    public $montant_journalier_attendu = '';
    public string $contrat_debut = '';
    public string $contrat_fin = '';
    public string $contrat_numero = '';

    public function mount()
    {
        // Générer automatiquement le numéro matricule
        $this->numero_matricule = $this->generateNumeroMatricule();
    }

    /**
     * Génère un numéro matricule unique
     * Format: TC-YY-XXXX (TC = Tricycle, YY = Année, XXXX = Numéro séquentiel)
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

    protected function rules(): array
    {
        return [
            'numero_matricule' => 'required|string|unique:motos,numero_matricule',
            'plaque_immatriculation' => 'required|string|unique:motos,plaque_immatriculation',
            'numero_chassis' => 'nullable|string|unique:motos,numero_chassis',
            'marque' => 'nullable|string|max:100',
            'modele' => 'nullable|string|max:100',
            'annee' => 'nullable|integer|min:1990|max:' . (date('Y') + 1),
            'couleur' => 'nullable|string|max:50',
            'proprietaire_id' => 'required|exists:proprietaires,id',
            'motard_id' => 'nullable|exists:motards,id',
            'statut' => 'required|in:actif,suspendu,maintenance',
            'montant_journalier_attendu' => 'required|numeric|min:0',
            'contrat_debut' => 'nullable|date',
            'contrat_fin' => 'nullable|date|after_or_equal:contrat_debut',
            'contrat_numero' => 'nullable|string|max:50',
        ];
    }

    protected $messages = [
        'numero_matricule.required' => 'Le numéro matricule est obligatoire.',
        'numero_matricule.unique' => 'Ce numéro matricule existe déjà.',
        'plaque_immatriculation.required' => 'La plaque d\'immatriculation est obligatoire.',
        'plaque_immatriculation.unique' => 'Cette plaque existe déjà.',
        'numero_chassis.unique' => 'Ce numéro de châssis existe déjà.',
        'proprietaire_id.required' => 'Le propriétaire est obligatoire.',
        'montant_journalier_attendu.required' => 'Le montant journalier est obligatoire.',
        'contrat_fin.after_or_equal' => 'La date de fin doit être après la date de début.',
    ];

    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $moto = Moto::create([
                'numero_matricule' => $this->numero_matricule,
                'plaque_immatriculation' => $this->plaque_immatriculation,
                'numero_chassis' => $this->numero_chassis ?: null,
                'marque' => $this->marque ?: null,
                'modele' => $this->modele ?: null,
                'annee_fabrication' => $this->annee ?: null,
                'couleur' => $this->couleur ?: null,
                'proprietaire_id' => $this->proprietaire_id,
                'motard_id' => $this->motard_id ?: null,
                'statut' => $this->statut,
                'montant_journalier_attendu' => $this->montant_journalier_attendu,
                'contrat_debut' => $this->contrat_debut ?: null,
                'contrat_fin' => $this->contrat_fin ?: null,
                'contrat_numero' => $this->contrat_numero ?: null,
            ]);

            DB::commit();

            session()->flash('success', 'Moto créée avec succès.');
            return redirect()->route('supervisor.motos.index');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $proprietaires = Proprietaire::with('user')->get();
        $motards = Motard::with('user')
            ->where('is_active', true)
            ->get();

        return view('livewire.supervisor.motos.create', compact('proprietaires', 'motards'));
    }
}

