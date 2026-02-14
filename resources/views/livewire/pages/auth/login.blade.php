<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
 public LoginForm $form;

 /**
 * Handle an incoming authentication request.
 */
 public function login(): void
 {
 $this->validate();

 $this->form->authenticate();

 Session::regenerate();

 $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
 }
}; ?>

<div >
 <div >
 <div class="absolute-top-right d-lg-none p-3 p-sm-5">
 <a href="#" class="toggle btn-white btn btn-sm btn-outline-secondary btn-light" data-target="athPromo">
 <em class="icon ni ni-info"></em>
 </a>
 </div>
 <div >
 <div class="brand-logo pb-4 text-center">
 <a href="{{ url('/') }}" class="logo-link">
 <img class="logo-light logo-img logo-img-lg" src="{{ asset('images/logo.png') }}" srcset="{{ asset('images/logo2x.png') }} 2x" alt="logo">
 <img class="logo-dark logo-img logo-img-lg" src="{{ asset('images/logo-dark.png') }}" srcset="{{ asset('images/logo-dark2x.png') }} 2x" alt="logo-dark">
 </a>
 </div>
 <div class="mb-4">
 <div class="mb-4">
 <h4 >Connexion</h4>
 <div >
 <p>Accédez à votre espace {{ config('app.name') }} avec vos identifiants.</p>
 </div>
 </div>
 </div>

 {{-- Session Status --}}
 @if (session('status'))
 <div class="alert alert-success alert-icon mb-3">
 <em class="icon ni ni-check-circle"></em>
 <strong>{{ session('status') }}</strong>
 </div>
 @endif

 <form wire:submit="login">
 {{-- Email --}}
 <div class="mb-3">
 <div class="form-label-group">
 <label class="form-label" for="email">Email</label>
 <a class="link link-primary link-sm" tabindex="-1" href="#">Besoin d'aide?</a>
 </div>
 <div >
 <input
 wire:model="form.email"
 type="email"
 class="form-control form-control-lg @error('form.email') is-invalid @enderror"
 id="email"
 placeholder="Entrez votre adresse email"
 required
 autofocus
 autocomplete="username"
 >
 @error('form.email')
 <span class="invalid-feedback" role="alert">
 <strong>{{ $message }}</strong>
 </span>
 @enderror
 </div>
 </div>

 {{-- Password --}}
 <div class="mb-3">
 <div class="form-label-group">
 <label class="form-label" for="password">Mot de passe</label>
 @if (Route::has('password.request'))
 <a class="link link-primary link-sm" tabindex="-1" href="{{ route('password.request') }}" wire:navigate>
 Mot de passe oublié?
 </a>
 @endif
 </div>
 <div >
 <a tabindex="-1" href="#" class="form-icon form-icon-right passcode-switch lg" data-target="password">
 <em class="passcode-icon icon-show icon ni ni-eye"></em>
 <em class="passcode-icon icon-hide icon ni ni-eye-off"></em>
 </a>
 <input
 wire:model="form.password"
 type="password"
 class="form-control form-control-lg @error('form.password') is-invalid @enderror"
 id="password"
 placeholder="Entrez votre mot de passe"
 required
 autocomplete="current-password"
 >
 @error('form.password')
 <span class="invalid-feedback" role="alert">
 <strong>{{ $message }}</strong>
 </span>
 @enderror
 </div>
 </div>

 {{-- Remember Me --}}
 <div class="mb-3">
 <div class="custom-control custom-control-xs custom-checkbox">
 <input wire:model="form.remember" type="checkbox" class="custom-control-input" id="remember">
 <label class="custom-control-label" for="remember">Se souvenir de moi</label>
 </div>
 </div>

 {{-- Submit Button --}}
 <div class="mb-3">
 <button type="submit" class="btn btn-primary btn-block" wire:loading.attr="disabled">
 <span wire:loading.remove>Se connecter</span>
 <span wire:loading>
 <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
 Connexion en cours...
 </span>
 </button>
 </div>
 </form>

 <div class="form-note-s2 text-center pt-4">
 Pas encore de compte? <a href="{{ route('register') }}" wire:navigate>Inscrivez-vous</a>
 </div>
 </div>

 {{-- Footer --}}
 <div >
 <div class="d-flex justify-content-between align-items-center">
 <ul class="nav nav-sm">
 <li class="nav-item">
 <a class="nav-link" href="#">Conditions d'utilisation</a>
 </li>
 <li class="nav-item">
 <a class="nav-link" href="#">Politique de confidentialité</a>
 </li>
 <li class="nav-item">
 <a class="nav-link" href="#">Aide</a>
 </li>
 </ul>
 </div>
 <div class="mt-3">
 <p>&copy; {{ date('Y') }} New Technology Hub Sarl. Tous droits réservés.</p>
 </div>
 </div>
 </div>

 {{-- Right Side - Promo --}}
 <div data-content="athPromo" data-bs-toggle-body="true">
 <div class="slider-wrap w-100 w-max-550px p-3 p-sm-5 m-auto">
 <div class="slider-init" data-slick='{"dots":true, "arrows":false, "autoplay":true, "autoplaySpeed":4000}'>
 <div class="slider-item">
 <div >
 <div >
 <img class="round" src="{{ asset('images/slides/promo-a.png') }}" srcset="{{ asset('images/slides/promo-a2x.png') }} 2x" alt="">
 </div>
 <div >
 <h4>Gestion de Flotte</h4>
 <p>Gérez efficacement votre flotte de motos-tricycles avec notre système centralisé et automatisé.</p>
 </div>
 </div>
 </div>
 <div class="slider-item">
 <div >
 <div >
 <img class="round" src="{{ asset('images/slides/promo-b.png') }}" srcset="{{ asset('images/slides/promo-b2x.png') }} 2x" alt="">
 </div>
 <div >
 <h4>Suivi des Versements</h4>
 <p>Suivez en temps réel les versements journaliers et les arriérés de vos motards.</p>
 </div>
 </div>
 </div>
 <div class="slider-item">
 <div >
 <div >
 <img class="round" src="{{ asset('images/slides/promo-c.png') }}" srcset="{{ asset('images/slides/promo-c2x.png') }} 2x" alt="">
 </div>
 <div >
 <h4>Rapports Complets</h4>
 <p>Générez des rapports quotidiens, hebdomadaires et mensuels pour une meilleure prise de décision.</p>
 </div>
 </div>
 </div>
 </div>
 <div class="slider-dots"></div>
 </div>
 </div>
</div>


