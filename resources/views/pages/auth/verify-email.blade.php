<?php

use App\Livewire\Actions\Logout;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public function sendVerification(): void
    {
        if (auth()->user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: false);
            return;
        }

        auth()->user()->sendEmailVerificationNotification();
        session()->flash('status', 'verification-link-sent');
    }

    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    @section('title', 'V&eacute;rification email')

    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="bi bi-envelope-check"></i>
            </div>
            <h4>V&eacute;rifiez votre email</h4>
            <p>Un lien de v&eacute;rification a &eacute;t&eacute; envoy&eacute; &agrave; votre adresse email.</p>
        </div>

        <div class="auth-body">
            @if (session('status') == 'verification-link-sent')
                <div class="alert alert-success d-flex align-items-center gap-2 small" role="alert">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>Un nouveau lien de v&eacute;rification a &eacute;t&eacute; envoy&eacute;.</div>
                </div>
            @endif

            <div class="d-grid gap-2">
                <button wire:click="sendVerification" class="btn btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="sendVerification">
                        <i class="bi bi-send me-1"></i> Renvoyer le lien
                    </span>
                    <span wire:loading wire:target="sendVerification">
                        <span class="spinner-border spinner-border-sm me-1" role="status"></span> Envoi...
                    </span>
                </button>
                <button wire:click="logout" class="btn btn-outline-secondary">
                    <i class="bi bi-box-arrow-right me-1"></i> Se d&eacute;connecter
                </button>
            </div>
        </div>
    </div>
</div>
