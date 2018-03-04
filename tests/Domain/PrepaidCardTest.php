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
    public function it_can_get_a_card_number()
    {
        //Arrange
        $currency = new Currency('GBP');

        //Act
        $prepaidCard = PrepaidCard::emit($currency);
        $cardNumber = $prepaidCard->getNumber();

        //Assert
        self::assertNotNull($cardNumber);
    }

    /** @test */
    public function it_can_not_load_a_card_when_locked()
    {
        //Assert
        $this->expectException(CardLockedException::class);

        //Arrange
        $currency = new Currency('GBP');
        $expected = new Money(100, $currency);
        $prepaidCard = PrepaidCard::emit($currency);

        //Act
        $prepaidCard->lock();
        $prepaidCard->load($expected);
    }

    /** @test */
    public function it_can_load_a_card()
    {
        //Arrange
        $currency = new Currency('GBP');
        $expected = new Money(100, $currency);
        $prepaidCard = PrepaidCard::emit($currency);

        //Act
        $prepaidCard->load($expected);

        //Assert
        $this->assertEquals($prepaidCard->balance(), $expected->getAmount());
    }

    /** @test */
    public function it_can_not_be_locked_when_locked()
    {
        //Assert
        $this->expectException(CardLockedException::class);

        //Arrange
        $currency = new Currency('GBP');
        $prepaidCard = PrepaidCard::emit($currency);

        //Act
        $prepaidCard->lock();
        $prepaidCard->lock();
    }

    /** @test */
    public function it_can_be_unlocked()
    {
        //Arrange
        $currency = new Currency('GBP');
        $prepaidCard = PrepaidCard::emit(new Currency('GBP'));

        //Act
        $prepaidCard->lock();
        $prepaidCard->unlock();

        //Assert
        $this->assertEquals(0, $prepaidCard->balance());
    }

    /** @test */
    public function it_can_not_be_unlocked_when_active()
    {
        //Assert
        $this->expectException(CardLockedException::class);

        //Arrange
        $currency = new Currency('GBP');
        $prepaidCard = PrepaidCard::emit($currency);

        //Act
        $prepaidCard->unlock();
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

        //Act
        $prepaidCard->load($amount);
        $prepaidCard->lock();
        $prepaidCard->autorizationRequest('Coffee shop', $amount, new \DateTimeImmutable('2018-01-01'));
    }

    /** @test */
    public function it_does_not_allow_authorize_more_than_balance()
    {
        //Assert
        $this->expectException(InsufficientFundsException::class);

        //Arrange
        $currency = $currency = new Currency('GBP');
        $amount = new Money(100, $currency);
        $amount2 = new Money(200, $currency);
        $prepaidCard = PrepaidCard::emit(new Currency('GBP'));

        //Act
        $prepaidCard->load($amount);
        $prepaidCard->autorizationRequest('Coffee shop', $amount2, new \DateTimeImmutable('2018-01-01'));
    }

    /** @test */
    public function it_allows_to_capture_transaction_when_locked()
    {
        //Arrange
        $currency = $currency = new Currency('GBP');
        $amount = new Money(100, $currency);
        $prepaidCard = PrepaidCard::emit(new Currency('GBP'));

        //Act
        $prepaidCard->load($amount);
        $transaction = $prepaidCard->autorizationRequest('Coffee shop', $amount, new \DateTimeImmutable('2018-01-01'));
        $prepaidCard->lock();
        $prepaidCard->capture($transaction, $amount);

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
        $amount3 = new Money(30, $currency);
        $prepaidCard = PrepaidCard::emit(new Currency('GBP'));

        //Act
        $prepaidCard->load($amount);
        $transaction = $prepaidCard->autorizationRequest('Coffee shop', $amount, new \DateTimeImmutable('2018-01-01'));

        //Assert
        $this->assertEquals(0, $prepaidCard->availableBalance());
        $this->assertEquals(100, $prepaidCard->balance());

        //Act
        $prepaidCard->capture($transaction, $amount2);
        $prepaidCard->capture($transaction, $amount3);

        //Assert
        $this->assertEquals(0, $prepaidCard->balance());
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

        //Act
        $prepaidCard->load($amount);
        $transaction = $prepaidCard->autorizationRequest('Coffee shop', $amount, new \DateTimeImmutable('2018-01-01'));
        $prepaidCard->capture($transaction, $amount);
        $prepaidCard->capture($transaction, $amount);
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

        //Act
        $prepaidCard->load($amount);
        $transaction = $prepaidCard->autorizationRequest('Coffee shop', $amount, new \DateTimeImmutable('2018-01-01'));
        $prepaidCard->capture($transaction, $amount);
        $prepaidCard->reverse($transaction, $amount);
    }

    /** @test */
    public function it_does_not_allow_to_reverse_a_value_greater_than_authorized()
    {
        //Assert
        $this->expectException(TransactionException::class);

        //Arrange
        $currency = $currency = new Currency('GBP');
        $amount = new Money(100, $currency);
        $amount2 = new Money(200, $currency);
        $prepaidCard = PrepaidCard::emit(new Currency('GBP'));

        //Act
        $prepaidCard->load($amount);
        $transaction = $prepaidCard->autorizationRequest('Coffee shop', $amount, new \DateTimeImmutable('2018-01-01'));
        $prepaidCard->reverse($transaction, $amount2);
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

        //Act
        $prepaidCard->load($amount);
        $transaction = $prepaidCard->autorizationRequest('Coffee shop', $amount, new \DateTimeImmutable('2018-01-01'));

        //Assert
        $this->assertEquals(0, $prepaidCard->availableBalance());

        //Act
        $prepaidCard->reverse($transaction, $amount2);
        $prepaidCard->reverse($transaction, $amount2);

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

        //Act
        $prepaidCard->load($amount);
        $transaction = $prepaidCard->autorizationRequest('Coffee shop', $amount, new \DateTimeImmutable('2018-01-01'));
        $prepaidCard->capture($transaction, $amount);
        $prepaidCard->refund($transaction, $amount);
        $prepaidCard->refund($transaction, $amount);
    }

    /** @test */
    public function it_does_allows_to_refund_a_transaction_multiple_times()
    {
        //Arrange
        $currency = $currency = new Currency('GBP');
        $amount = new Money(100, $currency);
        $amount2 = new Money(20, $currency);

        $prepaidCard = PrepaidCard::emit(new Currency('GBP'));

        //Act
        $prepaidCard->load($amount);
        $transaction = $prepaidCard->autorizationRequest('Coffee shop', $amount, new \DateTimeImmutable('2018-01-01'));

        //Assert
        $this->assertEquals(0, $prepaidCard->availableBalance());

        //Act
        $prepaidCard->capture($transaction, $amount2);
        $prepaidCard->capture($transaction, $amount2);

        //Assert
        $this->assertEquals(0, $prepaidCard->availableBalance());
        $this->assertEquals(60, $prepaidCard->balance());

        //Act
        $prepaidCard->refund($transaction, $amount2);
        $prepaidCard->refund($transaction, $amount2);

        //Assert
        $this->assertEquals(40, $prepaidCard->availableBalance());
        $this->assertEquals(100, $prepaidCard->balance());
    }
}
