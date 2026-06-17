<?php

namespace Corals\Modules\Sorteos\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Barryvdh\DomPDF\Facade\Pdf;
use Corals\Modules\Sorteos\Models\Boleto;
use Corals\Modules\Sorteos\Models\Order;

class BoletoDigitalService
{
    /**
     * Return (and persist if needed) the anti-fraud validation token for a boleto.
     */
    public function getOrCreateToken(Boleto $boleto): string
    {
        $props = $boleto->properties ?? [];

        if (!empty($props['token'])) {
            return $props['token'];
        }

        $token = hash_hmac('sha256', $boleto->id . '|' . $boleto->digital_number, config('app.key'));
        $props['token'] = $token;
        $boleto->update(['properties' => $props]);

        return $token;
    }

    public function validationUrl(Boleto $boleto): string
    {
        return route('sorteos.boleto.validate', $this->getOrCreateToken($boleto));
    }

    /**
     * Generate QR code as an inline SVG string.
     */
    public function qrSvg(Boleto $boleto, int $size = 200): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size),
            new SvgImageBackEnd()
        );

        return (new Writer($renderer))->writeString($this->validationUrl($boleto));
    }

    /**
     * Render a single boleto as PDF binary.
     */
    public function pdfContent(Boleto $boleto): string
    {
        $boleto->loadMissing(['sorteo', 'cartera']);
        $qrSvg = $this->qrSvg($boleto);

        $pdf = Pdf::loadView('Sorteos::boletos.pdf_ticket', compact('boleto', 'qrSvg'))
            ->setPaper('letter', 'portrait');

        return $pdf->output();
    }

    /**
     * Generate PDFs for every boleto in an order and return them keyed by digital_number.
     *
     * @return array<int, string>  [digital_number => pdf_binary]
     */
    public function generateOrderPdfs(Order $order): array
    {
        $order->loadMissing(['items.boleto.sorteo', 'items.boleto.cartera']);
        $pdfs = [];

        foreach ($order->items as $item) {
            if ($item->boleto) {
                $pdfs[$item->boleto->digital_number] = $this->pdfContent($item->boleto);
            }
        }

        return $pdfs;
    }

    /**
     * Validate a token and return the matching Boleto or null.
     */
    public function findByToken(string $token): ?Boleto
    {
        return Boleto::where('properties->token', $token)->first();
    }
}
