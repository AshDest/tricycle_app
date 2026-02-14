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
    @section('title', 'Mot de passe oublié')

    <div class="auth-card">
        <div class="auth-header">
            <h4>Mot de passe oublié ? 🔐</h4>
            <p>Pas de panique ! Entrez votre email pour recevoir un lien de réinitialisation</p>
        </div>

        <div class="auth-body">
            @if (session('status'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <form wire:submit="sendPasswordResetLink">
                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">Adresse email</label>
                    <div class="input-group">
                        <i class="bi bi-envelope input-icon"></i>
                        <input
                            type="email"
                            wire:model="email"
                            id="email"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="nom@entreprise.com"
                            autocomplete="email"
                            autofocus
                        >
                    </div>
                    @error('email')
                        <span class="form-text-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="sendPasswordResetLink">
                        <i class="bi bi-send"></i>
                        Envoyer le lien
                    </span>
                    <span wire:loading wire:target="sendPasswordResetLink">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                        Envoi en cours...
                    </span>
                </button>
            </form>
        </div>

        <div class="auth-footer">
            <a href="{{ route('login') }}" class="auth-footer-link">
                <i class="bi bi-arrow-left"></i> Retour à la connexion
            </a>
        </div>
    </div>
</div>
