<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-shield-lock me-2 text-danger"></i>Super Admin Dashboard
            </h4>
            <p class="text-muted mb-0">Gestion avancée du système</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('super-admin.activity') }}" class="btn btn-outline-success">
                <i class="bi bi-activity me-1"></i>Activité
            </a>
            <a href="{{ route('super-admin.logs') }}" class="btn btn-outline-info">
                <i class="bi bi-file-text me-1"></i>Logs
            </a>
            <a href="{{ route('super-admin.database') }}" class="btn btn-danger">
                <i class="bi bi-database me-1"></i>Base de Données
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Informations Système -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header py-3 bg-dark text-white">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-cpu me-2"></i>Informations Système
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted">PHP Version</td>
                                <td class="fw-medium">{{ $systemInfo['php_version'] }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Laravel Version</td>
                                <td class="fw-medium">{{ $systemInfo['laravel_version'] }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Environnement</td>
                                <td>
                                    <span class="badge bg-{{ $systemInfo['environment'] === 'production' ? 'success' : 'warning' }}">
                                        {{ ucfirst($systemInfo['environment']) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Mode Debug</td>
                                <td>
                                    <span class="badge bg-{{ $systemInfo['debug_mode'] === 'Activé' ? 'danger' : 'success' }}">
                                        {{ $systemInfo['debug_mode'] }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Timezone</td>
                                <td class="fw-medium">{{ $systemInfo['timezone'] }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Cache Driver</td>
                                <td class="fw-medium">{{ $systemInfo['cache_driver'] }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Session Driver</td>
                                <td class="fw-medium">{{ $systemInfo['session_driver'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-people me-2"></i>Utilisateurs par Rôle
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-center">
                        <h2 class="fw-bold text-primary mb-0">{{ $totalUsers }}</h2>
                        <small class="text-muted">Utilisateurs total</small>
                    </div>
                    <div class="row g-2">
                        @foreach($usersParRole as $role => $count)
                        <div class="col-6">
                            <div class="bg-light rounded p-2 text-center">
                                <span class="d-block fw-bold">{{ $count }}</span>
                                <small class="text-muted">{{ ucfirst($role) }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques Base de Données -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header py-3 bg-success text-white">
            <h6 class="mb-0 fw-bold">
                <i class="bi bi-database me-2"></i>Statistiques Base de Données
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($databaseStats as $table => $info)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="border rounded p-3 text-center h-100">
                        <h4 class="fw-bold mb-1 {{ $info['count'] > 0 ? 'text-success' : 'text-muted' }}">
                            {{ number_format($info['count']) }}
                        </h4>
                        <small class="text-muted">{{ $info['label'] }}</small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Actions Système -->
    <div class="card border-0 shadow-sm">
        <div class="card-header py-3 bg-warning">
            <h6 class="mb-0 fw-bold">
                <i class="bi bi-gear me-2"></i>Actions Système
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card bg-light border-0 h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-trash3 fs-1 text-info mb-3 d-block"></i>
                            <h6 class="fw-bold">Vider les Caches</h6>
                            <p class="text-muted small mb-3">Supprime tous les fichiers de cache (vues, routes, config)</p>
                            <button wire:click="clearAllCaches" class="btn btn-info" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="clearAllCaches">
                                    <i class="bi bi-trash me-1"></i>Vider les Caches
                                </span>
                                <span wire:loading wire:target="clearAllCaches">
                                    <span class="spinner-border spinner-border-sm me-1"></span>En cours...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light border-0 h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-lightning fs-1 text-success mb-3 d-block"></i>
                            <h6 class="fw-bold">Optimiser l'Application</h6>
                            <p class="text-muted small mb-3">Cache les configurations et routes pour de meilleures performances</p>
                            <button wire:click="optimizeApp" class="btn btn-success" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="optimizeApp">
                                    <i class="bi bi-lightning me-1"></i>Optimiser
                                </span>
                                <span wire:loading wire:target="optimizeApp">
                                    <span class="spinner-border spinner-border-sm me-1"></span>En cours...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light border-0 h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-activity fs-1 text-primary mb-3 d-block"></i>
                            <h6 class="fw-bold">Moniteur d'Activité</h6>
                            <p class="text-muted small mb-3">Surveillance des utilisateurs connectés et activité en temps réel</p>
                            <a href="{{ route('super-admin.activity') }}" class="btn btn-primary">
                                <i class="bi bi-activity me-1"></i>Surveiller
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light border-0 h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-database-down fs-1 text-danger mb-3 d-block"></i>
                            <h6 class="fw-bold">Gestion Base de Données</h6>
                            <p class="text-muted small mb-3">Backup, restauration et suppression des données</p>
                            <a href="{{ route('super-admin.database') }}" class="btn btn-danger">
                                <i class="bi bi-database me-1"></i>Gérer
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deuxième ligne d'actions -->
            <div class="row g-3 mt-2">
                <div class="col-md-3">
                    <div class="card bg-light border-0 h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-file-text fs-1 text-secondary mb-3 d-block"></i>
                            <h6 class="fw-bold">Logs Système</h6>
                            <p class="text-muted small mb-3">Consultation et gestion des fichiers de logs d'application</p>
                            <a href="{{ route('super-admin.logs') }}" class="btn btn-secondary">
                                <i class="bi bi-file-text me-1"></i>Consulter
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

