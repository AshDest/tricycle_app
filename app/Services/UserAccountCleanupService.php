<?php

namespace App\Services;

use App\Models\Caissier;
use App\Models\Cleaner;
use App\Models\Collecteur;
use App\Models\Motard;
use App\Models\Proprietaire;
use App\Models\User;

class UserAccountCleanupService
{
    /**
     * Nettoie le compte utilisateur après suppression d'un profil métier.
     * Si aucun autre profil actif n'existe, on tente de supprimer le compte utilisateur.
     * En cas d'échec (contraintes FK), le compte est désactivé et ses rôles retirés.
     */
    public function cleanupAfterProfileDeletion(?User $user, ?string $roleToRemove = null): array
    {
        if (!$user) {
            return [
                'status' => 'no_user',
                'message' => 'Aucun compte utilisateur associé.',
            ];
        }

        if ($roleToRemove && $user->hasRole($roleToRemove)) {
            $user->removeRole($roleToRemove);
        }

        if ($this->hasOtherActiveProfiles($user->id)) {
            return [
                'status' => 'kept',
                'message' => 'Compte utilisateur conservé (autres profils actifs détectés).',
            ];
        }

        try {
            $user->syncRoles([]);
            $user->delete();

            return [
                'status' => 'deleted',
                'message' => 'Compte utilisateur supprimé.',
            ];
        } catch (\Throwable $e) {
            $user->syncRoles([]);
            $user->update(['is_active' => false]);

            return [
                'status' => 'disabled',
                'message' => 'Compte utilisateur désactivé (suppression impossible à cause des dépendances).',
            ];
        }
    }

    protected function hasOtherActiveProfiles(int $userId): bool
    {
        return Motard::where('user_id', $userId)->exists()
            || Caissier::where('user_id', $userId)->exists()
            || Collecteur::where('user_id', $userId)->exists()
            || Proprietaire::where('user_id', $userId)->exists()
            || Cleaner::where('user_id', $userId)->exists();
    }
}

