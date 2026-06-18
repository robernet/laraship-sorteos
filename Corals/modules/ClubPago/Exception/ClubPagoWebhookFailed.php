<?php

namespace Corals\Modules\ClubPago\Exception;

class ClubPagoWebhookFailed extends \RuntimeException
{
    public static function invalidSignature(): self
    {
        return new static(trans('ClubPago::exception.request_did_not_contain'));
    }

    public static function signingSecretNotSet(): self
    {
        return new static(trans('ClubPago::exception.clubpago_sign_secret_not_set'));
    }
}
