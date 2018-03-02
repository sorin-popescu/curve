<?php

namespace Curve\Domain\Exception;

use Throwable;

class CardLockedException extends \RuntimeException
{
    const MESSAGE = 'The account is closed.';

    public function __construct($message = self::MESSAGE, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
