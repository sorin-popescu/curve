<?php

namespace Curve\Domain\ValueObject;

class SortCode
{
    /** @var string */
    private $sortCode;

    /**
     * SortCode constructor.
     * @param string $sortCode
     */
    private function __construct(string $sortCode)
    {
        $this->sortCode = $sortCode;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->sortCode;
    }

    /**
     * @param string $sortCode
     * @return SortCode
     */
    public static function fromString(string $sortCode): SortCode
    {
        return new self($sortCode);
    }
}
