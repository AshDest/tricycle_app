<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();
        $this->form->authenticate();
        Session::regenerate();
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <!-- Session Status -->
    @if (session('status'))
    <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('status') }}
    </div>
    @endif

    <!-- Error Message -->
    @if (session('error'))
    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-exclamation-circle-fill me-2"></i>
        {{ session('error') }}
    </div>
    @endif

    <form wire:submit="login">
        <!-- Email -->
        <div class="mb-4">
            <label class="form-label fw-semibold" for="email">Adresse email</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-envelope text-muted"></i>
                </span>
                <input
                    wire:model="form.email"
                    type="email"
                    class="form-control form-control-lg border-start-0 ps-0 @error('form.email') is-invalid @enderror"
                    id="email"
                    placeholder="exemple@email.com"
                    required
                    autofocus
                    autocomplete="username"
                >
            </div>
            @error('form.email')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label fw-semibold mb-0" for="password">Mot de passe</label>
                @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-primary small text-decoration-none" wire:navigate>
                    Mot de passe oublié?
                </a>
                @endif
            </div>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-lock text-muted"></i>
                </span>
                <input
                    wire:model="form.password"
                    type="password"
                    class="form-control form-control-lg border-start-0 border-end-0 ps-0 @error('form.password') is-invalid @enderror"
                    id="password"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                >
                <button type="button" class="input-group-text bg-light border-start-0" onclick="togglePassword()">
                    <i class="bi bi-eye text-muted" id="toggleIcon"></i>
                </button>
            </div>
            @error('form.password')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="mb-4">
            <div class="form-check">
                <input wire:model="form.remember" type="checkbox" class="form-check-input" id="remember">
                <label class="form-check-label text-muted" for="remember">Se souvenir de moi</label>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary btn-lg w-100 mb-4" wire:loading.attr="disabled">
            <span wire:loading.remove>
                <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
            </span>
            <span wire:loading>
                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                Connexion...
            </span>
        </button>
    </form>

    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                pwd.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>
</div>


