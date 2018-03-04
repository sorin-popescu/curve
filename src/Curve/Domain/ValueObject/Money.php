<?php

namespace Curve\Domain\ValueObject;

use Curve\Domain\Exception\NegativeFundsException;

class Money
{
    /** @var  float */
    private $amount;

    /** @var Currency */
    private $currency;

    /**
     * Money constructor.
     * @param int $amount
     * @param Currency $currency
     */
    public function __construct(int $amount, Currency $currency)
    {
        if ($amount < 0) {
            throw new NegativeFundsException();
        }
        $this->amount = $amount;
        $this->currency = $currency;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @return Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * @param Money $other
     * @return Money
     */
    public function add(Money $other)
    {
        if ($other->getCurrency()->isNot($this->getCurrency())) {
            throw new \InvalidArgumentException('Cannot add because currencies do not match');
        }
        return $this->newMoney($this->amount + $other->getAmount());
    }

    /**
     * @param Money $other
     * @return Money
     */
    public function deduct(Money $other): Money
    {
        if ($other->getCurrency()->isNot($this->getCurrency())) {
            throw new \InvalidArgumentException('Cannot add because currencies do not match');
        }
        return $this->newMoney($this->amount - $other->getAmount());
    }

    /**
     * @param Money $other
     * @return bool
     */
    public function isMoreThan(Money $other): bool
    {
        return $this->compareWith($other) === 1;
    }

    /**
     * @param Money $other
     * @return bool
     */
    public function isLessThan(Money $other): bool
    {
        return $this->compareWith($other) === -1;
    }

    /**
     * @param Money $other
     * @return bool
     */
    public function isEqual(Money $other): bool
    {
        return $this->compareWith($other) === 0;
    }

    /**
     * @param Money $other
     * @return int
     */
    private function compareWith(Money $other): int
    {
        if ($other->getCurrency()->isNot($this->getCurrency())) {
            throw new \InvalidArgumentException('Cannot compare because currencies do not match');
        }
        return $this->getAmount() <=> $other->getAmount();
    }

    /**
     * @param $amount
     * @return Money
     */
    private function newMoney($amount): Money
    {
        return new static($amount, $this->currency);
    }
}
