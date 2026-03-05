<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-file-text me-2 text-info"></i>Logs Système
            </h4>
            <p class="text-muted mb-0">Consultation des logs de l'application</p>
        </div>
        <a href="{{ route('super-admin.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
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

    <div class="row g-4">
        <!-- Liste des fichiers de log -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-folder me-2"></i>Fichiers de Logs
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                        @forelse($logFiles as $file)
                        <button type="button" wire:click="selectFile('{{ $file['name'] }}')"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $selectedFile === $file['name'] ? 'active' : '' }}">
                            <div>
                                <i class="bi bi-file-earmark-text me-1"></i>
                                <small>{{ $file['name'] }}</small>
                            </div>
                            <small class="badge bg-{{ $selectedFile === $file['name'] ? 'light text-dark' : 'secondary' }}">
                                {{ $file['size'] }}
                            </small>
                        </button>
                        @empty
                        <div class="list-group-item text-center text-muted py-4">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            Aucun fichier de log
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu du log -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <input type="text" wire:model.live.debounce.500ms="search" class="form-control form-control-sm"
                                   placeholder="Rechercher dans les logs...">
                        </div>
                        <div class="col-md-3">
                            <select wire:model.live="logLines" class="form-select form-select-sm">
                                <option value="50">50 lignes</option>
                                <option value="100">100 lignes</option>
                                <option value="200">200 lignes</option>
                                <option value="500">500 lignes</option>
                                <option value="1000">1000 lignes</option>
                            </select>
                        </div>
                        <div class="col-md-5 text-end">
                            <div class="btn-group btn-group-sm">
                                <button wire:click="refreshLogs" class="btn btn-outline-info" title="Rafraîchir">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                                <button wire:click="downloadLog" class="btn btn-outline-primary" title="Télécharger">
                                    <i class="bi bi-download"></i>
                                </button>
                                <button wire:click="clearLog" class="btn btn-outline-warning" title="Vider"
                                        wire:confirm="Êtes-vous sûr de vouloir vider ce fichier de log ?">
                                    <i class="bi bi-eraser"></i>
                                </button>
                                @if($selectedFile !== 'laravel.log')
                                <button wire:click="deleteLog" class="btn btn-outline-danger" title="Supprimer"
                                        wire:confirm="Êtes-vous sûr de vouloir supprimer ce fichier ?">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($selectedFile)
                    <div class="bg-dark text-light p-3" style="max-height: 600px; overflow: auto; font-family: monospace; font-size: 12px; white-space: pre-wrap;">
                        @if(!empty($logContent))
                            @foreach(explode("\n", $logContent) as $line)
                                <div class="@if(str_contains($line, '[ERROR]') || str_contains($line, 'error')) text-danger @elseif(str_contains($line, '[WARNING]') || str_contains($line, 'warning')) text-warning @elseif(str_contains($line, '[INFO]')) text-info @else text-light @endif">{{ $line }}</div>
                            @endforeach
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Le fichier est vide ou aucun résultat correspondant
                            </div>
                        @endif
                    </div>
                    @else
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-arrow-left-circle fs-1 d-block mb-2"></i>
                        Sélectionnez un fichier de log à gauche
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

