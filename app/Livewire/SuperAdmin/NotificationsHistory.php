<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.dashlite')]
class NotificationsHistory extends Component
{
    use WithPagination;

    public $search = '';
    public $filterType = '';
    public $filterUser = '';
    public $filterPriorite = '';
    public $filterDateDebut = '';
    public $filterDateFin = '';
    public $perPage = 20;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterType() { $this->resetPage(); }
    public function updatingFilterUser() { $this->resetPage(); }
    public function updatingFilterPriorite() { $this->resetPage(); }

    /**
     * Obtenir les statistiques globales
     */
    public function getStatistiques()
    {
        $aujourdhui = now()->startOfDay();
        $semaineDebut = now()->startOfWeek();
        $moisDebut = now()->startOfMonth();

        return [
            'total' => SystemNotification::count(),
            'aujourdhui' => SystemNotification::where('created_at', '>=', $aujourdhui)->count(),
            'semaine' => SystemNotification::where('created_at', '>=', $semaineDebut)->count(),
            'mois' => SystemNotification::where('created_at', '>=', $moisDebut)->count(),
            'non_lues' => SystemNotification::where('lu', false)->count(),
            'urgentes' => SystemNotification::where('priorite', 'urgente')->where('lu', false)->count(),
        ];
    }

    /**
     * Obtenir les types de notifications disponibles
     */
    public function getTypes()
    {
        return SystemNotification::distinct()->pluck('type')->filter()->mapWithKeys(function ($type) {
            $labels = [
                'retard_paiement' => 'Retard de paiement',
                'versement_valide' => 'Versement validé',
                'arrieres_critiques' => 'Arriérés critiques',
                'arrieres_motard' => 'Arriérés motard',
                'ramassage_prevu' => 'Ramassage prévu',
                'versement_moto' => 'Versement moto',
                'paiement_recu' => 'Paiement reçu',
                'paiement_rejete' => 'Paiement rejeté',
                'paiement_approuve' => 'Paiement approuvé',
                'accident_moto' => 'Accident moto',
                'accident_grave' => 'Accident grave',
                'maintenance_moto' => 'Maintenance moto',
                'maintenance_programmee' => 'Maintenance programmée',
                'tournee_jour' => 'Tournée du jour',
                'tournee_confirmee' => 'Tournée confirmée',
                'modification_tournee' => 'Modification tournée',
                'collecte_validee' => 'Collecte validée',
                'collecte_rejetee' => 'Collecte rejetée',
                'fin_ramassage' => 'Fin de ramassage',
                'contrat_expire' => 'Contrat expiré',
                'contrat_expire_bientot' => 'Contrat expire bientôt',
                'immobilisation_prolongee' => 'Immobilisation prolongée',
                'depassement_budget' => 'Dépassement budget',
                'demande_paiement' => 'Demande de paiement',
            ];
            return [$type => $labels[$type] ?? ucfirst(str_replace('_', ' ', $type))];
        });
    }

    /**
     * Supprimer une notification
     */
    public function supprimer($id)
    {
        SystemNotification::find($id)?->delete();
        session()->flash('success', 'Notification supprimée');
    }

    /**
     * Supprimer les notifications anciennes (plus de 30 jours)
     */
    public function supprimerAnciennes()
    {
        $deleted = SystemNotification::where('created_at', '<', now()->subDays(30))
            ->where('lu', true)
            ->delete();

        session()->flash('success', "{$deleted} notification(s) ancienne(s) supprimée(s)");
    }

    /**
     * Exporter les notifications en CSV
     */
    public function exporterCsv()
    {
        $notifications = SystemNotification::with('user')
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterUser, fn($q) => $q->where('user_id', $this->filterUser))
            ->when($this->filterPriorite, fn($q) => $q->where('priorite', $this->filterPriorite))
            ->when($this->filterDateDebut, fn($q) => $q->whereDate('created_at', '>=', $this->filterDateDebut))
            ->when($this->filterDateFin, fn($q) => $q->whereDate('created_at', '<=', $this->filterDateFin))
            ->orderBy('created_at', 'desc')
            ->get();

        $csv = "ID,Type,Titre,Message,Utilisateur,Priorité,Lu,Date\n";
        foreach ($notifications as $n) {
            $csv .= sprintf(
                "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $n->id,
                $n->type,
                str_replace('"', '""', $n->titre),
                str_replace('"', '""', $n->message),
                $n->user->name ?? 'N/A',
                $n->priorite,
                $n->lu ? 'Oui' : 'Non',
                $n->created_at->format('d/m/Y H:i')
            );
        }

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'notifications_' . date('Y-m-d') . '.csv');
    }

    public function render()
    {
        $notifications = SystemNotification::with('user')
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('titre', 'like', "%{$this->search}%")
                       ->orWhere('message', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterUser, fn($q) => $q->where('user_id', $this->filterUser))
            ->when($this->filterPriorite, fn($q) => $q->where('priorite', $this->filterPriorite))
            ->when($this->filterDateDebut, fn($q) => $q->whereDate('created_at', '>=', $this->filterDateDebut))
            ->when($this->filterDateFin, fn($q) => $q->whereDate('created_at', '<=', $this->filterDateFin))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        $users = User::orderBy('name')->get(['id', 'name']);
        $types = $this->getTypes();
        $stats = $this->getStatistiques();

        return view('livewire.super-admin.notifications-history', compact('notifications', 'users', 'types', 'stats'));
    }
}

