<?php

namespace Curve\Domain\ValueObject;

class CardStatus
{
    const ACTIVE_STATUS = 'active';
    const LOCKED_STATUS = 'locked';

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
     * @return CardStatus
     */
    public static function active(): CardStatus
    {
        return new self(self::ACTIVE_STATUS);
    }

    /**
     * @return CardStatus
     */
    public static function locked(): CardStatus
    {
        return new self(self::LOCKED_STATUS);
    }

    /**
     * @return bool
     */
    public function isLocked()
    {
        return $this->status === self::LOCKED_STATUS;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->status === self::ACTIVE_STATUS;
    }
}
