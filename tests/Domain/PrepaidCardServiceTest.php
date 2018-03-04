<?php

namespace Tests\Domain;

use Curve\Application\Repository\Redis\RedisRepository;
use Curve\Domain\Entity\PrepaidCard;
use Curve\Domain\Repository\PrepaidCardRepository;
use Curve\Domain\Service\PrepaidCardService;
use Curve\Domain\ValueObject\Currency;
use Mockery as m;

class PrepaidCardServiceTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    /** @test */
    public function it_ca_emit_a_card()
    {
        $repository = m::mock(PrepaidCardRepository::class);
        $repository->shouldReceive('save')->once();

        $service = new PrepaidCardService($repository);

        $prepaidCard = $service->emitCard('GBP');

        $this->assertInstanceOf(PrepaidCard::class, $prepaidCard);
    }

    /** @test */
    public function it_can_lock_a_card()
    {
        $repository = m::mock(PrepaidCardRepository::class);
        $prepaidCard = m::mock(PrepaidCard::class);
        $repository->shouldReceive('getByCardNumber')->once()->andReturn($prepaidCard);
        $prepaidCard->shouldReceive('lock')->once();
        $repository->shouldReceive('save')->once();

        $service = new PrepaidCardService($repository);

        $this->assertNull($service->lockCard(1234));
    }

    /** @test */
    public function it_can_unlock_a_card()
    {
        $repository = m::mock(PrepaidCardRepository::class);
        $prepaidCard = m::mock(PrepaidCard::class);
        $repository->shouldReceive('getByCardNumber')->once()->andReturn($prepaidCard);
        $prepaidCard->shouldReceive('unlock')->once();
        $repository->shouldReceive('save')->once();

        $service = new PrepaidCardService($repository);

        $this->assertNull($service->unlockCard(1234));
    }

    /** @test */
    public function it_can_display_balance()
    {
        $repository = m::mock(PrepaidCardRepository::class);
        $prepaidCard = PrepaidCard::emit(new Currency('GBP'));
        $repository->shouldReceive('getByCardNumber')->once()->andReturn($prepaidCard);

        $service = new PrepaidCardService($repository);

        $card = $service->displayBalance($prepaidCard->getNumber());

        $this->assertEquals(0, $card->balance());
        $this->assertEquals(0, $card->availableBalance());
    }

    /** @test */
    public function it_can_make_deposit()
    {
        $repository = m::mock(PrepaidCardRepository::class);
        $prepaidCard = PrepaidCard::emit(new Currency('GBP'));
        $repository->shouldReceive('getByCardNumber')->once()->andReturn($prepaidCard);
        $repository->shouldReceive('save')->once();


        $service = new PrepaidCardService($repository);
        $this->assertNull($service->makeDeposit($prepaidCard->getNumber(), 100));
    }

    /** @test */
    public function it_can_make_an_authorization_request()
    {
        $repository = m::mock(PrepaidCardRepository::class);
        $prepaidCard = PrepaidCard::emit(new Currency('GBP'));

        $repository->shouldReceive('getByCardNumber')->times(2)->andReturn($prepaidCard);
        $repository->shouldReceive('save')->times(2);


        $service = new PrepaidCardService($repository);
        $service->makeDeposit($prepaidCard->getNumber(), 100);

        $this->assertNotNull($service->makeAuthorizationRequest('Coffee shop', $prepaidCard->getNumber(), 100, '2018-01-01'));
    }

    /** @test */
    public function it_can_capture_a_transaction()
    {
        $repository = m::mock(PrepaidCardRepository::class);
        $prepaidCard = PrepaidCard::emit(new Currency('GBP'));

        $repository->shouldReceive('getByCardNumber')->times(3)->andReturn($prepaidCard);
        $repository->shouldReceive('save')->times(3);


        $service = new PrepaidCardService($repository);
        $service->makeDeposit($prepaidCard->getNumber(), 100);
        $transaction = $service->makeAuthorizationRequest('Coffee shop', $prepaidCard->getNumber(), 100, '2018-01-01');

        $this->assertNull($service->makeCapture($prepaidCard->getNumber(), $transaction, 100));
    }

    /** @test */
    public function it_can_reverse_a_transaction()
    {
        $repository = m::mock(PrepaidCardRepository::class);
        $prepaidCard = PrepaidCard::emit(new Currency('GBP'));

        $repository->shouldReceive('getByCardNumber')->times(3)->andReturn($prepaidCard);
        $repository->shouldReceive('save')->times(3);


        $service = new PrepaidCardService($repository);
        $service->makeDeposit($prepaidCard->getNumber(), 100);
        $transaction = $service->makeAuthorizationRequest('Coffee shop', $prepaidCard->getNumber(), 100, '2018-01-01');

        $this->assertNull($service->makeReverse($prepaidCard->getNumber(), $transaction, 100));
    }

    /** @test */
    public function it_can_refund_a_transaction()
    {
        $repository = m::mock(PrepaidCardRepository::class);
        $prepaidCard = PrepaidCard::emit(new Currency('GBP'));

        $repository->shouldReceive('getByCardNumber')->times(4)->andReturn($prepaidCard);
        $repository->shouldReceive('save')->times(4);


        $service = new PrepaidCardService($repository);
        $service->makeDeposit($prepaidCard->getNumber(), 100);
        $transaction = $service->makeAuthorizationRequest('Coffee shop', $prepaidCard->getNumber(), 100, '2018-01-01');

        $this->assertNull($service->makeCapture($prepaidCard->getNumber(), $transaction, 100));
        $this->assertNull($service->makeRefund($prepaidCard->getNumber(), $transaction, 100));
    }
}
