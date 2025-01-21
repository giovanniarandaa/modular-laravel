<?php

namespace Modules\Order\Exceptions;

use RuntimeException;

class PaymentFailException extends RuntimeException
{
    public static function dueToInvalidToken(): self
    {
        return new self('The given token is not valid.');
    }
}
