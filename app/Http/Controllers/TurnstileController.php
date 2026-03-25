<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TurnstileController extends Controller
{
    public function show()
    {
        return view('turnstile.challenge');
    }

    public function verify(Request $request)
    {
        $token = $request->input('cf-turnstile-response');

        if (! $token) {
            return back()->withErrors(['turnstile' => 'Please complete the challenge.']);
        }

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret'   => config('services.turnstile.secret_key'),
            'response' => $token,
            'remoteip' => $request->ip(),
        ]);

        if (! $response->successful() || ! $response->json('success')) {
            return back()->withErrors(['turnstile' => 'Verification failed. Please try again.']);
        }

        $request->session()->put('turnstile_verified', true);

        return redirect()->route('feedback.form');
    }
}
