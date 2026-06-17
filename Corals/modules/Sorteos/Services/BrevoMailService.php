<?php

namespace Corals\Modules\Sorteos\Services;

use Corals\Modules\Sorteos\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class BrevoMailService
{
    private const API_URL = 'https://api.brevo.com/v3/smtp/email';

    private string $apiKey;
    private string $fromEmail;
    private string $fromName;

    public function __construct()
    {
        $this->apiKey    = \Settings::get('brevo_api_key', '');
        $this->fromEmail = \Settings::get('brevo_from_email', config('mail.from.address', 'noreply@sorteos.itson.mx'));
        $this->fromName  = \Settings::get('brevo_from_name', config('mail.from.name', 'Sorteos ITSON'));
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Send the order confirmation email with PDF ticket attachments to the buyer.
     *
     * @return array{sent: bool, message_id: string|null, error: string|null}
     */
    public function sendOrderConfirmation(Order $order, BoletoDigitalService $boletoDigital): array
    {
        $order->loadMissing(['sorteo', 'items.boleto.sorteo', 'items.boleto.cartera']);

        $attachments = $this->buildAttachments($order, $boletoDigital);
        $htmlContent = View::make('Sorteos::emails.order_confirmation', compact('order'))->render();

        $subject = 'Tus boletos — ' . ($order->sorteo?->name ?? 'Sorteos ITSON') . ' #' . $order->id;

        $payload = [
            'sender'      => ['email' => $this->fromEmail, 'name' => $this->fromName],
            'to'          => [['email' => $order->buyer_email, 'name' => $order->buyer_name]],
            'subject'     => $subject,
            'htmlContent' => $htmlContent,
            'attachment'  => $attachments,
        ];

        $response = Http::withHeaders([
            'api-key'      => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post(self::API_URL, $payload);

        if ($response->failed()) {
            $error = $response->json('message', 'Error al enviar el correo');
            Log::error('Brevo send failed', ['order_id' => $order->id, 'status' => $response->status(), 'body' => $response->body()]);
            return ['sent' => false, 'message_id' => null, 'error' => $error];
        }

        $messageId = $response->json('messageId');
        $this->recordSend($order, $messageId);

        return ['sent' => true, 'message_id' => $messageId, 'error' => null];
    }

    /**
     * Build the attachments array for the Brevo API (base64-encoded PDFs).
     */
    private function buildAttachments(Order $order, BoletoDigitalService $boletoDigital): array
    {
        $attachments = [];

        foreach ($order->items as $item) {
            if (!$item->boleto) {
                continue;
            }

            try {
                $pdf      = $boletoDigital->pdfContent($item->boleto);
                $filename = 'boleto-' . str_pad($item->boleto->digital_number, 5, '0', STR_PAD_LEFT) . '.pdf';

                $attachments[] = [
                    'name'    => $filename,
                    'content' => base64_encode($pdf),
                ];
            } catch (\Exception $e) {
                Log::warning('Failed to generate PDF for boleto ' . $item->boleto->id, ['error' => $e->getMessage()]);
            }
        }

        return $attachments;
    }

    /**
     * Append a send record to the order's properties for tracking purposes.
     */
    private function recordSend(Order $order, ?string $messageId): void
    {
        $props = $order->properties ?? [];
        $props['email_sends'][] = [
            'sent_at'    => now()->toISOString(),
            'message_id' => $messageId,
        ];
        $order->update(['properties' => $props]);
    }

    /**
     * Return the list of send records stored on the order.
     */
    public function getSendHistory(Order $order): array
    {
        return $order->properties['email_sends'] ?? [];
    }
}
