<?php

namespace Curve\Domain\ValueObject;

class Currency
{
    private $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function isNot(Currency $other)
    {
        return !$this->is($other);
    }

    public function is(Currency $other)
    {
        return $this->code === $other->code;
    }

    public function __toString(): string
    {
        return (string)$this->code;
    }
}
