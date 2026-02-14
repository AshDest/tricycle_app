<?php

use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    #[Rule('required|string|email')]
    public string $email = '';

    #[Rule('required|string')]
    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $this->validate();

        if (! auth()->attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    @section('title', 'Connexion')

    <div class="auth-card">
        <div class="auth-header">
            <h4>Bon retour ! 👋</h4>
            <p>Entrez vos identifiants pour accéder à votre espace</p>
        </div>

        <div class="auth-body">
            @if (session('status'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <form wire:submit="login">
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

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <i class="bi bi-lock input-icon"></i>
                        <input
                            type="password"
                            wire:model="password"
                            id="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="••••••••"
                            autocomplete="current-password"
                        >
                        <button type="button" class="toggle-password" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="form-text-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Remember & Forgot -->
                <div class="form-options">
                    <div class="form-check">
                        <input
                            type="checkbox"
                            wire:model="remember"
                            class="form-check-input"
                            id="remember"
                        >
                        <label class="form-check-label" for="remember">Se souvenir de moi</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="forgot-link">Mot de passe oublié ?</a>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="login">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Se connecter
                    </span>
                    <span wire:loading wire:target="login">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                        Connexion en cours...
                    </span>
                </button>
            </form>
        </div>

        <div class="auth-footer">
            <span class="auth-footer-text">Pas encore de compte ?</span>
            <a href="{{ route('register') }}" class="auth-footer-link">Créer un compte</a>
        </div>
    </div>
</div>
