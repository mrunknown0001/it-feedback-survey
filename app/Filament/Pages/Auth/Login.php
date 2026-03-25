<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public string $turnstileResponse = '';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
                View::make('filament.auth.turnstile-widget'),
            ])
            ->statePath('data');
    }

    public function authenticate(): ?\Filament\Http\Responses\Auth\Contracts\LoginResponse
    {
        if (empty($this->turnstileResponse)) {
            throw ValidationException::withMessages([
                'turnstileResponse' => 'Please complete the security verification.',
            ]);
        }

        $result = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret'   => config('services.turnstile.secret_key'),
            'response' => $this->turnstileResponse,
            'remoteip' => request()->ip(),
        ]);

        if (! $result->successful() || ! $result->json('success')) {
            $this->turnstileResponse = '';

            throw ValidationException::withMessages([
                'turnstileResponse' => 'Security verification failed. Please try again.',
            ]);
        }

        return parent::authenticate();
    }
}
