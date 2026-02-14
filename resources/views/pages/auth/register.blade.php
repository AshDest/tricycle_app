<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        auth()->login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    @section('title', 'Inscription')

    <div class="auth-card">
        <div class="auth-header">
            <h4>Créer un compte ✨</h4>
            <p>Rejoignez Tricycle App en quelques étapes</p>
        </div>

        <div class="auth-body">
            <form wire:submit="register">
                <!-- Name -->
                <div class="form-group">
                    <label for="name" class="form-label">Nom complet</label>
                    <div class="input-group">
                        <i class="bi bi-person input-icon"></i>
                        <input
                            type="text"
                            wire:model="name"
                            id="name"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="Jean Dupont"
                            autocomplete="name"
                            autofocus
                        >
                    </div>
                    @error('name')
                        <span class="form-text-error">{{ $message }}</span>
                    @enderror
                </div>

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
                            placeholder="Minimum 8 caractères"
                            autocomplete="new-password"
                        >
                        <button type="button" class="toggle-password" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="form-text-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                    <div class="input-group">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <input
                            type="password"
                            wire:model="password_confirmation"
                            id="password_confirmation"
                            class="form-control"
                            placeholder="Retapez votre mot de passe"
                            autocomplete="new-password"
                        >
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="register">
                        <i class="bi bi-person-plus"></i>
                        Créer mon compte
                    </span>
                    <span wire:loading wire:target="register">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                        Création en cours...
                    </span>
                </button>
            </form>
        </div>

        <div class="auth-footer">
            <span class="auth-footer-text">Déjà un compte ?</span>
            <a href="{{ route('login') }}" class="auth-footer-link">Se connecter</a>
        </div>
    </div>
</div>
