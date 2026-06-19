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
    Route::get('boletos/create', fn() => redirect(url('sorteos/carteras/create'))->with('info', 'Los boletos se generan automáticamente al crear una cartera.'))->name('sorteos.boletos.create');
    Route::get('boletos/{boleto}/pdf', 'BoletosController@download')->name('sorteos.boletos.pdf');

    Route::get('carteras/generate', 'CarterasController@showGenerate')->name('sorteos.carteras.generate');
    Route::post('carteras/generate', 'CarterasController@generate')->name('sorteos.carteras.do-generate');
    Route::post('carteras/{cartera}/quick-status', 'CarterasController@quickStatus')->name('sorteos.carteras.quick-status');
    Route::get('carteras/import/template', 'CarterasController@downloadTemplate')->name('sorteos.carteras.import.template');
    Route::post('carteras/import', 'CarterasController@importCsv')->name('sorteos.carteras.import');
    Route::resource('carteras', 'CarterasController')->parameters(['carteras' => 'cartera']);

    Route::get('orders/carteras-by-asignado', 'OrdersController@carterasByAsignado')->name('sorteos.orders.carteras-by-asignado');
    Route::post('orders/{order}/confirm', 'OrdersController@confirmOrder')->name('sorteos.orders.confirm');
    Route::post('orders/{order}/cancel', 'OrdersController@cancelOrder')->name('sorteos.orders.cancel');
    Route::post('orders/{order}/pay', 'PaymentsController@initiate')->name('sorteos.orders.pay');
    Route::post('orders/{order}/record-payment', 'PaymentsController@recordPayment')->name('sorteos.orders.record-payment');
    Route::post('orders/{order}/update-reference', 'OrdersController@updateReference')->name('sorteos.orders.update-reference');
    Route::get('orders/{order}/tickets/download', 'OrdersController@downloadTickets')->name('sorteos.orders.tickets.download');
    Route::post('orders/{order}/resend', 'OrdersController@resendTickets')->name('sorteos.orders.resend');
    Route::resource('orders', 'OrdersController')->parameters(['orders' => 'order']);

    Route::post('asignados/{asignado}/assign-carteras', 'AsignadosController@assignCarteras')->name('sorteos.asignados.assign-carteras');
    Route::resource('asignados', 'AsignadosController')->parameters(['asignados' => 'asignado']);

    Route::get('audit', 'AuditController@index')->name('sorteos.audit.index');

    Route::get('reports',                'ReportsController@index')->name('sorteos.reports.index');
    Route::get('reports/sales',          'ReportsController@sales')->name('sorteos.reports.sales');
    Route::get('reports/buyers',         'ReportsController@buyers')->name('sorteos.reports.buyers');
    Route::get('reports/payment-methods','ReportsController@paymentMethods')->name('sorteos.reports.payment-methods');
    Route::get('reports/geographic',     'ReportsController@geographic')->name('sorteos.reports.geographic');

    Route::post('{sorteo}/change-status/{status}', 'SorteosController@changeStatus')->name('sorteos.change-status');
    Route::resource('/', 'SorteosController')->parameters(['' => 'sorteo']);
});

