<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Rôles selon le cahier des charges:
     * - admin (New Technology Hub Sarl) : Gestion complète du système
     * - supervisor (OKAMI) : Supervision opérationnelle
     * - owner (Propriétaire/Bailleur) : Consultation de ses revenus
     * - driver (Motard) : Consultation de son statut uniquement
     * - cashier (Caissier) : Point de collecte terrain
     * - collector (Collecteur) : Ramassage chez les caissiers
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ========================================
        // CRÉATION DES PERMISSIONS
        // ========================================

        // --- Permissions Dashboard ---
        Permission::create(['name' => 'view_dashboard']);
        Permission::create(['name' => 'view_admin_dashboard']);
        Permission::create(['name' => 'view_okami_dashboard']);
        Permission::create(['name' => 'view_owner_dashboard']);
        Permission::create(['name' => 'view_driver_dashboard']);
        Permission::create(['name' => 'view_cashier_dashboard']);
        Permission::create(['name' => 'view_collector_dashboard']);

        // --- Permissions Motards ---
        Permission::create(['name' => 'view_motards']);
        Permission::create(['name' => 'create_motards']);
        Permission::create(['name' => 'edit_motards']);
        Permission::create(['name' => 'delete_motards']);
        Permission::create(['name' => 'activate_motards']);
        Permission::create(['name' => 'suspend_motards']);
        Permission::create(['name' => 'transfer_motards']);

        // --- Permissions Motos ---
        Permission::create(['name' => 'view_motos']);
        Permission::create(['name' => 'create_motos']);
        Permission::create(['name' => 'edit_motos']);
        Permission::create(['name' => 'delete_motos']);
        Permission::create(['name' => 'assign_moto_to_motard']);

        // --- Permissions Propriétaires ---
        Permission::create(['name' => 'view_proprietaires']);
        Permission::create(['name' => 'create_proprietaires']);
        Permission::create(['name' => 'edit_proprietaires']);
        Permission::create(['name' => 'delete_proprietaires']);

        // --- Permissions Versements ---
        Permission::create(['name' => 'view_versements']);
        Permission::create(['name' => 'view_own_versements']); // Pour motard
        Permission::create(['name' => 'create_versements']);
        Permission::create(['name' => 'validate_versements']); // Pour caissier
        Permission::create(['name' => 'validate_versements_okami']); // Validation OKAMI (cas litigieux)

        // --- Permissions Paiements Propriétaires ---
        Permission::create(['name' => 'view_payments']);
        Permission::create(['name' => 'view_own_payments']); // Pour propriétaire
        Permission::create(['name' => 'create_payments']);
        Permission::create(['name' => 'process_payments']);
        Permission::create(['name' => 'generate_payment_receipts']);

        // --- Permissions Collectes ---
        Permission::create(['name' => 'view_collectes']);
        Permission::create(['name' => 'create_collectes']);
        Permission::create(['name' => 'validate_collectes']);

        // --- Permissions Tournées ---
        Permission::create(['name' => 'view_tournees']);
        Permission::create(['name' => 'view_own_tournees']); // Pour collecteur
        Permission::create(['name' => 'create_tournees']);
        Permission::create(['name' => 'edit_tournees']);
        Permission::create(['name' => 'validate_tournees']); // NTH valide réception

        // --- Permissions Caissiers ---
        Permission::create(['name' => 'view_caissiers']);
        Permission::create(['name' => 'create_caissiers']);
        Permission::create(['name' => 'edit_caissiers']);
        Permission::create(['name' => 'delete_caissiers']);

        // --- Permissions Collecteurs ---
        Permission::create(['name' => 'view_collecteurs']);
        Permission::create(['name' => 'create_collecteurs']);
        Permission::create(['name' => 'edit_collecteurs']);
        Permission::create(['name' => 'delete_collecteurs']);

        // --- Permissions Maintenances ---
        Permission::create(['name' => 'view_maintenances']);
        Permission::create(['name' => 'view_own_maintenances']); // Pour propriétaire
        Permission::create(['name' => 'create_maintenances']);
        Permission::create(['name' => 'edit_maintenances']);
        Permission::create(['name' => 'delete_maintenances']);
        Permission::create(['name' => 'validate_maintenances']); // NTH seul

        // --- Permissions Accidents ---
        Permission::create(['name' => 'view_accidents']);
        Permission::create(['name' => 'view_own_accidents']); // Pour propriétaire
        Permission::create(['name' => 'declare_accidents']); // Motard peut déclarer
        Permission::create(['name' => 'edit_accidents']);
        Permission::create(['name' => 'delete_accidents']);
        Permission::create(['name' => 'validate_accidents']); // NTH seul

        // --- Permissions Rapports ---
        Permission::create(['name' => 'view_reports']);
        Permission::create(['name' => 'view_daily_reports']);
        Permission::create(['name' => 'view_weekly_reports']);
        Permission::create(['name' => 'view_monthly_reports']);
        Permission::create(['name' => 'export_reports']);

        // --- Permissions Utilisateurs ---
        Permission::create(['name' => 'view_users']);
        Permission::create(['name' => 'create_users']);
        Permission::create(['name' => 'edit_users']);
        Permission::create(['name' => 'delete_users']);
        Permission::create(['name' => 'manage_permissions']);

        // --- Permissions Paramètres ---
        Permission::create(['name' => 'manage_settings']);
        Permission::create(['name' => 'manage_zones']);
        Permission::create(['name' => 'manage_tarifs']);
        Permission::create(['name' => 'manage_penalites']);

        // --- Permissions Notifications ---
        Permission::create(['name' => 'send_notifications']);
        Permission::create(['name' => 'receive_notifications']);

        // --- Permissions Messagerie ---
        Permission::create(['name' => 'send_messages']);
        Permission::create(['name' => 'receive_messages']);
        Permission::create(['name' => 'send_preformatted_messages']); // Motard uniquement

        // ========================================
        // CRÉATION DES RÔLES
        // ========================================

        // 1. ADMIN (New Technology Hub Sarl) - Tous les droits
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // 2. SUPERVISOR (OKAMI) - Supervision opérationnelle
        $supervisorRole = Role::create(['name' => 'supervisor']);
        $supervisorRole->givePermissionTo([
            'view_dashboard',
            'view_okami_dashboard',
            // Consultation motards
            'view_motards',
            'view_motos',
            // Versements - consultation et validation cas douteux
            'view_versements',
            'validate_versements_okami',
            // Rapports
            'view_reports',
            'view_daily_reports',
            'view_weekly_reports',
            'view_monthly_reports',
            'export_reports',
            // Consultation maintenances et accidents (lecture seule)
            'view_maintenances',
            'view_accidents',
            // Collectes et tournées (lecture seule)
            'view_collectes',
            'view_tournees',
            // Notifications
            'receive_notifications',
        ]);

        // 3. OWNER (Propriétaire/Bailleur)
        $ownerRole = Role::create(['name' => 'owner']);
        $ownerRole->givePermissionTo([
            'view_dashboard',
            'view_owner_dashboard',
            // Consultation de SES versements et paiements
            'view_own_versements',
            'view_own_payments',
            // Consultation de SES maintenances et accidents
            'view_own_maintenances',
            'view_own_accidents',
            // Rapports (ses propres données)
            'view_reports',
            'view_monthly_reports',
            // Messagerie pour réclamations
            'send_messages',
            'receive_messages',
            'receive_notifications',
        ]);

        // 4. DRIVER (Motard) - Accès très limité
        $driverRole = Role::create(['name' => 'driver']);
        $driverRole->givePermissionTo([
            'view_dashboard',
            'view_driver_dashboard',
            // Consultation de SON historique uniquement
            'view_own_versements',
            // Déclaration d'accident (ne peut pas modifier)
            'declare_accidents',
            // Messages préformatés uniquement
            'send_preformatted_messages',
            'receive_notifications',
        ]);

        // 5. CASHIER (Caissier) - Point de collecte terrain
        $cashierRole = Role::create(['name' => 'cashier']);
        $cashierRole->givePermissionTo([
            'view_dashboard',
            'view_cashier_dashboard',
            // Réception et validation des versements des motards
            'view_versements',
            'create_versements',
            'validate_versements',
            // Consultation maintenances et accidents (lecture seule)
            'view_maintenances',
            'view_accidents',
            // Notifications
            'receive_notifications',
        ]);

        // 6. COLLECTOR (Collecteur) - Ramassage terrain
        $collectorRole = Role::create(['name' => 'collector']);
        $collectorRole->givePermissionTo([
            'view_dashboard',
            'view_collector_dashboard',
            // Ses propres tournées
            'view_own_tournees',
            // Création des collectes lors du ramassage
            'view_collectes',
            'create_collectes',
            // Notifications
            'receive_notifications',
        ]);
    }
}
