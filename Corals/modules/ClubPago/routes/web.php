<?php

// Admin: ClubPago reference list (read-only)
Route::group(['middleware' => 'auth'], function () {
    Route::resource('clubpago/clubpago-references', 'ClubPagoReferencesController', ['only' => ['index']]);
});

// Terminal/POS routes — validated by verificaHeader(), no auth middleware needed
Route::group(['prefix' => 'clubpago'], function () {
    Route::get('Service/ConsultaReferencia', 'ClubPagoController@consultaReferencia');
    Route::match(['get', 'post'], 'Service/PagoReferencia', 'ClubPagoController@pagoReferencia');
    Route::delete('Service/CancelaPago', 'ClubPagoController@cancelaPago');
});
