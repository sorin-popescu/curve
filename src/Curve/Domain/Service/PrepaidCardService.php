<?php

namespace Curve\Domain\Service;

use Curve\Domain\Entity\PrepaidCard;
use Curve\Domain\Entity\Transaction;
use Curve\Domain\Repository\PrepaidCardRepository;
use Curve\Domain\ValueObject\CardNumber;
use Curve\Domain\ValueObject\Currency;
use Curve\Domain\ValueObject\Money;
use Curve\Domain\ValueObject\SortCode;

class PrepaidCardService
{
    private $repository;

    /**
     * CurrentAccountService constructor.
     * @param PrepaidCardRepository $repository
     */
    public function __construct(PrepaidCardRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $currency
     * @return PrepaidCard
     */
    public function openAccount(string $currency): PrepaidCard
    {
        $prepaidCard = PrepaidCard::emit(new Currency($currency));
        $this->repository->save($prepaidCard);

        return $prepaidCard;
    }

    /**
     * @param int $cardNumber
     */
    public function lockCard(int $cardNumber)
    {
        $cardNumber = CardNumber::fromString($cardNumber);

        /** @var PrepaidCard $prepaidCard */
        $prepaidCard = $this->repository->getByCardNumber($cardNumber);
        $prepaidCard->lock();

        $this->repository->save($prepaidCard);
    }

    /**
     * @param int $cardNumber
     */
    public function unlockCard(int $cardNumber)
    {
        $cardNumber = CardNumber::fromString($cardNumber);

        /** @var PrepaidCard $prepaidCard */
        $prepaidCard = $this->repository->getByCardNumber($cardNumber);
        $prepaidCard->unlock();

        $this->repository->save($prepaidCard);
    }

    /**
     * @param int $cardNumber
     */
    public function transactions(int $cardNumber)
    {
        $cardNumber = CardNumber::fromString($cardNumber);

        /** @var PrepaidCard $prepaidCard */
        $prepaidCard = $this->repository->getByCardNumber($cardNumber);
        $prepaidCard->close();

        $this->repository->save($prepaidCard);
    }

    /**
     * @param int $cardNumber
     * @return PrepaidCard
     */
    public function displayBalance(int $cardNumber): PrepaidCard
    {
        $cardNumber = CardNumber::fromString($cardNumber);

        $prepaidCard = $this->repository->getByCardNumber($cardNumber);

        return $prepaidCard;
    }

    /**
     * @param int $cardNumber
     * @param float $amount
     */
    public function makeDeposit(int $cardNumber, float $amount)
    {
        $cardNumber = CardNumber::fromString($cardNumber);

        $money = new Money($amount, new Currency('GBP'));

        /** @var PrepaidCard $prepaidCard */
        $prepaidCard = $this->repository->getByCardNumber($cardNumber);
        $prepaidCard->load($money);

        $this->repository->save($prepaidCard);
    }

    /**
     * @param string $merchant
     * @param int $cardNumber
     * @param float $amount
     * @param string $date
     * @return Transaction
     */
    public function makeAuthorizationRequest(string $merchant, int $cardNumber, float $amount, string $date)
    {
        $cardNumber = CardNumber::fromString($cardNumber);

        $transaction = new Transaction(
            $merchant,
            new Money($amount, new Currency('GBP')),
            new \DateTimeImmutable($date)
        );

        /** @var PrepaidCard $prepaidCard */
        $prepaidCard = $this->repository->getByCardNumber($cardNumber);
        $prepaidCard->autorizationRequest($transaction);

        $this->repository->save($prepaidCard);

        return $transaction;
    }

    /**
     * @param int $cardNumber
     * @param int $transactionId
     * @param float $amount
     */
    public function makeCapture(int $cardNumber, int $transactionId, float $amount)
    {
        $cardNumber = CardNumber::fromString($cardNumber);

        $money = new Money($amount, new Currency('GBP'));

        /** @var PrepaidCard $prepaidCard */
        $prepaidCard = $this->repository->getByCardNumber($cardNumber);
        $prepaidCard->capture($transactionId, $money);

        $this->repository->save($prepaidCard);
    }

    /**
     * @param int $cardNumber
     * @param int $transactionId
     * @param float $amount
     */
    public function makeReverse(int $cardNumber, int $transactionId, float $amount)
    {
        $cardNumber = CardNumber::fromString($cardNumber);

        $money = new Money($amount, new Currency('GBP'));

        /** @var PrepaidCard $prepaidCard */
        $prepaidCard = $this->repository->getByCardNumber($cardNumber);
        $prepaidCard->reverse($transactionId, $money);

        $this->repository->save($prepaidCard);
    }

    /**
     * @param int $cardNumber
     * @param int $transactionId
     * @param float $amount
     */
    public function makeRefund(int $cardNumber, int $transactionId, float $amount)
    {
        $cardNumber = CardNumber::fromString($cardNumber);

        $money = new Money($amount, new Currency('GBP'));

        /** @var PrepaidCard $prepaidCard */
        $prepaidCard = $this->repository->getByCardNumber($cardNumber);
        $prepaidCard->refund($transactionId, $money);

        $this->repository->save($prepaidCard);
    }
}
