<?php

use App\Http\Controllers\FeedbackController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FeedbackController::class, 'show'])->name('feedback.form');
Route::post('/feedback', [FeedbackController::class, 'store'])->middleware('throttle:5,1')->name('feedback.store');
Route::get('/feedback/thanks', [FeedbackController::class, 'thanks'])->name('feedback.thanks');
