<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\SystemNotification;

#[Layout('components.dashlite-layout')]
class NotificationsIndex extends Component
{
    use WithPagination;

    public $filterType = '';
    public $filterLu = '';
    public $filterPriorite = '';

    public function updatingFilterType() { $this->resetPage(); }
    public function updatingFilterLu() { $this->resetPage(); }
    public function updatingFilterPriorite() { $this->resetPage(); }

    /**
     * Marquer une notification comme lue
     */
    public function marquerCommeLu($notificationId)
    {
        $notification = SystemNotification::where('user_id', auth()->id())
            ->find($notificationId);

        if ($notification) {
            $notification->marquerCommeLu();
        }
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function marquerToutesCommeLues()
    {
        SystemNotification::where('user_id', auth()->id())
            ->where('lu', false)
            ->update([
                'lu' => true,
                'lu_at' => now(),
            ]);

        session()->flash('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    /**
     * Supprimer une notification
     */
    public function supprimer($notificationId)
    {
        SystemNotification::where('user_id', auth()->id())
            ->where('id', $notificationId)
            ->delete();

        session()->flash('success', 'Notification supprimée.');
    }

    /**
     * Supprimer toutes les notifications lues
     */
    public function supprimerLues()
    {
        $deleted = SystemNotification::where('user_id', auth()->id())
            ->where('lu', true)
            ->delete();

        session()->flash('success', "{$deleted} notification(s) supprimée(s).");
    }

    public function render()
    {
        $notifications = SystemNotification::where('user_id', auth()->id())
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterLu !== '', fn($q) => $q->where('lu', $this->filterLu === '1'))
            ->when($this->filterPriorite, fn($q) => $q->where('priorite', $this->filterPriorite))
            ->nonExpirees()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total' => SystemNotification::where('user_id', auth()->id())->nonExpirees()->count(),
            'nonLues' => SystemNotification::where('user_id', auth()->id())->nonLues()->nonExpirees()->count(),
            'urgentes' => SystemNotification::where('user_id', auth()->id())->urgentes()->nonLues()->nonExpirees()->count(),
        ];

        // Types de notifications pour le filtre
        $types = SystemNotification::where('user_id', auth()->id())
            ->distinct()
            ->pluck('type')
            ->filter()
            ->mapWithKeys(fn($type) => [$type => $this->getTypeLabel($type)]);

        return view('livewire.notifications-index', compact('notifications', 'stats', 'types'));
    }

    /**
     * Obtenir le label d'un type de notification
     */
    protected function getTypeLabel(string $type): string
    {
        $labels = [
            'retard_paiement' => 'Retard de paiement',
            'versement_valide' => 'Versement validé',
            'arrieres_critiques' => 'Arriérés critiques',
            'ramassage_prevu' => 'Ramassage prévu',
            'versement_moto' => 'Versement moto',
            'paiement_recu' => 'Paiement reçu',
            'paiement_rejete' => 'Paiement rejeté',
            'paiement_approuve' => 'Paiement approuvé',
            'accident_moto' => 'Accident moto',
            'accident_grave' => 'Accident grave',
            'accident_cloture' => 'Accident clôturé',
            'reparation_accident' => 'Réparation accident',
            'maintenance_moto' => 'Maintenance moto',
            'maintenance_programmee' => 'Maintenance programmée',
            'maintenance_rappel' => 'Rappel maintenance',
            'tournee_jour' => 'Tournée du jour',
            'tournee_confirmee' => 'Tournée confirmée',
            'modification_tournee' => 'Modification tournée',
            'collecte_validee' => 'Collecte validée',
            'collecte_rejetee' => 'Collecte rejetée',
            'fin_ramassage' => 'Fin de ramassage',
            'arrieres_motard' => 'Arriérés motard',
            'demande_paiement' => 'Demande de paiement',
            'contrat_expire' => 'Contrat expiré',
            'contrat_expire_bientot' => 'Contrat expire bientôt',
            'immobilisation_prolongee' => 'Immobilisation prolongée',
            'depassement_budget' => 'Dépassement budget',
        ];

        return $labels[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }
}

