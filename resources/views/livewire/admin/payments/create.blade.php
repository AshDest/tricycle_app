<div>
    <div class="mb-4">
        <h2>Formulaire</h2>
    </div>
    <div class="card">
        <div class="card-body">
            <form wire:submit.prevent="save">
                <div class="mb-3">
                    <label class="form-label">Champ</label>
                    <input type="text" class="form-control" placeholder="Entrez la valeur">
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-check"></i> Enregistrer</button>
            </form>
        </div>
    </div>
</div>
