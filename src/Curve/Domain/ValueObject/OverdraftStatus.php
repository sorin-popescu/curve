<?php

namespace Curve\Domain\ValueObject;

class OverdraftStatus
{
    const PENDING_APPROVAL = 'pending_approval';
    const APPROVED = 'approved';
    const DENIED = 'denied';

    /** @var string  */
    private $status;

    /**
     * OverdraftStatus constructor.
     * @param string $status
     */
    private function __construct(string $status)
    {
        $this->status = $status;
    }

    /**
     * @return OverdraftStatus
     */
    public static function pendingApproval(): OverdraftStatus
    {
        return new self(self::PENDING_APPROVAL);
    }

    /**
     * @return OverdraftStatus
     */
    public static function approved(): OverdraftStatus
    {
        return new self(self::APPROVED);
    }

    /**
     * @return OverdraftStatus
     */
    public static function denied(): OverdraftStatus
    {
        return new self(self::DENIED);
    }

    /**
     * @return bool
     */
    public function isApproved()
    {
        return $this->status === self::APPROVED;
    }

    /**
     * @return bool
     */
    public function isDenied()
    {
        return $this->status === self::DENIED;
    }
}
