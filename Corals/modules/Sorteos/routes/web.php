<?php

use Illuminate\Support\Facades\Route;

// Public buyer-facing routes (no auth required, need web sessions)
Route::get('boleto/{token}/validate', 'BoletosController@validateTicket')->name('sorteos.boleto.validate');
Route::get('mis-boletos/reenviar', 'BoletosController@resendForm')->name('sorteos.boletos.resend-form');
Route::post('mis-boletos/reenviar', 'BoletosController@resendByEmail')->name('sorteos.boletos.resend-email');
Route::get('orden/{hashedId}', 'PublicSorteoController@orderStatus')->name('sorteos.public.order');
Route::get('sorteo/{slug}', 'PublicSorteoController@show')->name('sorteos.public.show');
Route::post('sorteo/{slug}/comprar', 'PublicSorteoController@checkout')->name('sorteos.public.checkout');

// Admin routes — specific literal paths first, resource catch-alls last.
Route::group(['prefix' => 'sorteos'], function () {
    Route::resource('boletos', 'BoletosController')->parameters(['boletos' => 'boleto'])->only(['index', 'show']);
    Route::get('boletos/{boleto}/pdf', 'BoletosController@download')->name('sorteos.boletos.pdf');

    Route::get('carteras/import/template', 'CarterasController@downloadTemplate')->name('sorteos.carteras.import.template');
    Route::post('carteras/import', 'CarterasController@importCsv')->name('sorteos.carteras.import');
    Route::resource('carteras', 'CarterasController')->parameters(['carteras' => 'cartera']);

    Route::post('orders/{order}/confirm', 'OrdersController@confirmOrder')->name('sorteos.orders.confirm');
    Route::post('orders/{order}/cancel', 'OrdersController@cancelOrder')->name('sorteos.orders.cancel');
    Route::post('orders/{order}/pay', 'PaymentsController@initiate')->name('sorteos.orders.pay');
    Route::get('orders/{order}/tickets/download', 'OrdersController@downloadTickets')->name('sorteos.orders.tickets.download');
    Route::post('orders/{order}/resend', 'OrdersController@resendTickets')->name('sorteos.orders.resend');
    Route::resource('orders', 'OrdersController')->parameters(['orders' => 'order']);

    Route::get('audit', 'AuditController@index')->name('sorteos.audit.index');

    Route::get('reports',                'ReportsController@index')->name('sorteos.reports.index');
    Route::get('reports/sales',          'ReportsController@sales')->name('sorteos.reports.sales');
    Route::get('reports/buyers',         'ReportsController@buyers')->name('sorteos.reports.buyers');
    Route::get('reports/payment-methods','ReportsController@paymentMethods')->name('sorteos.reports.payment-methods');
    Route::get('reports/geographic',     'ReportsController@geographic')->name('sorteos.reports.geographic');

    Route::post('{sorteo}/change-status/{status}', 'SorteosController@changeStatus')->name('sorteos.change-status');
    Route::resource('/', 'SorteosController')->parameters(['' => 'sorteo']);
});

