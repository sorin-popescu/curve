<?php

namespace Curve\Domain\Entity;

use Curve\Domain\Exception\InsufficientFundsException;
use Curve\Domain\Exception\TransactionException;
use Curve\Domain\ValueObject\Currency;
use Curve\Domain\ValueObject\Money;
use Curve\Domain\ValueObject\TransactionStatus;
use DateTimeImmutable;

class Transaction
{
    /** @var int */
    private $id;

    /** @var string */
    private $merchant;

    /** @var Money */
    private $amount;

    /** @var Money */
    private $capturedAmount;

    /** @var DateTimeImmutable */
    private $date;

    /** @var TransactionStatus */
    private $status;

    /**
     * Transaction constructor.
     * @param $merchant
     * @param $amount
     * @param $date
     */
    public function __construct(string $merchant, Money $amount, DateTimeImmutable $date)
    {
        $this->id = rand(1000, 9999);
        $this->merchant = $merchant;
        $this->amount = $amount;
        $this->capturedAmount = new Money(0, new Currency('GBP'));
        $this->date = $date;
        $this->status = TransactionStatus::pending();
    }

    /**
     * @return Money
     */
    public function amount(): Money
    {
        return $this->amount;
    }

    /**
     * @param Money $money
     */
    public function capture(Money $money)
    {
        if ($this->amount->deduct($this->capturedAmount)->isLessThan($money)) {
            throw new InsufficientFundsException();
        }

        $this->capturedAmount = $this->capturedAmount->add($money);

        if ($this->amount->isEqual($this->capturedAmount)) {
            $this->status = TransactionStatus::captured();
        }
    }

    /**
     * @param Money $money
     */
    public function reverse(Money $money)
    {
        if ($this->amount->deduct($this->capturedAmount)->isLessThan($money)) {
            throw new TransactionException(TransactionException::MESSAGE_CAPTURED);
        }

        $this->amount = $this->amount->deduct($money);
    }

    /**
     * @param Money $money
     */
    public function refund(Money $money)
    {
        if ($this->capturedAmount->isLessThan($money)) {
            throw new TransactionException("Can't refund");
        }

        $this->capturedAmount = $this->capturedAmount->deduct($money);
    }

    public function authorize()
    {
        $this->status = TransactionStatus::authorized();
    }

    public function decline()
    {
        $this->status = TransactionStatus::declined();
    }

    public function isCaptured(): bool
    {
        return $this->status->isCaptured();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
