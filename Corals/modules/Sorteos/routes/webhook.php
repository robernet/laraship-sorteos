<?php

use Illuminate\Support\Facades\Route;

/*
 * Public webhook routes — no session, no CSRF, no auth.
 * ClubPago calls these endpoints to notify payment status changes.
 */
Route::post('webhooks/clubpago', 'PaymentsController@webhook')->name('sorteos.webhook.clubpago');
Route::get('boleto/{token}/validate', 'BoletosController@validateTicket')->name('sorteos.boleto.validate');
Route::get('mis-boletos/reenviar', 'BoletosController@resendForm')->name('sorteos.boletos.resend-form');
Route::post('mis-boletos/reenviar', 'BoletosController@resendByEmail')->name('sorteos.boletos.resend-email');

Route::get('sorteo/{slug}', 'PublicSorteoController@show')->name('sorteos.public.show');
Route::post('sorteo/{slug}/comprar', 'PublicSorteoController@checkout')->name('sorteos.public.checkout');
Route::get('orden/{hashedId}', 'PublicSorteoController@orderStatus')->name('sorteos.public.order');
