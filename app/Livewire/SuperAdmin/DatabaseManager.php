<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class DatabaseManager extends Component
{
    public $tables = [];
    public $selectedTables = [];
    public $backups = [];
    public $confirmDelete = false;
    public $deleteConfirmText = '';

    // Pour le backup
    public $backupInProgress = false;
    public $lastBackup = null;

    protected $listeners = ['refreshBackups' => 'loadBackups'];

    public function mount()
    {
        $this->loadTables();
        $this->loadBackups();
    }

    public function loadTables()
    {
        $tablesToManage = [
            'versements' => ['label' => 'Versements', 'icon' => 'bi-cash-stack', 'color' => 'success'],
            'collectes' => ['label' => 'Collectes', 'icon' => 'bi-collection', 'color' => 'info'],
            'payments' => ['label' => 'Paiements', 'icon' => 'bi-credit-card', 'color' => 'primary'],
            'lavages' => ['label' => 'Lavages', 'icon' => 'bi-droplet', 'color' => 'info'],
            'depense_lavages' => ['label' => 'Dépenses Lavage', 'icon' => 'bi-wallet2', 'color' => 'danger'],
            'maintenances' => ['label' => 'Maintenances', 'icon' => 'bi-tools', 'color' => 'warning'],
            'accidents' => ['label' => 'Accidents', 'icon' => 'bi-exclamation-triangle', 'color' => 'danger'],
            'tournees' => ['label' => 'Tournées', 'icon' => 'bi-calendar-event', 'color' => 'secondary'],
            'system_notifications' => ['label' => 'Notifications', 'icon' => 'bi-bell', 'color' => 'secondary'],
            'motards' => ['label' => 'Motards', 'icon' => 'bi-person', 'color' => 'primary'],
            'motos' => ['label' => 'Motos', 'icon' => 'bi-bicycle', 'color' => 'dark'],
            'caissiers' => ['label' => 'Caissiers', 'icon' => 'bi-person-badge', 'color' => 'success'],
            'collecteurs' => ['label' => 'Collecteurs', 'icon' => 'bi-person-check', 'color' => 'info'],
            'cleaners' => ['label' => 'Laveurs', 'icon' => 'bi-person-gear', 'color' => 'info'],
            'proprietaires' => ['label' => 'Propriétaires', 'icon' => 'bi-people', 'color' => 'warning'],
            'zones' => ['label' => 'Zones', 'icon' => 'bi-geo-alt', 'color' => 'secondary'],
            'system_settings' => ['label' => 'Paramètres Système', 'icon' => 'bi-gear', 'color' => 'dark'],
        ];

        $this->tables = [];
        foreach ($tablesToManage as $table => $info) {
            if (Schema::hasTable($table)) {
                $this->tables[$table] = array_merge($info, [
                    'count' => DB::table($table)->count(),
                ]);
            }
        }
    }

    public function loadBackups()
    {
        $backupPath = storage_path('app/backups');

        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $files = glob($backupPath . '/*.sql');
        $this->backups = [];

        foreach ($files as $file) {
            $this->backups[] = [
                'name' => basename($file),
                'path' => $file,
                'size' => $this->formatBytes(filesize($file)),
                'date' => Carbon::createFromTimestamp(filemtime($file))->format('d/m/Y H:i'),
                'timestamp' => filemtime($file),
            ];
        }

        // Trier par date décroissante
        usort($this->backups, fn($a, $b) => $b['timestamp'] - $a['timestamp']);

        $this->lastBackup = count($this->backups) > 0 ? $this->backups[0] : null;
    }

    /**
     * Créer un backup de la base de données
     */
    public function createBackup()
    {
        try {
            $this->backupInProgress = true;

            $backupPath = storage_path('app/backups');
            if (!is_dir($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            $filename = 'backup_' . date('Y-m-d_His') . '.sql';
            $filepath = $backupPath . '/' . $filename;

            // Obtenir les informations de connexion
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', 3306);
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');

            // Commande mysqldump
            $command = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s 2>&1',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($filepath)
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($filepath) && filesize($filepath) > 0) {
                $this->loadBackups();
                session()->flash('success', 'Backup créé avec succès: ' . $filename);
            } else {
                session()->flash('error', 'Erreur lors de la création du backup. Vérifiez que mysqldump est installé.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        } finally {
            $this->backupInProgress = false;
        }
    }

    /**
     * Télécharger un backup
     */
    public function downloadBackup($filename)
    {
        $filepath = storage_path('app/backups/' . $filename);

        if (file_exists($filepath)) {
            return response()->download($filepath);
        }

        session()->flash('error', 'Fichier non trouvé.');
    }

    /**
     * Supprimer un backup
     */
    public function deleteBackup($filename)
    {
        $filepath = storage_path('app/backups/' . $filename);

        if (file_exists($filepath)) {
            unlink($filepath);
            $this->loadBackups();
            session()->flash('success', 'Backup supprimé.');
        } else {
            session()->flash('error', 'Fichier non trouvé.');
        }
    }

    /**
     * Vider les tables sélectionnées
     */
    public function clearSelectedTables()
    {
        if (empty($this->selectedTables)) {
            session()->flash('error', 'Veuillez sélectionner au moins une table.');
            return;
        }

        if ($this->deleteConfirmText !== 'SUPPRIMER') {
            session()->flash('error', 'Veuillez taper SUPPRIMER pour confirmer.');
            return;
        }

        try {
            Schema::disableForeignKeyConstraints();

            $cleared = 0;
            foreach ($this->selectedTables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                    $cleared++;
                }
            }

            Schema::enableForeignKeyConstraints();

            $this->loadTables();
            $this->selectedTables = [];
            $this->deleteConfirmText = '';
            $this->confirmDelete = false;

            session()->flash('success', $cleared . ' table(s) vidée(s) avec succès!');
        } catch (\Exception $e) {
            Schema::enableForeignKeyConstraints();
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Vider TOUTES les données sauf les utilisateurs
     */
    public function clearAllData()
    {
        if ($this->deleteConfirmText !== 'SUPPRIMER TOUT') {
            session()->flash('error', 'Veuillez taper SUPPRIMER TOUT pour confirmer.');
            return;
        }

        try {
            Artisan::call('data:clear', ['--force' => true]);

            $this->loadTables();
            $this->deleteConfirmText = '';
            $this->confirmDelete = false;

            session()->flash('success', 'Toutes les données ont été supprimées (utilisateurs conservés).');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Sélectionner/Désélectionner toutes les tables
     */
    public function toggleSelectAll()
    {
        if (count($this->selectedTables) === count($this->tables)) {
            $this->selectedTables = [];
        } else {
            $this->selectedTables = array_keys($this->tables);
        }
    }

    private function formatBytes($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    public function render()
    {
        return view('livewire.super-admin.database-manager');
    }
}

