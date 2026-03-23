<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-plus-circle me-2 text-info"></i>Enregistrer un Lavage
            </h4>
            <p class="text-muted mb-0">Enregistrer un nouveau lavage de moto</p>
        </div>
        <a href="{{ route('cleaner.lavages.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    <!-- Info répartition -->
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-info-circle fs-4"></i>
            <div>
                <strong>Système de répartition :</strong><br>
                <small>
                    <span class="badge bg-info">Moto du système</span> → 80% pour vous, 20% pour OKAMI<br>
                    <span class="badge bg-secondary">Moto externe</span> → 100% pour vous
                </small>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Formulaire -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-droplet me-2 text-info"></i>Détails du Lavage
                    </h6>
                </div>
                <div class="card-body">
                    <form wire:submit="enregistrer">
                        <!-- Type de moto -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Type de moto <span class="text-danger">*</span></label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check card p-3 {{ !$is_externe ? 'border-info bg-info bg-opacity-10' : '' }}">
                                        <input class="form-check-input" type="radio" wire:model.live="is_externe" value="0" id="motoInterne">
                                        <label class="form-check-label d-flex align-items-center gap-2" for="motoInterne">
                                            <i class="bi bi-bicycle text-info fs-4"></i>
                                            <div>
                                                <strong>Moto du système</strong>
                                                <small class="d-block text-muted">80% pour vous</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check card p-3 {{ $is_externe ? 'border-secondary bg-secondary bg-opacity-10' : '' }}">
                                        <input class="form-check-input" type="radio" wire:model.live="is_externe" value="1" id="motoExterne">
                                        <label class="form-check-label d-flex align-items-center gap-2" for="motoExterne">
                                            <i class="bi bi-truck text-secondary fs-4"></i>
                                            <div>
                                                <strong>Moto externe</strong>
                                                <small class="d-block text-muted">100% pour vous</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(!$is_externe)
                        <!-- Sélection moto du système -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Sélectionner la moto <span class="text-danger">*</span></label>
                            <select wire:model.live="moto_id" class="form-select @error('moto_id') is-invalid @enderror">
                                <option value="">-- Choisir une moto --</option>
                                @foreach($motos as $moto)
                                <option value="{{ $moto->id }}">
                                    {{ $moto->plaque_immatriculation }} - {{ $moto->proprietaire?->user?->name ?? 'N/A' }}
                                </option>
                                @endforeach
                            </select>
                            @error('moto_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($motoSelectionnee)
                        <!-- Infos moto sélectionnée -->
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Plaque</small>
                                        <strong>{{ $motoSelectionnee->plaque_immatriculation }}</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Propriétaire</small>
                                        <strong>{{ $motoSelectionnee->proprietaire?->user?->name ?? 'N/A' }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @else
                        <!-- Informations moto externe -->
                        <div class="card bg-light mb-4">
                            <div class="card-header bg-transparent py-2">
                                <small class="fw-semibold">Informations moto externe</small>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small">Plaque <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="plaque_externe" class="form-control @error('plaque_externe') is-invalid @enderror" placeholder="Ex: AB1234CD">
                                        @error('plaque_externe')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Nom propriétaire</label>
                                        <input type="text" wire:model="proprietaire_externe" class="form-control" placeholder="Nom (optionnel)">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Téléphone</label>
                                        <input type="text" wire:model="telephone_externe" class="form-control" placeholder="Téléphone (optionnel)">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Type de lavage -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Type de lavage <span class="text-danger">*</span></label>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-check card p-3 {{ $type_lavage === 'simple' ? 'border-info bg-info bg-opacity-10' : '' }}">
                                        <input class="form-check-input" type="radio" wire:model.live="type_lavage" value="simple" id="lavageSimple">
                                        <label class="form-check-label" for="lavageSimple">
                                            <i class="bi bi-droplet text-info me-1"></i>
                                            <strong>Simple</strong>
                                            <div class="text-primary fw-bold">{{ number_format($prixSimple) }} FC</div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check card p-3 {{ $type_lavage === 'complet' ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                                        <input class="form-check-input" type="radio" wire:model.live="type_lavage" value="complet" id="lavageComplet">
                                        <label class="form-check-label" for="lavageComplet">
                                            <i class="bi bi-droplet-fill text-primary me-1"></i>
                                            <strong>Complet</strong>
                                            <div class="text-primary fw-bold">{{ number_format($prixComplet) }} FC</div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check card p-3 {{ $type_lavage === 'premium' ? 'border-warning bg-warning bg-opacity-10' : '' }}">
                                        <input class="form-check-input" type="radio" wire:model.live="type_lavage" value="premium" id="lavagePremium">
                                        <label class="form-check-label" for="lavagePremium">
                                            <i class="bi bi-stars text-warning me-1"></i>
                                            <strong>Premium</strong>
                                            <div class="text-primary fw-bold">{{ number_format($prixPremium) }} FC</div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Prix et remise -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Prix de base</label>
                                <div class="input-group">
                                    <input type="number" wire:model="prix_base" class="form-control" readonly>
                                    <span class="input-group-text">FC</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Remise</label>
                                <div class="input-group">
                                    <input type="number" wire:model.live="remise" class="form-control" min="0" placeholder="0">
                                    <span class="input-group-text">FC</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Prix final</label>
                                <div class="input-group">
                                    <input type="number" wire:model="prix_final" class="form-control form-control-lg fw-bold text-success" readonly>
                                    <span class="input-group-text">FC</span>
                                </div>
                            </div>
                        </div>

                        <!-- Prévisualisation répartition -->
                        <div class="card bg-light mb-4">
                            <div class="card-header bg-transparent py-2">
                                <small class="fw-semibold"><i class="bi bi-pie-chart me-1"></i>Répartition</small>
                            </div>
                            <div class="card-body py-3">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="border-end">
                                            <small class="text-muted d-block">Votre part ({{ $is_externe ? '100%' : '80%' }})</small>
                                            <h4 class="fw-bold text-success mb-0">{{ number_format($partCleanerPreview) }} FC</h4>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Part OKAMI ({{ $is_externe ? '0%' : '20%' }})</small>
                                        <h4 class="fw-bold text-warning mb-0">{{ number_format($partOkamiPreview) }} FC</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mode de paiement -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Mode de paiement <span class="text-danger">*</span></label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check card p-3 {{ $mode_paiement === 'cash' ? 'border-success bg-success bg-opacity-10' : '' }}">
                                        <input class="form-check-input" type="radio" wire:model.live="mode_paiement" value="cash" id="modeCash">
                                        <label class="form-check-label d-flex align-items-center gap-2" for="modeCash">
                                            <i class="bi bi-cash text-success fs-4"></i>
                                            <span>Cash</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check card p-3 {{ $mode_paiement === 'mobile_money' ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                                        <input class="form-check-input" type="radio" wire:model.live="mode_paiement" value="mobile_money" id="modeMobile">
                                        <label class="form-check-label d-flex align-items-center gap-2" for="modeMobile">
                                            <i class="bi bi-phone text-primary fs-4"></i>
                                            <span>Mobile Money</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Notes / Observations</label>
                            <textarea wire:model="notes" class="form-control" rows="2" placeholder="Remarques éventuelles..."></textarea>
                        </div>

                        <!-- Bouton soumettre -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-info btn-lg" wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    <i class="bi bi-check-circle me-2"></i>Enregistrer le Lavage
                                </span>
                                <span wire:loading>
                                    <span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Tarifs -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-tags me-2 text-info"></i>Tarifs</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span><i class="bi bi-droplet text-info me-2"></i>Simple</span>
                            <strong>{{ number_format($prixSimple) }} FC</strong>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span><i class="bi bi-droplet-fill text-primary me-2"></i>Complet</span>
                            <strong>{{ number_format($prixComplet) }} FC</strong>
                        </li>
                        <li class="d-flex justify-content-between py-2">
                            <span><i class="bi bi-stars text-warning me-2"></i>Premium</span>
                            <strong>{{ number_format($prixPremium) }} FC</strong>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Instructions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-primary"></i>Instructions</h6>
                </div>
                <div class="card-body">
                    <ol class="mb-0 ps-3">
                        <li class="mb-2">Choisissez si la moto est du système ou externe</li>
                        <li class="mb-2">Sélectionnez le type de lavage</li>
                        <li class="mb-2">Appliquez une remise si nécessaire</li>
                        <li class="mb-2">Sélectionnez le mode de paiement</li>
                        <li>Validez l'enregistrement</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

