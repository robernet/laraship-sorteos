<?php

return [
    'models' => [
        'sorteo'  => [
            'presenter'    => \Corals\Modules\Sorteos\Transformers\SorteoPresenter::class,
            'resource_url' => 'sorteos',
        ],
        'cartera' => [
            'presenter'    => \Corals\Modules\Sorteos\Transformers\CarteraPresenter::class,
            'resource_url' => 'sorteos/carteras',
        ],
        'boleto'  => [
            'presenter'    => \Corals\Modules\Sorteos\Transformers\BoletoPresenter::class,
            'resource_url' => 'sorteos/boletos',
        ],
        'order'   => [
            'presenter'    => \Corals\Modules\Sorteos\Transformers\OrderPresenter::class,
            'resource_url' => 'sorteos/orders',
        ],
    ],
];
