<?php

namespace Curve\Domain\Entity;

use Curve\Domain\Exception\CardLockedException;
use Curve\Domain\Exception\InsufficientFundsException;
use Curve\Domain\Exception\TransactionException;
use Curve\Domain\ValueObject\CardNumber;
use Curve\Domain\ValueObject\CardStatus;
use Curve\Domain\ValueObject\Currency;
use Curve\Domain\ValueObject\Money;
use DateTimeImmutable;

class PrepaidCard
{
    /** @var CardNumber */
    private $number;

    /** @var CardStatus */
    private $status;

    /** @var Money */
    private $balance;

    /** @var Money */
    private $availableBalance;

    /** @var Currency */
    private $currency;

    private $authorizationRequests;

    /**
     * PrepaidCard constructor.
     * @param Currency $currency
     */
    private function __construct(Currency $currency)
    {
        $this->number = CardNumber::generate();
        $this->status = CardStatus::active();
        $this->balance = new Money(0, $currency);
        $this->availableBalance = new Money(0, $currency);
        $this->currency = $currency;
        $this->authorizationRequests = [];
    }

    /**
     * @param Currency $currency
     * @return PrepaidCard
     */
    public static function emit(Currency $currency): PrepaidCard
    {
        return new static($currency);
    }

    public function lock()
    {
        if ($this->status->isLocked()) {
            throw new CardLockedException();
        }

        $this->status = CardStatus::locked();
    }

    public function unlock()
    {
        if ($this->status->isActive()) {
            throw new CardLockedException('test');
        }

        $this->status = CardStatus::active();
    }

    /**
     * @param Money $money
     */
    public function load(Money $money)
    {
        if ($this->status->isLocked()) {
            throw new CardLockedException();
        }

        $this->balance = $this->balance->add($money);
        $this->availableBalance = $this->availableBalance->add($money);
    }

    /**
     * @param string $merchant
     * @param Money $amount
     * @param DateTimeImmutable $date
     * @return int
     */
    public function autorizationRequest(string $merchant, Money $amount, DateTimeImmutable $date)
    {
        if ($this->status->isLocked()) {
            throw new CardLockedException();
        }

        $transaction = new Transaction($merchant, $amount, $date);
        if ($transaction->amount()->isMoreThan($this->availableBalance)) {
            $transaction->decline();
            throw new InsufficientFundsException();
        }

        $this->authorizationRequests[$transaction->getId()] = $transaction;
        $this->availableBalance = $this->availableBalance->deduct($transaction->amount());
        $transaction->authorize();

        return $transaction->getId();
    }

    /**
     * @param int $authorizationId
     * @param Money $money
     */
    public function capture(int $authorizationId, Money $money)
    {
        $this->authorizationRequests[$authorizationId]->capture($money);
        $this->balance = $this->balance->deduct($money);
    }

    /**
     * @param int $authorizationId
     * @param Money $money
     */
    public function reverse(int $authorizationId, Money $money)
    {
        if (!isset($this->authorizationRequests[$authorizationId])) {
            throw new TransactionException();
        }
        /** @var Transaction $transaction */
        $transaction =  $this->authorizationRequests[$authorizationId];
        if ($transaction->isCaptured()) {
            throw new TransactionException();
        }
        $transaction->reverse($money);
        $this->availableBalance = $this->availableBalance->add($money);
    }

    /**
     * @param int $authorizationId
     * @param Money $money
     */
    public function refund(int $authorizationId, Money $money)
    {
        /** @var Transaction $transaction */
        $transaction =  $this->authorizationRequests[$authorizationId];
        $transaction->refund($money);
        $this->availableBalance = $this->availableBalance->add($money);
        $this->balance = $this->balance->add($money);
    }

    /**
     * @return int
     */
    public function balance(): int
    {
        return $this->balance->getAmount();
    }

    /**
     * @return int
     */
    public function availableBalance(): int
    {
        return $this->availableBalance->getAmount();
    }

    public function getNumber()
    {
        return $this->number->getNumber();
    }
}
