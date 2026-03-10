<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

#[Layout('components.layouts.dashlite')]
class ServerProcesses extends Component
{
    public $supervisorStatus = [];
    public $queueStats = [];
    public $cronStatus = '';
    public $lastDeployment = '';
    public $phpVersion = '';
    public $laravelVersion = '';
    public $serverInfo = [];

    public $isRefreshing = false;

    public function mount()
    {
        $this->loadAllStatus();
    }

    public function loadAllStatus()
    {
        $this->isRefreshing = true;

        $this->loadSupervisorStatus();
        $this->loadQueueStats();
        $this->loadCronStatus();
        $this->loadServerInfo();

        $this->isRefreshing = false;
    }

    /**
     * Charger le statut de Supervisor
     */
    protected function loadSupervisorStatus()
    {
        try {
            $output = shell_exec('supervisorctl status 2>&1');

            if ($output && !str_contains($output, 'command not found')) {
                $lines = array_filter(explode("\n", trim($output)));
                $this->supervisorStatus = [];

                foreach ($lines as $line) {
                    if (preg_match('/^(\S+)\s+(\S+)\s+(.*)$/', $line, $matches)) {
                        $this->supervisorStatus[] = [
                            'name' => $matches[1],
                            'status' => $matches[2],
                            'info' => $matches[3] ?? '',
                            'isRunning' => $matches[2] === 'RUNNING',
                        ];
                    }
                }
            } else {
                $this->supervisorStatus = [
                    ['name' => 'supervisor', 'status' => 'NOT_INSTALLED', 'info' => 'Supervisor non installé', 'isRunning' => false]
                ];
            }
        } catch (\Exception $e) {
            $this->supervisorStatus = [
                ['name' => 'error', 'status' => 'ERROR', 'info' => $e->getMessage(), 'isRunning' => false]
            ];
        }
    }

    /**
     * Charger les statistiques de la queue
     */
    protected function loadQueueStats()
    {
        try {
            $this->queueStats = [
                'jobs_pending' => DB::table('jobs')->count(),
                'jobs_failed' => DB::table('failed_jobs')->count(),
                'jobs_today' => DB::table('jobs')
                    ->whereDate('created_at', today())
                    ->count(),
                'oldest_job' => DB::table('jobs')
                    ->orderBy('created_at', 'asc')
                    ->value('created_at'),
                'last_failed' => DB::table('failed_jobs')
                    ->orderBy('failed_at', 'desc')
                    ->value('failed_at'),
            ];
        } catch (\Exception $e) {
            $this->queueStats = [
                'jobs_pending' => 0,
                'jobs_failed' => 0,
                'jobs_today' => 0,
                'oldest_job' => null,
                'last_failed' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Vérifier le statut du cron
     */
    protected function loadCronStatus()
    {
        try {
            $output = shell_exec('crontab -l 2>&1');

            if ($output && str_contains($output, 'schedule:run')) {
                $this->cronStatus = 'configured';
            } elseif ($output && str_contains($output, 'no crontab')) {
                $this->cronStatus = 'not_configured';
            } else {
                $this->cronStatus = 'unknown';
            }
        } catch (\Exception $e) {
            $this->cronStatus = 'error';
        }
    }

    /**
     * Charger les informations du serveur
     */
    protected function loadServerInfo()
    {
        $this->phpVersion = PHP_VERSION;
        $this->laravelVersion = app()->version();

        $this->serverInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug') ? 'Activé' : 'Désactivé',
            'timezone' => config('app.timezone'),
            'queue_driver' => config('queue.default'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'mail_driver' => config('mail.default'),
            'disk_free' => $this->getDiskSpace(),
            'memory_usage' => $this->getMemoryUsage(),
        ];

        // Dernière modification du fichier de déploiement
        $deployFile = base_path('.git/FETCH_HEAD');
        if (file_exists($deployFile)) {
            $this->lastDeployment = date('d/m/Y H:i:s', filemtime($deployFile));
        } else {
            $this->lastDeployment = 'Inconnu';
        }
    }

    /**
     * Obtenir l'espace disque disponible
     */
    protected function getDiskSpace(): string
    {
        try {
            $free = disk_free_space(base_path());
            $total = disk_total_space(base_path());
            $used = $total - $free;
            $percent = round(($used / $total) * 100, 1);

            return $this->formatBytes($free) . ' libre / ' . $this->formatBytes($total) . ' total (' . $percent . '% utilisé)';
        } catch (\Exception $e) {
            return 'Non disponible';
        }
    }

    /**
     * Obtenir l'utilisation mémoire
     */
    protected function getMemoryUsage(): string
    {
        return $this->formatBytes(memory_get_usage(true));
    }

    /**
     * Formater les bytes
     */
    protected function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Exécuter une commande supervisorctl
     * Essaie d'abord sans sudo, puis avec sudo
     */
    protected function executeSupervisorCommand(string $action): array
    {
        $command = "tricycle-queue-worker:*";
        $output = '';
        $success = false;

        // Essayer d'abord sans sudo (si l'utilisateur est dans le groupe supervisor)
        $output = shell_exec("supervisorctl {$action} {$command} 2>&1");

        if ($output && !str_contains($output, 'error') && !str_contains($output, 'refused') && !str_contains($output, 'permission')) {
            $success = true;
        } else {
            // Essayer avec sudo
            $output = shell_exec("sudo -n supervisorctl {$action} {$command} 2>&1");

            if ($output && !str_contains($output, 'password') && !str_contains($output, 'sorry')) {
                $success = true;
            }
        }

        return [
            'success' => $success,
            'output' => $output,
            'command' => "sudo supervisorctl {$action} {$command}"
        ];
    }

    /**
     * Redémarrer les workers de queue
     */
    public function restartQueueWorkers()
    {
        try {
            $result = $this->executeSupervisorCommand('restart');

            if ($result['success']) {
                session()->flash('success', 'Workers de queue redémarrés avec succès');
                Log::info('Queue workers redémarrés', ['by' => auth()->user()->email, 'output' => $result['output']]);
            } else {
                session()->flash('warning', "Permission refusée. Exécutez manuellement sur le serveur: {$result['command']}");
            }

            sleep(1); // Attendre un peu pour que le statut se mette à jour
            $this->loadSupervisorStatus();
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Arrêter les workers de queue
     */
    public function stopQueueWorkers()
    {
        try {
            $result = $this->executeSupervisorCommand('stop');

            if ($result['success']) {
                session()->flash('success', 'Workers de queue arrêtés');
                Log::info('Queue workers arrêtés', ['by' => auth()->user()->email]);
            } else {
                session()->flash('warning', "Permission refusée. Exécutez manuellement sur le serveur: {$result['command']}");
            }

            sleep(1);
            $this->loadSupervisorStatus();
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Démarrer les workers de queue
     */
    public function startQueueWorkers()
    {
        try {
            $result = $this->executeSupervisorCommand('start');

            if ($result['success']) {
                session()->flash('success', 'Workers de queue démarrés');
                Log::info('Queue workers démarrés', ['by' => auth()->user()->email]);
            } else {
                session()->flash('warning', "Permission refusée. Exécutez manuellement sur le serveur: {$result['command']}");
            }

            sleep(1);
            $this->loadSupervisorStatus();
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Vider la queue des jobs en attente
     */
    public function clearPendingJobs()
    {
        try {
            DB::table('jobs')->truncate();
            session()->flash('success', 'Jobs en attente supprimés');
            Log::info('Jobs en attente vidés', ['by' => auth()->user()->email]);
            $this->loadQueueStats();
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Relancer tous les jobs échoués
     */
    public function retryFailedJobs()
    {
        try {
            Artisan::call('queue:retry', ['id' => ['all']]);
            session()->flash('success', 'Jobs échoués relancés');
            Log::info('Jobs échoués relancés', ['by' => auth()->user()->email]);
            $this->loadQueueStats();
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer tous les jobs échoués
     */
    public function clearFailedJobs()
    {
        try {
            Artisan::call('queue:flush');
            session()->flash('success', 'Jobs échoués supprimés');
            Log::info('Jobs échoués vidés', ['by' => auth()->user()->email]);
            $this->loadQueueStats();
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Exécuter le scheduler manuellement
     */
    public function runScheduler()
    {
        try {
            Artisan::call('schedule:run');
            $output = Artisan::output();
            session()->flash('success', 'Scheduler exécuté. ' . ($output ?: 'Aucune tâche à exécuter.'));
            Log::info('Scheduler exécuté manuellement', ['by' => auth()->user()->email]);
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Exécuter les notifications quotidiennes
     */
    public function runDailyNotifications()
    {
        try {
            Artisan::call('notifications:quotidiennes');
            $output = Artisan::output();
            session()->flash('success', 'Notifications quotidiennes envoyées. ' . $output);
            Log::info('Notifications quotidiennes exécutées manuellement', ['by' => auth()->user()->email]);
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Vider tous les caches
     */
    public function clearAllCaches()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            session()->flash('success', 'Tous les caches ont été vidés');
            Log::info('Caches vidés', ['by' => auth()->user()->email]);
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Optimiser l'application
     */
    public function optimizeApplication()
    {
        try {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            Artisan::call('event:cache');
            session()->flash('success', 'Application optimisée');
            Log::info('Application optimisée', ['by' => auth()->user()->email]);
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir les logs du queue worker
     */
    public function getQueueWorkerLogs()
    {
        $logFile = storage_path('logs/queue-worker.log');

        if (file_exists($logFile)) {
            return array_slice(file($logFile), -50);
        }

        return ['Fichier de log non trouvé'];
    }

    public function render()
    {
        return view('livewire.super-admin.server-processes', [
            'queueLogs' => $this->getQueueWorkerLogs(),
        ]);
    }
}

