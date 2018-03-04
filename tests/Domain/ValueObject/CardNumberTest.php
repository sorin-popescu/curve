<?php

namespace Test\Domain\ValueObject;

use Curve\Domain\ValueObject\CardNumber;

class CardNumberTest extends \PHPUnit_Framework_TestCase
{
    public function testYouCanDisplayAccountNumber()
    {
        $number = 999999;

        $cardNumber = CardNumber::fromString($number);
        $this->assertEquals($cardNumber->getNumber(), $number);
    }
}
