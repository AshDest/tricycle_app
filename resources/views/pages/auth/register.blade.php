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
            <div class="auth-logo">
                <i class="bi bi-person-plus"></i>
            </div>
            <h4>Cr&eacute;er un compte</h4>
            <p>Remplissez le formulaire pour vous inscrire</p>
        </div>

        <div class="auth-body">
            <form wire:submit="register">
                <!-- Name -->
                <div class="mb-3">
                    <label for="name" class="form-label">Nom complet</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" wire:model="name" id="name" class="form-control @error('name') is-invalid @enderror" placeholder="Votre nom complet" autofocus>
                    </div>
                    @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Adresse email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" wire:model="email" id="email" class="form-control @error('email') is-invalid @enderror" placeholder="exemple@email.com">
                    </div>
                    @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" wire:model="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Cr&eacute;er un mot de passe">
                    </div>
                    @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" wire:model="password_confirmation" id="password_confirmation" class="form-control" placeholder="Confirmez le mot de passe">
                    </div>
                </div>

                <!-- Submit -->
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="register">
                            <i class="bi bi-person-plus me-1"></i> S'inscrire
                        </span>
                        <span wire:loading wire:target="register">
                            <span class="spinner-border spinner-border-sm me-1" role="status"></span> Inscription...
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <div class="auth-footer">
            <span class="small text-muted">D&eacute;j&agrave; un compte ?</span>
            <a href="{{ route('login') }}" class="small fw-medium text-decoration-none ms-1">Se connecter</a>
        </div>
    </div>
</div>
