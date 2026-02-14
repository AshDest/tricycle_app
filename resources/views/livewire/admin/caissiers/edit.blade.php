<div>
    <div class="mb-4">
        <h2>Éditer Caissier</h2>
    </div>
    <div class="card">
        <div class="card-body">
            <form wire:submit.prevent="save">
                <div class="mb-3">
                    <label class="form-label">Nom</label>
                    <input type="text" class="form-control" placeholder="Entrez le nom">
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-check"></i> Mettre à jour</button>
            </form>
        </div>
    </div>
</div>
