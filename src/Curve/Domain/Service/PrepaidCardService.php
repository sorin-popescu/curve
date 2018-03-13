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
    public function emitCard(string $currency): PrepaidCard
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
        $cardNumber = CardNumber::fromInt($cardNumber);

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
        $cardNumber = CardNumber::fromInt($cardNumber);

        /** @var PrepaidCard $prepaidCard */
        $prepaidCard = $this->repository->getByCardNumber($cardNumber);
        $prepaidCard->unlock();

        $this->repository->save($prepaidCard);
    }

    /**
     * @param int $cardNumber
     * @return PrepaidCard
     */
    public function displayBalance(int $cardNumber): PrepaidCard
    {
        $cardNumber = CardNumber::fromInt($cardNumber);

        $prepaidCard = $this->repository->getByCardNumber($cardNumber);

        return $prepaidCard;
    }

    /**
     * @param int $cardNumber
     * @param int $amount
     */
    public function makeDeposit(int $cardNumber, int $amount)
    {
        $cardNumber = CardNumber::fromInt($cardNumber);

        $money = new Money($amount, new Currency('GBP'));

        /** @var PrepaidCard $prepaidCard */
        $prepaidCard = $this->repository->getByCardNumber($cardNumber);
        $prepaidCard->load($money);

        $this->repository->save($prepaidCard);
    }

    /**
     * @param string $merchant
     * @param int $cardNumber
     * @param int $amount
     * @param string $date
     * @return int
     */
    public function makeAuthorizationRequest(string $merchant, int $cardNumber, int $amount, string $date): int
    {
        $cardNumber = CardNumber::fromInt($cardNumber);

        /** @var PrepaidCard $prepaidCard */
        $prepaidCard = $this->repository->getByCardNumber($cardNumber);
        $transactionId = $prepaidCard->autorizationRequest(
            $merchant,
            new Money($amount, new Currency('GBP')),
            new \DateTimeImmutable($date)
        );

        $this->repository->save($prepaidCard);

        return $transactionId;
    }

    /**
     * @param int $cardNumber
     * @param int $transactionId
     * @param int $amount
     */
    public function makeCapture(int $cardNumber, int $transactionId, int $amount)
    {
        $cardNumber = CardNumber::fromInt($cardNumber);

        $money = new Money($amount, new Currency('GBP'));

        /** @var PrepaidCard $prepaidCard */
        $prepaidCard = $this->repository->getByCardNumber($cardNumber);
        $prepaidCard->capture($transactionId, $money);

        $this->repository->save($prepaidCard);
    }

    /**
     * @param int $cardNumber
     * @param int $transactionId
     * @param int $amount
     */
    public function makeReverse(int $cardNumber, int $transactionId, int $amount)
    {
        $cardNumber = CardNumber::fromInt($cardNumber);

        $money = new Money($amount, new Currency('GBP'));

        /** @var PrepaidCard $prepaidCard */
        $prepaidCard = $this->repository->getByCardNumber($cardNumber);
        $prepaidCard->reverse($transactionId, $money);

        $this->repository->save($prepaidCard);
    }

    /**
     * @param int $cardNumber
     * @param int $transactionId
     * @param int $amount
     */
    public function makeRefund(int $cardNumber, int $transactionId, int $amount)
    {
        $cardNumber = CardNumber::fromInt($cardNumber);

        $money = new Money($amount, new Currency('GBP'));

        /** @var PrepaidCard $prepaidCard */
        $prepaidCard = $this->repository->getByCardNumber($cardNumber);
        $prepaidCard->refund($transactionId, $money);

        $this->repository->save($prepaidCard);
    }
}
