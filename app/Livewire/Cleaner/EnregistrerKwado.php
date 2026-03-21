<?php

namespace App\Livewire\Cleaner;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\KwadoService;
use App\Models\Moto;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class EnregistrerKwado extends Component
{
    // Type de véhicule
    public $is_externe = false;

    // Moto interne
    public $moto_id = '';
    public $motoSelectionnee = null;

    // Moto externe
    public $plaque_externe = '';
    public $proprietaire_externe = '';
    public $telephone_externe = '';

    // Détails du service
    public $type_service = 'crevaison';
    public $description_service = '';
    public $position_pneu = '';
    public $prix = 0;
    public $cout_pieces = 0;
    public $montant_encaisse = 0;
    public $mode_paiement = 'cash';
    public $notes = '';

    // Pour le reçu
    public $dernierServiceId = null;

    protected function rules()
    {
        $rules = [
            'type_service' => 'required|in:crevaison,changement_pneu,changement_chambre,equilibrage,gonflage,rustine,autre',
            'description_service' => 'nullable|string|max:500',
            'position_pneu' => 'nullable|in:avant,arriere_gauche,arriere_droit',
            'prix' => 'required|numeric|min:0',
            'cout_pieces' => 'nullable|numeric|min:0',
            'montant_encaisse' => 'required|numeric|min:0',
            'mode_paiement' => 'required|in:cash,mobile_money',
            'notes' => 'nullable|string|max:500',
        ];

        if ($this->is_externe) {
            $rules['plaque_externe'] = 'required|string|max:20';
            $rules['proprietaire_externe'] = 'nullable|string|max:100';
            $rules['telephone_externe'] = 'nullable|string|max:20';
        } else {
            $rules['moto_id'] = 'required|exists:motos,id';
        }

        return $rules;
    }

    protected $messages = [
        'moto_id.required' => 'Veuillez sélectionner une moto.',
        'plaque_externe.required' => 'Veuillez saisir la plaque du véhicule.',
        'type_service.required' => 'Veuillez sélectionner un type de service.',
        'prix.required' => 'Le prix du service est obligatoire.',
        'montant_encaisse.required' => 'Le montant encaissé est obligatoire.',
    ];

    public function mount()
    {
        $this->prix = 2000;
        $this->montant_encaisse = 2000;
    }

    public function updatedIsExterne($value)
    {
        if ($value) {
            $this->moto_id = '';
            $this->motoSelectionnee = null;
        } else {
            $this->plaque_externe = '';
            $this->proprietaire_externe = '';
            $this->telephone_externe = '';
        }
    }

    public function updatedMotoId($value)
    {
        if ($value) {
            $this->motoSelectionnee = Moto::with('proprietaire.user')->find($value);
        } else {
            $this->motoSelectionnee = null;
        }
    }

    public function updatedPrix($value)
    {
        $this->montant_encaisse = max(0, (float)$value);
    }

    public function updatedCoutPieces()
    {
        // Recalculer si besoin
    }

    public function enregistrer()
    {
        $this->validate();

        $cleaner = auth()->user()->cleaner;

        if (!$cleaner) {
            session()->flash('error', 'Profil laveur non trouvé.');
            return;
        }

        $service = KwadoService::create([
            'cleaner_id' => $cleaner->id,
            'moto_id' => $this->is_externe ? null : $this->moto_id,
            'is_externe' => $this->is_externe,
            'plaque_externe' => $this->is_externe ? $this->plaque_externe : null,
            'proprietaire_externe' => $this->is_externe ? $this->proprietaire_externe : null,
            'telephone_externe' => $this->is_externe ? $this->telephone_externe : null,
            'type_service' => $this->type_service,
            'description_service' => $this->description_service ?: null,
            'position_pneu' => $this->position_pneu ?: null,
            'prix' => $this->prix,
            'cout_pieces' => $this->cout_pieces ?? 0,
            'montant_encaisse' => $this->montant_encaisse,
            'mode_paiement' => $this->mode_paiement,
            'statut_paiement' => 'payé',
            'date_service' => now(),
            'notes' => $this->notes,
        ]);

        $this->dernierServiceId = $service->id;

        session()->flash('success', 'Service KWADO enregistré! N° ' . $service->numero_service . ' - ' . number_format($service->montant_encaisse) . ' FC encaissé.');
        session()->flash('dernierServiceId', $service->id);

        return redirect()->route('cleaner.kwado.index');
    }

    public function telechargerRecu($serviceId)
    {
        $service = KwadoService::with(['cleaner.user', 'moto.proprietaire.user'])->findOrFail($serviceId);

        $pdf = Pdf::loadView('pdf.recu-kwado', compact('service'));
        $pdf->setPaper([0, 0, 204, 400], 'portrait');

        $filename = 'recu_kwado_' . $service->numero_service . '.pdf';

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    public function render()
    {
        $motos = Moto::where('statut', 'actif')
            ->with('proprietaire.user')
            ->orderBy('plaque_immatriculation')
            ->get();

        return view('livewire.cleaner.enregistrer-kwado', [
            'motos' => $motos,
            'typesService' => KwadoService::TYPES_SERVICE,
            'positionsPneu' => KwadoService::POSITIONS_PNEU,
        ]);
    }
}

