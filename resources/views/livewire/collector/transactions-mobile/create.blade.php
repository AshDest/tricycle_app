<div>
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-phone-fill me-2 text-primary"></i>Nouvelle Transaction Mobile
            </h4>
            <p class="text-muted mb-0">Enregistrer un envoi ou retrait d'argent mobile</p>
        </div>
        <a href="{{ route('collector.transactions-mobile.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    <div class="row">
        <div class="col-lg-8">
            <form wire:submit="submit">
                <!-- Type de transaction -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-arrow-left-right me-2"></i>Type de Transaction</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check card p-3 h-100 {{ $type === 'envoi' ? 'border-danger bg-danger bg-opacity-10' : '' }}">
                                    <input class="form-check-input" type="radio" wire:model.live="type" value="envoi" id="typeEnvoi">
                                    <label class="form-check-label d-flex align-items-center gap-3 w-100" for="typeEnvoi">
                                        <i class="bi bi-arrow-up-circle fs-2 text-danger"></i>
                                        <div>
                                            <strong class="d-block">Envoi d'argent</strong>
                                            <small class="text-muted">Envoyer de l'argent à un bénéficiaire</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check card p-3 h-100 {{ $type === 'retrait' ? 'border-success bg-success bg-opacity-10' : '' }}">
                                    <input class="form-check-input" type="radio" wire:model.live="type" value="retrait" id="typeRetrait">
                                    <label class="form-check-label d-flex align-items-center gap-3 w-100" for="typeRetrait">
                                        <i class="bi bi-arrow-down-circle fs-2 text-success"></i>
                                        <div>
                                            <strong class="d-block">Retrait d'argent</strong>
                                            <small class="text-muted">Retirer de l'argent en cash</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Détails -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2"></i>Détails de la Transaction</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Opérateur <span class="text-danger">*</span></label>
                                <select wire:model="operateur" class="form-select @error('operateur') is-invalid @enderror">
                                    @foreach($operateurs as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('operateur')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Numéro de téléphone <span class="text-danger">*</span></label>
                                <input type="text" wire:model="numero_telephone" class="form-control @error('numero_telephone') is-invalid @enderror" placeholder="+243 XXX XXX XXX">
                                @error('numero_telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom du {{ $type === 'envoi' ? 'bénéficiaire' : 'titulaire' }}</label>
                                <input type="text" wire:model="nom_beneficiaire" class="form-control" placeholder="Nom complet">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Référence opérateur</label>
                                <input type="text" wire:model="reference_operateur" class="form-control" placeholder="Code de confirmation">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Montant <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" wire:model.live="montant" class="form-control @error('montant') is-invalid @enderror" placeholder="0" min="1">
                                    <span class="input-group-text">FC</span>
                                </div>
                                @error('montant')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Frais de transaction</label>
                                <div class="input-group">
                                    <input type="number" wire:model.live="frais" class="form-control" placeholder="0" min="0">
                                    <span class="input-group-text">FC</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Montant net</label>
                                <div class="input-group">
                                    <input type="text" value="{{ number_format($this->montant_net) }}" class="form-control bg-light" readonly>
                                    <span class="input-group-text">FC</span>
                                </div>
                                <small class="text-muted">{{ $type === 'envoi' ? 'Montant + Frais' : 'Montant - Frais' }}</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Motif <span class="text-danger">*</span></label>
                                <textarea wire:model="motif" class="form-control @error('motif') is-invalid @enderror" rows="2" placeholder="Ex: Paiement propriétaire, Retrait pour caisse..."></textarea>
                                @error('motif')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Notes</label>
                                <textarea wire:model="notes" class="form-control" rows="2" placeholder="Informations complémentaires..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Bouton -->
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-{{ $type === 'envoi' ? 'danger' : 'success' }} btn-lg flex-grow-1" wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <i class="bi bi-{{ $type === 'envoi' ? 'send' : 'download' }} me-2"></i>
                            Enregistrer {{ $type === 'envoi' ? 'l\'envoi' : 'le retrait' }}
                        </span>
                        <span wire:loading>
                            <span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...
                        </span>
                    </button>
                    <a href="{{ route('collector.transactions-mobile.index') }}" class="btn btn-outline-secondary btn-lg">Annuler</a>
                </div>
            </form>
        </div>
        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-lightbulb me-2 text-warning"></i>Aide</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-{{ $type === 'envoi' ? 'danger' : 'success' }} border-0 mb-3">
                        <strong>{{ $type === 'envoi' ? 'Envoi d\'argent' : 'Retrait d\'argent' }}</strong><br>
                        <small>
                            @if($type === 'envoi')
                            Utilisez cette option pour enregistrer un transfert d'argent vers un bénéficiaire (paiement propriétaire, etc.)
                            @else
                            Utilisez cette option pour enregistrer un retrait d'argent mobile en cash.
                            @endif
                        </small>
                    </div>
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Vérifiez le numéro de téléphone</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Notez la référence opérateur</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Incluez les frais si applicable</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>Précisez le motif de la transaction</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
