<?php

use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\TurnstileController;
use App\Http\Middleware\CheckTurnstile;
use Illuminate\Support\Facades\Route;

Route::get('/', [TurnstileController::class, 'show'])->name('turnstile.challenge');
Route::post('/turnstile/verify', [TurnstileController::class, 'verify'])->name('turnstile.verify')->middleware('throttle:10,1');

Route::middleware(CheckTurnstile::class)->group(function () {
    Route::get('/form', [FeedbackController::class, 'show'])->name('feedback.form');
    Route::post('/feedback', [FeedbackController::class, 'store'])->middleware('throttle:5,1')->name('feedback.store');
    Route::get('/feedback/thanks', [FeedbackController::class, 'thanks'])->name('feedback.thanks');
});

// Temporary OAuth routes for Google Drive refresh token — remove after setup
Route::get('refresh-token', function () {
    $client = new \Google\Client();
    $client->setClientId(config('filesystems.disks.google.clientId'));
    $client->setClientSecret(config('filesystems.disks.google.clientSecret'));
    $client->setRedirectUri(url('/oauth2callback'));
    $client->addScope(\Google\Service\Drive::DRIVE);
    $client->setAccessType('offline');
    $client->setPrompt('consent');

    if (!request()->has('code')) {
        return redirect($client->createAuthUrl());
    }

    $token = $client->fetchAccessTokenWithAuthCode(request('code'));
    return response()->json($token);
});

Route::get('/oauth2callback', function () {
    $code = request('code');
    if ($code) {
        return redirect('/refresh-token?code=' . $code);
    }
    return response()->json(['error' => 'No authorization code received']);
});
