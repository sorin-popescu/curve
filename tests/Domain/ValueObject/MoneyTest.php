<?php

namespace Test\Domain\ValueObject;

use Curve\Domain\Exception\NegativeFundsException;
use Curve\Domain\ValueObject\Currency;
use Curve\Domain\ValueObject\Money;

class MoneyTest extends \PHPUnit_Framework_TestCase
{
    public function testYouCanNotHaveNegativeMoney()
    {
        $amount = -320;
        $currency = new Currency('GBP');

        $this->expectException(NegativeFundsException::class);
        new Money($amount, $currency);
    }

    public function testYouCanNotAddDifferentCurrency()
    {
        $amount = 10;
        $currency1 = new Currency('GBP');
        $currency2 = new Currency('USD');

        $money1 = new Money($amount, $currency1);
        $money2 = new Money($amount, $currency2);
        $this->expectException(\InvalidArgumentException::class);
        $money1->add($money2);
    }

    public function testYouCanNotDeductDifferentCurrency()
    {
        $amount = 10;
        $currency1 = new Currency('GBP');
        $currency2 = new Currency('USD');

        $money1 = new Money($amount, $currency1);
        $money2 = new Money($amount, $currency2);
        $this->expectException(\InvalidArgumentException::class);
        $money1->deduct($money2);
    }

    public function testOneIsLessThanOther()
    {
        $amount1 = 320;
        $amount2 = 30;
        $currency = new Currency('GBP');

        $money1 = new Money($amount1, $currency);
        $money2 = new Money($amount2, $currency);
        $this->assertTrue($money2->isLessThan($money1));
    }

    public function testOneIsMoreThanOther()
    {
        $amount1 = 320;
        $amount2 = 30;
        $currency = new Currency('GBP');

        $money1 = new Money($amount1, $currency);
        $money2 = new Money($amount2, $currency);
        $this->assertTrue($money1->isMoreThan($money2));
    }

    public function testYouCanNotCompareDifferentCurrency()
    {
        $amount1 = 320;
        $amount2 = 30;
        $currency1 = new Currency('GBP');
        $currency2 = new Currency('USD');

        $money1 = new Money($amount1, $currency1);
        $money2 = new Money($amount2, $currency2);

        $this->expectException(\InvalidArgumentException::class);
        $this->assertTrue($money1->isMoreThan($money2));
    }
}
