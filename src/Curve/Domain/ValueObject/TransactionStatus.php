<?php

namespace Curve\Domain\ValueObject;

class TransactionStatus
{
    const AUTHORIZED_STATUS = 'authorized';
    const CAPTURED_STATUS = 'captured';
    const PENDING_STATUS = 'pending';
    const DECLINE_STATUS = 'declined';

    /** @var string  */
    private $status;

    /**
     * CardStatus constructor.
     * @param string $status
     */
    private function __construct(string $status)
    {
        $this->status = $status;
    }

    /**
     * @return TransactionStatus
     */
    public static function authorized(): TransactionStatus
    {
        return new self(self::AUTHORIZED_STATUS);
    }

    /**
     * @return TransactionStatus
     */
    public static function captured(): TransactionStatus
    {
        return new self(self::CAPTURED_STATUS);
    }

    /**
     * @return TransactionStatus
     */
    public static function pending(): TransactionStatus
    {
        return new self(self::PENDING_STATUS);
    }

    /**
     * @return TransactionStatus
     */
    public static function declined(): TransactionStatus
    {
        return new self(self::DECLINE_STATUS);
    }

    /**
     * @return bool
     */
    public function isCaptured()
    {
        return $this->status === self::CAPTURED_STATUS;
    }
}
