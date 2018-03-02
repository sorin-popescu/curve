<?php

namespace Curve\Domain\Exception;

use Throwable;

class InsufficientFundsException extends \RuntimeException
{
    const MESSAGE = 'The account has insufficient funds.';

    public function __construct($message = self::MESSAGE, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
