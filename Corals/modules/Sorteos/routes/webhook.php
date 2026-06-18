<?php

use Illuminate\Support\Facades\Route;

/*
 * Public webhook routes — no session, no CSRF, no auth.
 * ClubPago calls these endpoints to notify payment status changes.
 */
Route::post('sorteos/webhook/clubpago', 'WebhookController@handle')->name('sorteos.webhook.clubpago');
