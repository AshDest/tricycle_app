<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        Session::flash('password-updated', 'Mot de passe mis à jour avec succès.');
    }
}; ?>

<div>
    @if (session('password-updated'))
    <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('password-updated') }}
    </div>
    @endif

    <form wire:submit="updatePassword">
        <div class="mb-4">
            <label for="current_password" class="form-label fw-semibold">Mot de passe actuel</label>
            <input
                wire:model="current_password"
                type="password"
                class="form-control @error('current_password') is-invalid @enderror"
                id="current_password"
                autocomplete="current-password"
            >
            @error('current_password')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="form-label fw-semibold">Nouveau mot de passe</label>
            <input
                wire:model="password"
                type="password"
                class="form-control @error('password') is-invalid @enderror"
                id="password"
                autocomplete="new-password"
            >
            @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label fw-semibold">Confirmer le mot de passe</label>
            <input
                wire:model="password_confirmation"
                type="password"
                class="form-control @error('password_confirmation') is-invalid @enderror"
                id="password_confirmation"
                autocomplete="new-password"
            >
            @error('password_confirmation')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-warning" wire:loading.attr="disabled">
            <span wire:loading.remove>
                <i class="bi bi-shield-lock me-2"></i>Changer le mot de passe
            </span>
            <span wire:loading>
                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                Modification...
            </span>
        </button>
    </form>
</div>
