<?php

namespace Curve\Domain\ValueObject;

class CardNumber
{
    /** @var int */
    private $number;

    private function __construct(int $number)
    {
        $this->number = $number;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return (string) $this->number;
    }

    /**
     * @return CardNumber
     */
    public static function generate()
    {
        return new self(mt_rand(100000, 999999));
    }

    /**
     * @param string $number
     * @return CardNumber
     */
    public static function fromString(string $number)
    {
        return new self($number);
    }
}
