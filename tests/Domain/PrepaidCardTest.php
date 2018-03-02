<?php

namespace Tests\Domain;

use Curve\Domain\Entity\PrepaidCard;
use Curve\Domain\Entity\Transaction;
use Curve\Domain\Exception\CardLockedException;
use Curve\Domain\Exception\InsufficientFundsException;
use Curve\Domain\Exception\TransactionException;
use Curve\Domain\ValueObject\Currency;
use Curve\Domain\ValueObject\Money;

class PrepaidCardTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_can_emit_a_card()
    {
        //Arrange
        $currency = new Currency('GBP');

        //Act
        $prepaidCard = PrepaidCard::emit($currency);

        //Assert
        self::assertInstanceOf(PrepaidCard::class, $prepaidCard);
    }

    /** @test */
    public function it_can_load_a_card()
    {
        //Arrange
        $currency = $currency = new Currency('GBP');
        $expected = new Money(100, $currency);
        $prepaidCard = PrepaidCard::emit(new Currency('GBP'));

        //Act
        $prepaidCard->load($expected);

        //Assert
        $this->assertEquals($prepaidCard->balance(), $expected->getAmount());
    }

    /** @test */
    public function it_does_not_allow_authorize_transaction_when_locked()
    {
        //Assert
        $this->expectException(CardLockedException::class);

        //Arrange
        $currency = $currency = new Currency('GBP');
        $amount = new Money(100, $currency);
        $prepaidCard = PrepaidCard::emit(new Currency('GBP'));
        $transaction = new Transaction('Coffee shop', $amount, new \DateTimeImmutable('2018-01-01'));

        //Act
        $prepaidCard->load($amount);
        $prepaidCard->lock();
        $prepaidCard->autorizationRequest($transaction);


    }

    /** @test */
    public function it_allows_to_capture_transaction_when_locked()
    {
        //Arrange
        $currency = $currency = new Currency('GBP');
        $amount = new Money(100, $currency);
        $prepaidCard = PrepaidCard::emit(new Currency('GBP'));
        $transaction = new Transaction('Coffee shop', $amount, new \DateTimeImmutable('2018-01-01'));

        //Act
        $prepaidCard->load($amount);
        $prepaidCard->autorizationRequest($transaction);
        $prepaidCard->lock();
        $prepaidCard->capture($transaction->getId(), $amount);

        //Assert
        $this->assertEquals(0, $prepaidCard->balance());
    }

    /** @test */
    public function it_allows_to_capture_transaction_multiple_times()
    {
        //Arrange
        $currency = $currency = new Currency('GBP');
        $amount = new Money(100, $currency);
        $amount2 = new Money(70, $currency);
        $amount3 = new Money(20, $currency);
        $prepaidCard = PrepaidCard::emit(new Currency('GBP'));
        $transaction = new Transaction('Coffee shop', $amount, new \DateTimeImmutable('2018-01-01'));

        //Act
        $prepaidCard->load($amount);
        $prepaidCard->autorizationRequest($transaction);

        //Assert
        $this->assertEquals(0, $prepaidCard->availableBalance());
        $this->assertEquals(100, $prepaidCard->balance());

        //Act
        $prepaidCard->capture($transaction->getId(), $amount2);
        $prepaidCard->capture($transaction->getId(), $amount3);

        //Assert
        $this->assertEquals(10, $prepaidCard->balance());
    }

    /** @test */
    public function it_does_not_allow_to_capture_transaction_more_than_authorized()
    {
        //Assert
        $this->expectException(InsufficientFundsException::class);

        //Arrange
        $currency = $currency = new Currency('GBP');
        $amount = new Money(100, $currency);
        $prepaidCard = PrepaidCard::emit(new Currency('GBP'));
        $transaction = new Transaction('Coffee shop', $amount, new \DateTimeImmutable('2018-01-01'));

        //Act
        $prepaidCard->load($amount);
        $prepaidCard->autorizationRequest($transaction);
        $prepaidCard->capture($transaction->getId(), $amount);
        $prepaidCard->capture($transaction->getId(), $amount);
    }

    /** @test */
    public function it_does_not_allow_to_reverse_captured_transaction()
    {
        //Assert
        $this->expectException(TransactionException::class);

        //Arrange
        $currency = $currency = new Currency('GBP');
        $amount = new Money(100, $currency);
        $prepaidCard = PrepaidCard::emit(new Currency('GBP'));
        $transaction = new Transaction('Coffee shop', $amount, new \DateTimeImmutable('2018-01-01'));

        //Act
        $prepaidCard->load($amount);
        $prepaidCard->autorizationRequest($transaction);
        $prepaidCard->capture($transaction->getId(), $amount);
        $prepaidCard->reverse($transaction->getId(), $amount);
    }

    /** @test */
    public function it_does_not_allow_to_reverse_a_value_greater_than_authorized()
    {
        //Assert
        $this->expectException(TransactionException::class);

        //Arrange
        $currency = $currency = new Currency('GBP');
        $amount = new Money(100, $currency);
        $prepaidCard = PrepaidCard::emit(new Currency('GBP'));
        $transaction = new Transaction('Coffee shop', $amount, new \DateTimeImmutable('2018-01-01'));

        //Act
        $prepaidCard->load($amount);
        $prepaidCard->autorizationRequest($transaction);
        $prepaidCard->reverse($transaction->getId(), $amount);
        $prepaidCard->reverse($transaction->getId(), $amount);
    }

    /** @test */
    public function it_does_not_allow_to_reverse_without_an_authorized_transaction()
    {
        //Assert
        $this->expectException(TransactionException::class);

        //Arrange
        $currency = $currency = new Currency('GBP');
        $amount = new Money(100, $currency);
        $prepaidCard = PrepaidCard::emit(new Currency('GBP'));
        $transaction = new Transaction('Coffee shop', $amount, new \DateTimeImmutable('2018-01-01'));

        //Act
        $prepaidCard->load($amount);
        $prepaidCard->reverse($transaction->getId(), $amount);
    }

    /** @test */
    public function it_does_allows_to_reverse_transaction_multiple_times()
    {
        //Arrange
        $currency = $currency = new Currency('GBP');
        $amount = new Money(100, $currency);
        $amount2 = new Money(20, $currency);

        $prepaidCard = PrepaidCard::emit(new Currency('GBP'));
        $transaction = new Transaction('Coffee shop', $amount, new \DateTimeImmutable('2018-01-01'));

        //Act
        $prepaidCard->load($amount);
        $prepaidCard->autorizationRequest($transaction);

        //Assert
        $this->assertEquals(0, $prepaidCard->availableBalance());

        //Act
        $prepaidCard->reverse($transaction->getId(), $amount2);
        $prepaidCard->reverse($transaction->getId(), $amount2);

        //Assert
        $this->assertEquals(40, $prepaidCard->availableBalance());
        $this->assertEquals(100, $prepaidCard->balance());
    }

    /** @test */
    public function it_does_not_allow_to_refund_a_value_greater_than_captured()
    {
        //Assert
        $this->expectException(TransactionException::class);

        //Arrange
        $currency = $currency = new Currency('GBP');
        $amount = new Money(100, $currency);
        $prepaidCard = PrepaidCard::emit(new Currency('GBP'));
        $transaction = new Transaction('Coffee shop', $amount, new \DateTimeImmutable('2018-01-01'));

        //Act
        $prepaidCard->load($amount);
        $prepaidCard->autorizationRequest($transaction);
        $prepaidCard->capture($transaction->getId(), $amount);
        $prepaidCard->refund($transaction->getId(), $amount);
        $prepaidCard->refund($transaction->getId(), $amount);
    }

    /** @test */
    public function it_does_allows_to_refund_a_transaction_multiple_times()
    {
        //Arrange
        $currency = $currency = new Currency('GBP');
        $amount = new Money(100, $currency);
        $amount2 = new Money(20, $currency);

        $prepaidCard = PrepaidCard::emit(new Currency('GBP'));
        $transaction = new Transaction('Coffee shop', $amount, new \DateTimeImmutable('2018-01-01'));

        //Act
        $prepaidCard->load($amount);
        $prepaidCard->autorizationRequest($transaction);

        //Assert
        $this->assertEquals(0, $prepaidCard->availableBalance());

        //Act
        $prepaidCard->capture($transaction->getId(), $amount2);
        $prepaidCard->capture($transaction->getId(), $amount2);

        //Assert
        $this->assertEquals(0, $prepaidCard->availableBalance());
        $this->assertEquals(60, $prepaidCard->balance());

        //Act
        $prepaidCard->refund($transaction->getId(), $amount2);
        $prepaidCard->refund($transaction->getId(), $amount2);

        //Assert
        $this->assertEquals(40, $prepaidCard->availableBalance());
        $this->assertEquals(100, $prepaidCard->balance());
    }
}
