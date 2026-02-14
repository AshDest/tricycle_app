<div>
    @section('title', 'Nouvelle Zone')
    <div class="page-header d-flex justify-content-between align-items-center">
        <div><h4>Nouvelle Zone</h4><p class="text-muted small mb-0">Cr&eacute;er une zone g&eacute;ographique</p></div>
        <a href="{{ route('admin.zones.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> Retour</a>
    </div>
    <div class="card"><div class="card-body">
        <form wire:submit.prevent="save">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nom <span class="text-danger">*</span></label><input type="text" wire:model="nom" class="form-control @error('nom') is-invalid @enderror">@error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                <div class="col-md-6"><label class="form-label">Communes</label><input type="text" wire:model="communes" class="form-control @error('communes') is-invalid @enderror" placeholder="S&eacute;par&eacute;es par virgules">@error('communes') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                <div class="col-12"><label class="form-label">Description</label><textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="3"></textarea>@error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                <div class="col-md-6"><div class="form-check form-switch mt-2"><input type="checkbox" wire:model="is_active" class="form-check-input" id="is_active"><label class="form-check-label" for="is_active">Zone active</label></div></div>
                <div class="col-12 mt-4"><hr><button type="submit" class="btn btn-primary" wire:loading.attr="disabled"><span wire:loading.remove wire:target="save"><i class="bi bi-check-lg me-1"></i> Enregistrer</span><span wire:loading wire:target="save"><span class="spinner-border spinner-border-sm me-1"></span> Enregistrement...</span></button><a href="{{ route('admin.zones.index') }}" class="btn btn-light ms-2">Annuler</a></div>
            </div>
        </form>
    </div></div>
</div>
