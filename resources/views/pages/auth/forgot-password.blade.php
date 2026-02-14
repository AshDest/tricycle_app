<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public string $email = '';

    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));
            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
    @section('title', 'Mot de passe oubli&eacute;')

    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="bi bi-key"></i>
            </div>
            <h4>Mot de passe oubli&eacute; ?</h4>
            <p>Entrez votre email pour recevoir un lien de r&eacute;initialisation</p>
        </div>

        <div class="auth-body">
            @if (session('status'))
                <div class="alert alert-success d-flex align-items-center gap-2 small" role="alert">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>{{ session('status') }}</div>
                </div>
            @endif

            <form wire:submit="sendPasswordResetLink">
                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Adresse email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" wire:model="email" id="email" class="form-control @error('email') is-invalid @enderror" placeholder="exemple@email.com" autofocus>
                    </div>
                    @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <!-- Submit -->
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="sendPasswordResetLink">
                            <i class="bi bi-send me-1"></i> Envoyer le lien
                        </span>
                        <span wire:loading wire:target="sendPasswordResetLink">
                            <span class="spinner-border spinner-border-sm me-1" role="status"></span> Envoi...
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <div class="auth-footer">
            <a href="{{ route('login') }}" class="small text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Retour &agrave; la connexion
            </a>
        </div>
    </div>
</div>
