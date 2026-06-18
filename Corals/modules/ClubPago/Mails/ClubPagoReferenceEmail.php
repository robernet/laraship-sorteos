<?php

namespace Corals\Modules\ClubPago\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClubPagoReferenceEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order_number, $amount, $payment_reference, $pay_format, $folio, $fecha, $body, $subject, $options;

    /**
     * ClubPagoReferenceEmail constructor.
     * @param $order_number
     * @param $folio
     * @param $fecha
     * @param $amount
     * @param $payment_reference
     * @param $pay_format
     * @param null $body
     * @param null $subject
     * @param array $options
     */
    public function __construct($order_number, $amount, $payment_reference, $pay_format, $folio, $fecha, $subject = null, $body = null, $options = [])
    {
        $this->order_number = $order_number;
        $this->folio = $folio;
        $this->fecha = $fecha;
        $this->amount = $amount;
        $this->payment_reference = $payment_reference;
        $this->pay_format = $pay_format;
        $this->body = $body;
        $this->subject = $subject;
        $this->options = $options;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)->view('ClubPago::mails.clubpago_reference');
    }
}
