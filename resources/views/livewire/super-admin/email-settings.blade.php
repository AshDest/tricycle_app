<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-envelope-gear me-2 text-danger"></i>Configuration Email & Notifications
            </h4>
            <p class="text-muted mb-0">Gérez les paramètres d'envoi d'emails et surveillez les files d'attente</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="toggleEmails" class="btn {{ $emailsActifs ? 'btn-success' : 'btn-danger' }}">
                <i class="bi bi-{{ $emailsActifs ? 'check-circle' : 'x-circle' }} me-1"></i>
                Emails {{ $emailsActifs ? 'Actifs' : 'Désactivés' }}
            </button>
            <button wire:click="verifierConfiguration" class="btn btn-outline-primary">
                <i class="bi bi-check-circle me-1"></i>Vérifier
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                        <i class="bi bi-envelope text-primary fs-4"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">{{ number_format($stats['notifications_aujourdhui']) }}</h4>
                        <small class="text-muted">Notifications aujourd'hui</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3">
                        <i class="bi bi-calendar-week text-info fs-4"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">{{ number_format($stats['notifications_semaine']) }}</h4>
                        <small class="text-muted">Cette semaine</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                        <i class="bi bi-hourglass-split text-warning fs-4"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">{{ number_format($stats['jobs_en_attente']) }}</h4>
                        <small class="text-muted">Jobs en attente</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                        <i class="bi bi-exclamation-triangle text-danger fs-4"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">{{ number_format($stats['jobs_echoues']) }}</h4>
                        <small class="text-muted">Jobs échoués</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'config' ? 'active' : '' }}" wire:click="$set('activeTab', 'config')">
                <i class="bi bi-gear me-1"></i>Configuration
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'test' ? 'active' : '' }}" wire:click="$set('activeTab', 'test')">
                <i class="bi bi-send me-1"></i>Test Email
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'queue' ? 'active' : '' }}" wire:click="$set('activeTab', 'queue')">
                <i class="bi bi-list-task me-1"></i>File d'attente
                @if($stats['jobs_en_attente'] > 0)
                <span class="badge bg-warning ms-1">{{ $stats['jobs_en_attente'] }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'failed' ? 'active' : '' }}" wire:click="$set('activeTab', 'failed')">
                <i class="bi bi-x-circle me-1"></i>Échecs
                @if($stats['jobs_echoues'] > 0)
                <span class="badge bg-danger ms-1">{{ $stats['jobs_echoues'] }}</span>
                @endif
            </button>
        </li>
    </ul>

    <!-- Tab Content: Configuration -->
    @if($activeTab === 'config')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-server me-2 text-primary"></i>Configuration SMTP
                    </h6>
                    @if($configFromDatabase)
                    <span class="badge bg-success"><i class="bi bi-database me-1"></i>Configuration personnalisée</span>
                    @else
                    <span class="badge bg-secondary"><i class="bi bi-file-code me-1"></i>Configuration serveur (.env)</span>
                    @endif
                </div>
                <div class="card-body">
                    <form wire:submit="sauvegarderConfiguration">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Type de mailer <span class="text-danger">*</span></label>
                                <select wire:model="mailMailer" class="form-select @error('mailMailer') is-invalid @enderror">
                                    <option value="smtp">SMTP</option>
                                    <option value="log">Log (pas d'envoi réel)</option>
                                    <option value="sendmail">Sendmail</option>
                                </select>
                                @error('mailMailer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                @if($mailMailer === 'log')
                                <small class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>Mode test - les emails seront dans les logs</small>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Encryption</label>
                                <select wire:model="mailEncryption" class="form-select @error('mailEncryption') is-invalid @enderror">
                                    <option value="tls">TLS</option>
                                    <option value="ssl">SSL</option>
                                    <option value="null">Aucune</option>
                                </select>
                                @error('mailEncryption')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-semibold text-dark">Serveur SMTP <span class="text-danger">*</span></label>
                                <input type="text" wire:model="mailHost" class="form-control @error('mailHost') is-invalid @enderror" placeholder="smtp.gmail.com">
                                @error('mailHost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Port <span class="text-danger">*</span></label>
                                <input type="number" wire:model="mailPort" class="form-control @error('mailPort') is-invalid @enderror" placeholder="587">
                                @error('mailPort')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Nom d'utilisateur (Email)</label>
                                <input type="text" wire:model="mailUsername" class="form-control @error('mailUsername') is-invalid @enderror" placeholder="votre-email@gmail.com">
                                @error('mailUsername')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">
                                    Mot de passe
                                    @if($hasPassword)
                                    <span class="badge bg-success ms-2"><i class="bi bi-check me-1"></i>Configuré</span>
                                    @endif
                                </label>
                                <input type="password" wire:model="mailPassword" class="form-control @error('mailPassword') is-invalid @enderror" placeholder="{{ $hasPassword ? '••••••••' : 'Mot de passe ou App Password' }}">
                                @error('mailPassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted">Laissez vide pour garder le mot de passe actuel</small>
                            </div>

                            <hr class="my-3">

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Adresse d'expédition <span class="text-danger">*</span></label>
                                <input type="email" wire:model="mailFromAddress" class="form-control @error('mailFromAddress') is-invalid @enderror" placeholder="noreply@newtechnologyhub.org">
                                @error('mailFromAddress')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Nom d'expédition <span class="text-danger">*</span></label>
                                <input type="text" wire:model="mailFromName" class="form-control @error('mailFromName') is-invalid @enderror" placeholder="Tricycle App - NTH">
                                @error('mailFromName')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="sauvegarderConfiguration">
                                    <i class="bi bi-save me-1"></i>Sauvegarder
                                </span>
                                <span wire:loading wire:target="sauvegarderConfiguration">
                                    <span class="spinner-border spinner-border-sm me-1"></span>Sauvegarde...
                                </span>
                            </button>
                            @if($configFromDatabase)
                            <button type="button" wire:click="reinitialiserConfiguration" class="btn btn-outline-secondary" onclick="return confirm('Réinitialiser la configuration aux valeurs par défaut du serveur ?')">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Réinitialiser
                            </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-lightning me-2 text-warning"></i>Configuration Rapide</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-danger btn-sm text-start"
                            wire:click="$set('mailMailer', 'smtp'); $set('mailHost', 'smtp.gmail.com'); $set('mailPort', '587'); $set('mailEncryption', 'tls')">
                            <i class="bi bi-google me-2"></i>Gmail SMTP
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm text-start"
                            wire:click="$set('mailMailer', 'smtp'); $set('mailHost', 'smtp.office365.com'); $set('mailPort', '587'); $set('mailEncryption', 'tls')">
                            <i class="bi bi-microsoft me-2"></i>Office 365
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm text-start"
                            wire:click="$set('mailMailer', 'smtp'); $set('mailHost', 'smtp.sendgrid.net'); $set('mailPort', '587'); $set('mailEncryption', 'tls')">
                            <i class="bi bi-send me-2"></i>SendGrid
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm text-start"
                            wire:click="$set('mailMailer', 'log')">
                            <i class="bi bi-file-text me-2"></i>Mode Test (Log)
                        </button>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-question-circle me-2 text-info"></i>Aide</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0 mb-3 small">
                        <strong><i class="bi bi-google me-1"></i>Gmail :</strong><br>
                        Utilisez un <a href="https://myaccount.google.com/apppasswords" target="_blank">mot de passe d'application</a>.
                    </div>
                    <div class="alert alert-light border mb-0 small">
                        <strong>Ports courants :</strong><br>
                        • 587 (TLS) - Recommandé<br>
                        • 465 (SSL)<br>
                        • 25 (sans encryption)
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Tab Content: Test -->
    @if($activeTab === 'test')
    <div class="row">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-send me-2 text-success"></i>Envoyer un email de test</h6>
                </div>
                <div class="card-body">
                    @if(!$emailsActifs)
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Attention :</strong> Les emails sont désactivés.
                    </div>
                    @endif

                    <form wire:submit="envoyerTestEmail">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Adresse email destinataire</label>
                            <input type="email" wire:model="testEmail" class="form-control @error('testEmail') is-invalid @enderror" placeholder="email@example.com">
                            @error('testEmail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Sujet</label>
                            <input type="text" wire:model="testSubject" class="form-control @error('testSubject') is-invalid @enderror">
                            @error('testSubject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark">Message</label>
                            <textarea wire:model="testMessage" class="form-control @error('testMessage') is-invalid @enderror" rows="4"></textarea>
                            @error('testMessage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="envoyerTestEmail">
                                <i class="bi bi-send me-1"></i>Envoyer le test
                            </span>
                            <span wire:loading wire:target="envoyerTestEmail">
                                <span class="spinner-border spinner-border-sm me-1"></span>Envoi...
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-info-circle me-2 text-warning"></i>Configuration actuelle</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted">Mailer</td>
                                <td class="fw-semibold">
                                    <span class="badge {{ $mailMailer === 'log' ? 'bg-warning' : 'bg-success' }}">{{ strtoupper($mailMailer) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Serveur</td>
                                <td class="fw-semibold text-dark">{{ $mailHost ?: 'Non configuré' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Port</td>
                                <td class="fw-semibold text-dark">{{ $mailPort ?: 'Non configuré' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Expéditeur</td>
                                <td class="fw-semibold text-dark">{{ $mailFromAddress ?: 'Non configuré' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Statut</td>
                                <td>
                                    @if($emailsActifs)
                                    <span class="badge bg-success"><i class="bi bi-check me-1"></i>Actif</span>
                                    @else
                                    <span class="badge bg-danger"><i class="bi bi-x me-1"></i>Désactivé</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Tab Content: Queue -->
    @if($activeTab === 'queue')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-list-task me-2 text-warning"></i>Jobs en attente</h6>
            <span class="badge bg-warning">{{ $stats['jobs_en_attente'] }} job(s)</span>
        </div>
        <div class="card-body p-0">
            @if($jobsEnAttente->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">ID</th>
                            <th class="border-0">Queue</th>
                            <th class="border-0">Payload</th>
                            <th class="border-0">Tentatives</th>
                            <th class="border-0">Créé le</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jobsEnAttente as $job)
                        <tr>
                            <td class="text-dark fw-semibold">{{ $job->id }}</td>
                            <td><span class="badge bg-info">{{ $job->queue }}</span></td>
                            <td>
                                @php
                                    $payload = json_decode($job->payload, true);
                                    $displayName = $payload['displayName'] ?? 'Unknown';
                                @endphp
                                <small class="text-muted">{{ Str::limit($displayName, 50) }}</small>
                            </td>
                            <td>{{ $job->attempts }}</td>
                            <td><small class="text-muted">{{ \Carbon\Carbon::createFromTimestamp($job->created_at)->diffForHumans() }}</small></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-light">{{ $jobsEnAttente->links() }}</div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3 mb-0">Aucun job en attente</p>
            </div>
            @endif
        </div>
    </div>
    <div class="alert alert-info mt-4">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Worker Status :</strong> {{ ucfirst($workerStatus) }}
        <br><small class="text-muted">Pour traiter les jobs : <code>php artisan queue:work</code></small>
    </div>
    @endif

    <!-- Tab Content: Failed -->
    @if($activeTab === 'failed')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-x-circle me-2 text-danger"></i>Jobs échoués</h6>
            <div class="d-flex gap-2">
                @if($stats['jobs_echoues'] > 0)
                <button wire:click="relancerTousJobs" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-arrow-repeat me-1"></i>Relancer tous
                </button>
                <button wire:click="viderJobsEchoues" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer tous les jobs échoués ?')">
                    <i class="bi bi-trash me-1"></i>Vider tout
                </button>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            @if($jobsEchoues->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">ID</th>
                            <th class="border-0">Queue</th>
                            <th class="border-0">Erreur</th>
                            <th class="border-0">Échoué le</th>
                            <th class="border-0 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jobsEchoues as $job)
                        <tr>
                            <td class="text-dark fw-semibold">{{ Str::limit($job->uuid, 8) }}</td>
                            <td><span class="badge bg-secondary">{{ $job->queue }}</span></td>
                            <td><small class="text-danger">{{ Str::limit($job->exception, 80) }}</small></td>
                            <td><small class="text-muted">{{ \Carbon\Carbon::parse($job->failed_at)->diffForHumans() }}</small></td>
                            <td class="text-end">
                                <button wire:click="relancerJob('{{ $job->uuid }}')" class="btn btn-sm btn-outline-success"><i class="bi bi-arrow-repeat"></i></button>
                                <button wire:click="supprimerJob({{ $job->id }})" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ?')"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-light">{{ $jobsEchoues->links() }}</div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3 mb-0">Aucun job échoué</p>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
