<?php

//Sorteo
Breadcrumbs::register('sorteos_sorteos', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(trans('Sorteos::module.sorteo.title'), url(config('sorteos.models.sorteo.resource_url')));
});

Breadcrumbs::register('sorteos_sorteo_create_edit', function ($breadcrumbs) {
    $breadcrumbs->parent('sorteos_sorteos');
    $breadcrumbs->push(view()->shared('title_singular'));
});

Breadcrumbs::register('sorteos_sorteo_show', function ($breadcrumbs) {
    $breadcrumbs->parent('sorteos_sorteos');
    $breadcrumbs->push(view()->shared('title_singular'));
});

//Cartera
Breadcrumbs::register('sorteos_carteras', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(trans('Sorteos::module.cartera.title'), url(config('sorteos.models.cartera.resource_url')));
});

Breadcrumbs::register('sorteos_cartera_create_edit', function ($breadcrumbs) {
    $breadcrumbs->parent('sorteos_carteras');
    $breadcrumbs->push(view()->shared('title_singular'));
});

Breadcrumbs::register('sorteos_cartera_show', function ($breadcrumbs) {
    $breadcrumbs->parent('sorteos_carteras');
    $breadcrumbs->push(view()->shared('title_singular'));
});

//Boleto
Breadcrumbs::register('sorteos_boletos', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(trans('Sorteos::module.boleto.title'), url(config('sorteos.models.boleto.resource_url')));
});

Breadcrumbs::register('sorteos_boleto_show', function ($breadcrumbs) {
    $breadcrumbs->parent('sorteos_boletos');
    $breadcrumbs->push(view()->shared('title_singular'));
});

//Order
Breadcrumbs::register('sorteos_orders', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push(trans('Sorteos::module.order.title'), url(config('sorteos.models.order.resource_url')));
});

Breadcrumbs::register('sorteos_order_create_edit', function ($breadcrumbs) {
    $breadcrumbs->parent('sorteos_orders');
    $breadcrumbs->push(view()->shared('title_singular'));
});

Breadcrumbs::register('sorteos_order_show', function ($breadcrumbs) {
    $breadcrumbs->parent('sorteos_orders');
    $breadcrumbs->push(view()->shared('title_singular'));
});

//Audit
Breadcrumbs::register('sorteos_audit', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push('Auditoría Sorteos', url('sorteos/audit'));
});

//Reports
Breadcrumbs::register('sorteos_reports', function ($breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push('Reportes', url('sorteos/reports'));
});

Breadcrumbs::register('sorteos_reports_sales', function ($breadcrumbs) {
    $breadcrumbs->parent('sorteos_reports');
    $breadcrumbs->push('Ventas por Período');
});

Breadcrumbs::register('sorteos_reports_buyers', function ($breadcrumbs) {
    $breadcrumbs->parent('sorteos_reports');
    $breadcrumbs->push('Compradores');
});

Breadcrumbs::register('sorteos_reports_payments', function ($breadcrumbs) {
    $breadcrumbs->parent('sorteos_reports');
    $breadcrumbs->push('Métodos de Pago');
});
