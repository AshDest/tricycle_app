<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-gear-wide-connected me-2 text-warning"></i>Nouveau Service KWADO
            </h4>
            <p class="text-muted mb-0">Enregistrer un service de réparation de pneus</p>
        </div>
        <a href="{{ route('cleaner.kwado.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">
        <!-- Formulaire -->
        <div class="col-lg-8">
            <form wire:submit="enregistrer">
                <!-- Type de véhicule -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-bicycle me-2 text-info"></i>Véhicule</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check card p-3 h-100 {{ !$is_externe ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                                        <input class="form-check-input" type="radio" wire:model.live="is_externe" value="0" id="typeInterne" {{ !$is_externe ? 'checked' : '' }}>
                                        <label class="form-check-label d-block" for="typeInterne">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <i class="bi bi-bicycle fs-5 text-primary"></i>
                                                <strong>Moto du système</strong>
                                            </div>
                                            <small class="text-muted">Moto enregistrée dans l'application</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check card p-3 h-100 {{ $is_externe ? 'border-secondary bg-secondary bg-opacity-10' : '' }}">
                                        <input class="form-check-input" type="radio" wire:model.live="is_externe" value="1" id="typeExterne">
                                        <label class="form-check-label d-block" for="typeExterne">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <i class="bi bi-truck fs-5 text-secondary"></i>
                                                <strong>Véhicule externe</strong>
                                            </div>
                                            <small class="text-muted">Véhicule non enregistré</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(!$is_externe)
                        <!-- Sélection moto interne -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Sélectionner la moto <span class="text-danger">*</span></label>
                            <select wire:model.live="moto_id" class="form-select @error('moto_id') is-invalid @enderror">
                                <option value="">-- Choisir une moto --</option>
                                @foreach($motos as $moto)
                                <option value="{{ $moto->id }}">
                                    {{ $moto->plaque_immatriculation }} - {{ $moto->proprietaire?->user?->name ?? 'N/A' }}
                                </option>
                                @endforeach
                            </select>
                            @error('moto_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        @if($motoSelectionnee)
                        <div class="alert alert-info py-2 mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Propriétaire: <strong>{{ $motoSelectionnee->proprietaire?->user?->name ?? 'N/A' }}</strong>
                            — Plaque: <strong>{{ $motoSelectionnee->plaque_immatriculation }}</strong>
                        </div>
                        @endif
                        @else
                        <!-- Infos moto externe -->
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Plaque <span class="text-danger">*</span></label>
                                <input type="text" wire:model="plaque_externe" class="form-control @error('plaque_externe') is-invalid @enderror" placeholder="AB 1234 CD">
                                @error('plaque_externe') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Propriétaire</label>
                                <input type="text" wire:model="proprietaire_externe" class="form-control" placeholder="Nom du propriétaire">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Téléphone</label>
                                <input type="text" wire:model="telephone_externe" class="form-control" placeholder="Téléphone">
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Détails du service -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-wrench me-2 text-warning"></i>Détails du Service</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Type de service <span class="text-danger">*</span></label>
                                <select wire:model="type_service" class="form-select @error('type_service') is-invalid @enderror">
                                    @foreach($typesService as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type_service') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Position du pneu</label>
                                <select wire:model="position_pneu" class="form-select">
                                    <option value="">-- Non précisé --</option>
                                    @foreach($positionsPneu as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description du service</label>
                                <textarea wire:model="description_service" class="form-control" rows="2" placeholder="Détails supplémentaires..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Montants -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-cash-stack me-2 text-success"></i>Montants</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Prix du service <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" wire:model.live="prix" class="form-control @error('prix') is-invalid @enderror" placeholder="0" min="0">
                                    <span class="input-group-text">FC</span>
                                </div>
                                @error('prix') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Coût des pièces</label>
                                <div class="input-group">
                                    <input type="number" wire:model.live="cout_pieces" class="form-control" placeholder="0" min="0">
                                    <span class="input-group-text">FC</span>
                                </div>
                                <small class="text-muted">Chambre à air, pneu, etc.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Montant encaissé <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg">
                                    <input type="number" wire:model="montant_encaisse" class="form-control fw-bold text-success @error('montant_encaisse') is-invalid @enderror" placeholder="0" min="0">
                                    <span class="input-group-text">FC</span>
                                </div>
                                @error('montant_encaisse') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        @if((float)$montant_encaisse > 0)
                        <div class="alert alert-success mt-3 mb-0">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <small class="d-block text-muted">Encaissé</small>
                                    <strong class="text-success fs-5">{{ number_format((float)$montant_encaisse) }} FC</strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="d-block text-muted">Coût pièces</small>
                                    <strong class="text-danger">{{ number_format((float)$cout_pieces) }} FC</strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="d-block text-muted">Bénéfice net</small>
                                    <strong class="text-primary fs-5">{{ number_format(max(0, (float)$montant_encaisse - (float)$cout_pieces)) }} FC</strong>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Mode de paiement et notes -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mode de paiement</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check card p-3 flex-fill text-center {{ $mode_paiement === 'cash' ? 'border-success bg-success bg-opacity-10' : '' }}">
                                        <input class="form-check-input d-none" type="radio" wire:model="mode_paiement" value="cash" id="modeCash">
                                        <label class="form-check-label d-block" for="modeCash" style="cursor: pointer;">
                                            <i class="bi bi-cash fs-4 text-success d-block mb-1"></i>
                                            <small class="fw-semibold">Cash</small>
                                        </label>
                                    </div>
                                    <div class="form-check card p-3 flex-fill text-center {{ $mode_paiement === 'mobile_money' ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                                        <input class="form-check-input d-none" type="radio" wire:model="mode_paiement" value="mobile_money" id="modeMobile">
                                        <label class="form-check-label d-block" for="modeMobile" style="cursor: pointer;">
                                            <i class="bi bi-phone fs-4 text-primary d-block mb-1"></i>
                                            <small class="fw-semibold">Mobile</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Notes</label>
                                <textarea wire:model="notes" class="form-control" rows="3" placeholder="Observations..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bouton de soumission -->
                <div class="d-grid">
                    <button type="submit" class="btn btn-warning btn-lg" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="enregistrer">
                            <i class="bi bi-check-circle me-2"></i>Enregistrer le Service KWADO
                        </span>
                        <span wire:loading wire:target="enregistrer">
                            <span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Panneau latéral -->
        <div class="col-lg-4">
            <!-- Info KWADO -->
            <div class="card mb-4 border-warning">
                <div class="card-header py-3 bg-warning bg-opacity-10">
                    <h6 class="mb-0 fw-bold text-warning">
                        <i class="bi bi-gear-wide-connected me-2"></i>KWADO
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Service de réparation de pneus. Les recettes sont ajoutées directement à la caisse du service lavage.
                    </p>
                    <div class="list-group list-group-flush">
                        @foreach($typesService as $key => $label)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <small>{{ $label }}</small>
                            <i class="bi bi-check-circle text-warning"></i>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Solde en caisse -->
            <div class="card border-0 bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success rounded-circle p-3">
                            <i class="bi bi-wallet2 fs-4 text-white"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Solde en caisse</small>
                            <h4 class="mb-0 fw-bold text-success">{{ number_format(auth()->user()->cleaner?->solde_actuel ?? 0) }} FC</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

