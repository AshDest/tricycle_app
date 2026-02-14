<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public string $password = '';

    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! auth()->guard('web')->validate([
            'email' => auth()->user()->email,
            'password' => $this->password,
        ])) {
            $this->addError('password', __('auth.password'));
            return;
        }

        session()->put('auth.password_confirmed_at', time());

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    @section('title', 'Confirmer le mot de passe')

    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="bi bi-shield-check"></i>
            </div>
            <h4>Confirmation requise</h4>
            <p>Veuillez confirmer votre mot de passe pour continuer.</p>
        </div>

        <div class="auth-body">
            <form wire:submit="confirmPassword">
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" wire:model="password" id="password" class="form-control @error('password') is-invalid @enderror" autofocus>
                    </div>
                    @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="confirmPassword">
                            <i class="bi bi-check-lg me-1"></i> Confirmer
                        </span>
                        <span wire:loading wire:target="confirmPassword">
                            <span class="spinner-border spinner-border-sm me-1" role="status"></span> V&eacute;rification...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
