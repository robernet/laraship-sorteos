<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => ''], function () {
    // More specific routes must come before sorteos/{sorteo} to avoid route model binding conflicts
    Route::get('sorteos/carteras/import/template', 'CarterasController@downloadTemplate')->name('sorteos.carteras.import.template');
    Route::post('sorteos/carteras/import', 'CarterasController@importCsv')->name('sorteos.carteras.import');
    Route::resource('sorteos/carteras', 'CarterasController')->parameters(['carteras' => 'cartera']);
    Route::resource('sorteos/boletos', 'BoletosController')->parameters(['boletos' => 'boleto'])->only(['index', 'show']);
    Route::get('sorteos/boletos/{boleto}/pdf', 'BoletosController@download')->name('sorteos.boletos.pdf');
    Route::resource('sorteos/orders', 'OrdersController')->parameters(['orders' => 'order']);
    Route::post('sorteos/orders/{order}/confirm', 'OrdersController@confirmOrder')->name('sorteos.orders.confirm');
    Route::post('sorteos/orders/{order}/cancel', 'OrdersController@cancelOrder')->name('sorteos.orders.cancel');
    Route::post('sorteos/orders/{order}/pay', 'PaymentsController@initiate')->name('sorteos.orders.pay');
    Route::get('sorteos/orders/{order}/tickets/download', 'OrdersController@downloadTickets')->name('sorteos.orders.tickets.download');
    Route::post('sorteos/orders/{order}/resend', 'OrdersController@resendTickets')->name('sorteos.orders.resend');

    // Audit log
    Route::get('sorteos/audit', 'AuditController@index')->name('sorteos.audit.index');

    // Reports
    Route::get('sorteos/reports',                'ReportsController@index')->name('sorteos.reports.index');
    Route::get('sorteos/reports/sales',          'ReportsController@sales')->name('sorteos.reports.sales');
    Route::get('sorteos/reports/buyers',         'ReportsController@buyers')->name('sorteos.reports.buyers');
    Route::get('sorteos/reports/payment-methods','ReportsController@paymentMethods')->name('sorteos.reports.payment-methods');
    Route::get('sorteos/reports/geographic',     'ReportsController@geographic')->name('sorteos.reports.geographic');

    Route::resource('sorteos', 'SorteosController');
    Route::post('sorteos/{sorteo}/change-status/{status}', 'SorteosController@changeStatus')
        ->name('sorteos.change-status');
});
