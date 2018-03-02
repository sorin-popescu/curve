<?php

namespace Curve\Domain\Exception;

use Throwable;

class TransactionException extends \RuntimeException
{
    const MESSAGE_CAPTURED = 'Transaction Captured';

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
