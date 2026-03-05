<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Dashboard extends Component
{
    public $systemInfo = [];
    public $databaseStats = [];
    public $storageStats = [];

    public function mount()
    {
        $this->loadSystemInfo();
        $this->loadDatabaseStats();
        $this->loadStorageStats();
    }

    public function loadSystemInfo()
    {
        $this->systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'timezone' => config('app.timezone'),
            'environment' => config('app.env'),
            'debug_mode' => config('app.debug') ? 'Activé' : 'Désactivé',
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
        ];
    }

    public function loadDatabaseStats()
    {
        $tables = [
            'users' => 'Utilisateurs',
            'motos' => 'Motos',
            'motards' => 'Motards',
            'proprietaires' => 'Propriétaires',
            'caissiers' => 'Caissiers',
            'collecteurs' => 'Collecteurs',
            'cleaners' => 'Laveurs',
            'versements' => 'Versements',
            'payments' => 'Paiements',
            'tournees' => 'Tournées',
            'collectes' => 'Collectes',
            'lavages' => 'Lavages',
            'maintenances' => 'Maintenances',
            'accidents' => 'Accidents',
        ];

        $this->databaseStats = [];
        foreach ($tables as $table => $label) {
            if (Schema::hasTable($table)) {
                $this->databaseStats[$table] = [
                    'label' => $label,
                    'count' => DB::table($table)->count(),
                ];
            }
        }
    }

    public function loadStorageStats()
    {
        $storagePath = storage_path('app');
        $backupPath = storage_path('app/backups');

        $this->storageStats = [
            'storage_used' => $this->formatBytes($this->getDirectorySize($storagePath)),
            'backups_count' => is_dir($backupPath) ? count(glob($backupPath . '/*.sql')) : 0,
        ];
    }

    private function getDirectorySize($path)
    {
        $size = 0;
        if (is_dir($path)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        }
        return $size;
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

    /**
     * Vider les caches du système
     */
    public function clearAllCaches()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            Artisan::call('config:clear');

            session()->flash('success', 'Tous les caches ont été vidés avec succès!');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Optimiser l'application
     */
    public function optimizeApp()
    {
        try {
            Artisan::call('optimize');

            session()->flash('success', 'Application optimisée avec succès!');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $totalUsers = User::count();
        $usersParRole = User::with('roles')
            ->get()
            ->groupBy(fn($u) => $u->roles->first()?->name ?? 'Sans rôle')
            ->map->count();

        return view('livewire.super-admin.dashboard', [
            'totalUsers' => $totalUsers,
            'usersParRole' => $usersParRole,
        ]);
    }
}

