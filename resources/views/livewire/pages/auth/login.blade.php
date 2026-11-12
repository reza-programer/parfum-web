<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
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

<div>
    <div class="mb-8 text-center">
        <h1 class="heading-page">Masuk</h1>
        <p class="mt-2 text-body text-ink-2">Masuk untuk belanja, melacak pesanan, dan menyimpan wishlist.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="form.email" id="email" class="mt-1 block w-full" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="form.password" id="password" class="mt-1 block w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="size-4 rounded-none border-ink/40 text-ink focus:ring-0 focus:ring-offset-0" name="remember">
                <span class="ms-2 text-sm text-ink-2">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="mt-8 flex flex-col-reverse items-stretch gap-4 text-center">
            @if (Route::has('password.request'))
                <a class="link text-label text-ink-2 hover:text-ink" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="h-12 w-full">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    <p class="mt-6 border-t border-line pt-6 text-center text-sm text-ink-2">
        Belum punya akun?
        <a href="{{ route('register') }}" wire:navigate class="font-medium text-ink underline decoration-ink/30 underline-offset-4 hover:decoration-ink">Daftar</a>
    </p>
</div>
