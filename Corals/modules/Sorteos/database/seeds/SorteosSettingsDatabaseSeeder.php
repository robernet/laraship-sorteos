<?php

namespace Corals\Modules\Sorteos\database\seeds;

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SorteosSettingsDatabaseSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        \DB::table('settings')->insert([
            [
                'code'       => 'sorteos_setting',
                'type'       => 'TEXT',
                'category'   => 'Sorteos',
                'label'      => 'Sorteos setting',
                'value'      => 'sorteos',
                'editable'   => 1,
                'hidden'     => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code'       => 'clubpago_api_url',
                'type'       => 'TEXT',
                'category'   => 'ClubPago',
                'label'      => 'ClubPago API URL',
                'value'      => 'https://api.clubpago.com.mx',
                'editable'   => 1,
                'hidden'     => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code'       => 'clubpago_merchant_id',
                'type'       => 'TEXT',
                'category'   => 'ClubPago',
                'label'      => 'ClubPago Merchant ID',
                'value'      => '',
                'editable'   => 1,
                'hidden'     => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code'       => 'clubpago_api_key',
                'type'       => 'TEXT',
                'category'   => 'ClubPago',
                'label'      => 'ClubPago API Key',
                'value'      => '',
                'editable'   => 1,
                'hidden'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code'       => 'clubpago_secret_key',
                'type'       => 'TEXT',
                'category'   => 'ClubPago',
                'label'      => 'ClubPago Secret Key (webhook)',
                'value'      => '',
                'editable'   => 1,
                'hidden'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code'       => 'brevo_api_key',
                'type'       => 'TEXT',
                'category'   => 'Brevo',
                'label'      => 'Brevo API Key',
                'value'      => '',
                'editable'   => 1,
                'hidden'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code'       => 'brevo_from_email',
                'type'       => 'TEXT',
                'category'   => 'Brevo',
                'label'      => 'Brevo From Email',
                'value'      => 'noreply@sorteos.itson.mx',
                'editable'   => 1,
                'hidden'     => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code'       => 'brevo_from_name',
                'type'       => 'TEXT',
                'category'   => 'Brevo',
                'label'      => 'Brevo From Name',
                'value'      => 'Sorteos ITSON',
                'editable'   => 1,
                'hidden'     => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
