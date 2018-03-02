<?php

namespace Curve\Domain\Exception;

use Throwable;

class NegativeFundsException extends \RuntimeException
{
    const MESSAGE = "You can't have negative funds.";

    public function __construct($message = self::MESSAGE, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
