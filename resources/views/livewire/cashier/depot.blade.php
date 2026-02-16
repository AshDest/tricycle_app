<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-box-arrow-up me-2 text-primary"></i>Dépôt au Collecteur
            </h4>
            <p class="text-muted mb-0">Déposer votre solde auprès du collecteur</p>
        </div>
        <a href="{{ route('cashier.depots.historique') }}" class="btn btn-outline-secondary">
            <i class="bi bi-clock-history me-1"></i>Historique
        </a>
    </div>

    <!-- Message Alert -->
    @if($message)
    <div class="alert alert-{{ $messageType }} alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-{{ $messageType === 'success' ? 'check-circle' : ($messageType === 'danger' ? 'exclamation-circle' : 'info-circle') }} me-2"></i>
        {{ $message }}
        <button type="button" class="btn-close" wire:click="closeMessage" aria-label="Close"></button>
    </div>
    @endif

    <div class="row g-4">
        <!-- Formulaire de dépôt -->
        <div class="col-lg-7">
            <!-- Solde actuel -->
            <div class="card bg-primary text-white mb-4">
                <div class="card-body py-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Votre Solde Actuel</h6>
                            <h2 class="fw-bold mb-0">{{ number_format($soldeActuel) }} FC</h2>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="bi bi-wallet2 fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Collecteur disponible -->
            @if($tourneeEnCours && $collecteurDisponible)
            <div class="alert alert-success mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-md bg-success bg-opacity-25 text-success rounded-circle">
                        <i class="bi bi-person-check fs-4"></i>
                    </div>
                    <div>
                        <strong>Collecteur disponible :</strong> {{ $collecteurDisponible->user->name ?? 'N/A' }}
                        <br><small class="text-muted">Tournée #{{ $tourneeEnCours->id }} - {{ $tourneeEnCours->zone ?? 'Zone non définie' }}</small>
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-warning mb-4">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Aucune tournée en cours</strong><br>
                <small>Aucun collecteur n'est programmé pour votre zone aujourd'hui. Veuillez attendre ou contacter l'administration.</small>
            </div>
            @endif

            <!-- Formulaire -->
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-cash-coin me-2 text-success"></i>Effectuer un Dépôt</h6>
                </div>
                <div class="card-body">
                    <form wire:submit="effectuerDepot">
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Montant à déposer <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <input type="number" wire:model="montant"
                                       class="form-control @error('montant') is-invalid @enderror"
                                       placeholder="0" min="1" max="{{ $soldeActuel }}"
                                       @if(!$tourneeEnCours) disabled @endif>
                                <span class="input-group-text">FC</span>
                                <button type="button" class="btn btn-outline-primary" wire:click="deposerTout"
                                        @if(!$tourneeEnCours || $soldeActuel <= 0) disabled @endif>
                                    Tout déposer
                                </button>
                            </div>
                            @error('montant')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            @if($soldeActuel > 0)
                            <small class="text-muted">Maximum: {{ number_format($soldeActuel) }} FC</small>
                            @endif
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Notes / Commentaires</label>
                            <textarea wire:model="notes" class="form-control" rows="3"
                                      placeholder="Ajouter des commentaires si nécessaire..."
                                      @if(!$tourneeEnCours) disabled @endif></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg"
                                    wire:loading.attr="disabled"
                                    @if(!$tourneeEnCours || $soldeActuel <= 0) disabled @endif>
                                <span wire:loading.remove>
                                    <i class="bi bi-box-arrow-up me-2"></i>Confirmer le Dépôt
                                </span>
                                <span wire:loading>
                                    <span class="spinner-border spinner-border-sm me-2"></span>Traitement...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-5">
            <!-- Instructions -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-info"></i>Instructions</h6>
                </div>
                <div class="card-body">
                    <ol class="mb-0 ps-3">
                        <li class="mb-2">Attendez le passage du collecteur dans votre zone</li>
                        <li class="mb-2">Vérifiez que la tournée est active</li>
                        <li class="mb-2">Entrez le montant exact à déposer</li>
                        <li class="mb-2">Confirmez le dépôt</li>
                        <li>Le collecteur validera la réception</li>
                    </ol>
                </div>
            </div>

            <!-- Dépôts récents -->
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-secondary"></i>Dépôts Récents</h6>
                </div>
                <div class="card-body p-0">
                    @if(count($depotsRecents) > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($depotsRecents as $depot)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-medium">{{ number_format($depot->montant_collecte ?? 0) }} FC</span>
                                <small class="text-muted d-block">{{ $depot->created_at?->format('d/m/Y H:i') }}</small>
                            </div>
                            <div class="text-end">
                                @if($depot->valide_par_collecteur)
                                <span class="badge badge-soft-success"><i class="bi bi-check-circle me-1"></i>Validé</span>
                                @else
                                <span class="badge badge-soft-warning">En attente</span>
                                @endif
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <p class="mb-0">Aucun dépôt récent</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
