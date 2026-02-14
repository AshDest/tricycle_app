<div>
    @section('title', 'D&eacute;tail Versement')
    <div class="page-header d-flex justify-content-between align-items-center">
        <div><h4>D&eacute;tail du Versement</h4><p class="text-muted small mb-0">#{{ $versement->id }}</p></div>
        <a href="{{ route('admin.versements.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> Retour</a>
    </div>
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h6 class="mb-0 fw-semibold">Informations</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label text-muted small mb-1">Motard</label><p class="fw-medium mb-0">{{ $versement->motard->user->name ?? 'N/A' }}</p></div>
                        <div class="col-md-6"><label class="form-label text-muted small mb-1">Moto</label><p class="fw-medium mb-0">{{ $versement->moto->plaque_immatriculation ?? 'N/A' }}</p></div>
                        <div class="col-md-6"><label class="form-label text-muted small mb-1">Montant vers&eacute;</label><p class="fw-bold text-primary mb-0 fs-5">{{ number_format($versement->montant) }} FCFA</p></div>
                        <div class="col-md-6"><label class="form-label text-muted small mb-1">Montant attendu</label><p class="fw-medium mb-0">{{ number_format($versement->montant_attendu) }} FCFA</p></div>
                        <div class="col-md-6"><label class="form-label text-muted small mb-1">Mode de paiement</label><p class="mb-0"><span class="badge bg-light text-dark">{{ ucfirst(str_replace('_', ' ', $versement->mode_paiement ?? '-')) }}</span></p></div>
                        <div class="col-md-6"><label class="form-label text-muted small mb-1">Statut</label><p class="mb-0">@php $colors = ['paye' => 'success', 'partiel' => 'warning', 'en_retard' => 'danger', 'non_paye' => 'secondary']; @endphp<span class="badge bg-{{ $colors[$versement->statut] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $versement->statut)) }}</span></p></div>
                        <div class="col-md-6"><label class="form-label text-muted small mb-1">Date</label><p class="mb-0">{{ $versement->date_versement?->format('d/m/Y') ?? '-' }}</p></div>
                        <div class="col-md-6"><label class="form-label text-muted small mb-1">Enregistr&eacute; le</label><p class="mb-0">{{ $versement->created_at->format('d/m/Y H:i') }}</p></div>
                        @if($versement->notes)<div class="col-12"><label class="form-label text-muted small mb-1">Notes</label><p class="mb-0 bg-light p-3 rounded">{{ $versement->notes }}</p></div>@endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0 fw-semibold">R&eacute;sum&eacute;</h6></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted small">Vers&eacute;</span><span class="fw-bold text-primary">{{ number_format($versement->montant) }} FCFA</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted small">Attendu</span><span class="fw-medium">{{ number_format($versement->montant_attendu) }} FCFA</span></div>
                    <hr>
                    @php $diff = $versement->montant - $versement->montant_attendu; @endphp
                    <div class="d-flex justify-content-between"><span class="text-muted small">Diff&eacute;rence</span><span class="fw-bold {{ $diff >= 0 ? 'text-success' : 'text-danger' }}">{{ $diff >= 0 ? '+' : '' }}{{ number_format($diff) }} FCFA</span></div>
                </div>
            </div>
            @php $progress = $versement->montant_attendu > 0 ? min(100, ($versement->montant / $versement->montant_attendu) * 100) : 0; @endphp
            <div class="card">
                <div class="card-header"><h6 class="mb-0 fw-semibold">Progression</h6></div>
                <div class="card-body">
                    <div class="progress mb-2" style="height:10px;"><div class="progress-bar bg-{{ $progress >= 100 ? 'success' : ($progress >= 50 ? 'warning' : 'danger') }}" style="width:{{ $progress }}%"></div></div>
                    <p class="text-muted small text-center mb-0">{{ number_format($progress, 0) }}% du montant attendu</p>
                </div>
            </div>
        </div>
    </div>
</div>
