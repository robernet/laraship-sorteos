<?php

namespace Corals\Modules\Sorteos\database\seeds;

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SorteosSettingsDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        \DB::table('settings')->insert([

            // ── Sorteos operacional ──────────────────────────────────────────
            [
                'code'      => 'sorteos_reservation_timeout_minutes',
                'type'      => 'TEXT',
                'category'  => 'Sorteos',
                'label'     => 'Tiempo de reserva (minutos)',
                'value'     => '30',
                'editable'  => 1,
                'is_public' => 0,
                'hidden'    => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code'      => 'sorteos_max_tickets_per_order',
                'type'      => 'TEXT',
                'category'  => 'Sorteos',
                'label'     => 'Máximo de boletos por orden',
                'value'     => '10',
                'editable'  => 1,
                'is_public' => 0,
                'hidden'    => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ── ClubPago ─────────────────────────────────────────────────────
            [
                'code'      => 'clubpago_api_url',
                'type'      => 'TEXT',
                'category'  => 'ClubPago',
                'label'     => 'ClubPago API URL',
                'value'     => 'https://api.clubpago.com.mx',
                'editable'  => 1,
                'is_public' => 0,
                'hidden'    => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code'      => 'clubpago_merchant_id',
                'type'      => 'TEXT',
                'category'  => 'ClubPago',
                'label'     => 'ClubPago Merchant ID',
                'value'     => '',
                'editable'  => 1,
                'is_public' => 0,
                'hidden'    => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code'      => 'clubpago_api_key',
                'type'      => 'TEXT',
                'category'  => 'ClubPago',
                'label'     => 'ClubPago API Key',
                'value'     => '',
                'editable'  => 1,
                'is_public' => 0,
                'hidden'    => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code'      => 'clubpago_secret_key',
                'type'      => 'TEXT',
                'category'  => 'ClubPago',
                'label'     => 'ClubPago Secret Key (webhook)',
                'value'     => '',
                'editable'  => 1,
                'is_public' => 0,
                'hidden'    => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ── Brevo (Sendinblue) ───────────────────────────────────────────
            [
                'code'      => 'brevo_api_key',
                'type'      => 'TEXT',
                'category'  => 'Brevo',
                'label'     => 'Brevo API Key',
                'value'     => '',
                'editable'  => 1,
                'is_public' => 0,
                'hidden'    => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code'      => 'brevo_from_email',
                'type'      => 'TEXT',
                'category'  => 'Brevo',
                'label'     => 'Correo remitente',
                'value'     => 'noreply@sorteos.itson.mx',
                'editable'  => 1,
                'is_public' => 0,
                'hidden'    => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code'      => 'brevo_from_name',
                'type'      => 'TEXT',
                'category'  => 'Brevo',
                'label'     => 'Nombre remitente',
                'value'     => 'Sorteos ITSON',
                'editable'  => 1,
                'is_public' => 0,
                'hidden'    => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code'      => 'brevo_ticket_template_id',
                'type'      => 'TEXT',
                'category'  => 'Brevo',
                'label'     => 'ID de plantilla — confirmación con boleto PDF',
                'value'     => '',
                'editable'  => 1,
                'is_public' => 0,
                'hidden'    => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
