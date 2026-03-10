<div>
    <!-- Header -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Processus Serveur</h4>
            <p class="text-muted mb-0">Gestion des workers, queues et tâches planifiées</p>
        </div>
        <button wire:click="loadAllStatus" class="btn btn-outline-primary">
            <i class="bi bi-arrow-clockwise me-1"></i>Actualiser
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Supervisor Status -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-gear-wide-connected me-2 text-primary"></i>Queue Workers</h5>
                </div>
                <div class="card-body">
                    @foreach($supervisorStatus as $process)
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                            <code>{{ $process['name'] }}</code>
                            @if($process['status'] === 'RUNNING')
                                <span class="badge bg-success">En cours</span>
                            @elseif($process['status'] === 'STOPPED')
                                <span class="badge bg-secondary">Arrêté</span>
                            @elseif($process['status'] === 'NOT_INSTALLED')
                                <span class="badge bg-danger">Non installé</span>
                            @else
                                <span class="badge bg-warning">{{ $process['status'] }}</span>
                            @endif
                        </div>
                    @endforeach

                    <div class="d-flex gap-2 mt-3">
                        <button wire:click="startQueueWorkers" class="btn btn-success btn-sm">
                            <i class="bi bi-play-fill me-1"></i>Démarrer
                        </button>
                        <button wire:click="stopQueueWorkers" class="btn btn-danger btn-sm">
                            <i class="bi bi-stop-fill me-1"></i>Arrêter
                        </button>
                        <button wire:click="restartQueueWorkers" class="btn btn-warning btn-sm">
                            <i class="bi bi-arrow-repeat me-1"></i>Redémarrer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Queue Statistics -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-inbox-fill me-2 text-info"></i>File d'attente</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded text-center">
                                <div class="fs-3 fw-bold text-primary">{{ $queueStats['jobs_pending'] ?? 0 }}</div>
                                <small class="text-muted">Jobs en attente</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded text-center">
                                <div class="fs-3 fw-bold text-danger">{{ $queueStats['jobs_failed'] ?? 0 }}</div>
                                <small class="text-muted">Jobs échoués</small>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button wire:click="retryFailedJobs" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Relancer échoués
                        </button>
                        <button wire:click="clearFailedJobs" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-trash me-1"></i>Vider échoués
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scheduler -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-calendar-check me-2 text-success"></i>Tâches planifiées</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="me-2">Statut Cron:</span>
                        @if($cronStatus === 'configured')
                            <span class="badge bg-success">Configuré</span>
                        @else
                            <span class="badge bg-danger">Non configuré</span>
                        @endif
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button wire:click="runScheduler" class="btn btn-primary btn-sm">
                            <i class="bi bi-play-circle me-1"></i>Exécuter scheduler
                        </button>
                        <button wire:click="runDailyNotifications" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-bell me-1"></i>Notifications quotidiennes
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cache -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning-charge me-2 text-warning"></i>Cache</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Dernier déploiement: {{ $lastDeployment }}</p>

                    <div class="d-flex gap-2 flex-wrap">
                        <button wire:click="clearAllCaches" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-trash me-1"></i>Vider caches
                        </button>
                        <button wire:click="optimizeApplication" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-speedometer2 me-1"></i>Optimiser
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Server Info -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-server me-2"></i>Informations Serveur</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($serverInfo as $key => $value)
                            <div class="col-md-4 col-lg-3">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted d-block">{{ ucfirst(str_replace('_', ' ', $key)) }}</small>
                                    <strong>{{ $value }}</strong>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Logs -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-terminal me-2"></i>Logs Queue Worker</h5>
                </div>
                <div class="card-body p-0">
                    <div class="bg-dark text-light p-3" style="max-height: 250px; overflow-y: auto; font-family: monospace; font-size: 0.7rem;">
                        @foreach($queueLogs as $log)
                            <div>{{ $log }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

