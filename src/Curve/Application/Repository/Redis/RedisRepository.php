<?php

namespace Curve\Application\Repository\Redis;

use Curve\Domain\Repository\PrepaidCardRepository;
use Curve\Domain\ValueObject\CardNumber;
use Predis\ClientInterface;
use Curve\Domain\Entity\PrepaidCard;

class RedisRepository implements PrepaidCardRepository
{
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function save(PrepaidCard $prepaidCard)
    {
        $key = sprintf("%s", $prepaidCard->getNumber());
        $this->client->set(
            $key,
            serialize($prepaidCard)
        );
    }

    public function getByCardNumber(CardNumber $cardNumber)
    {
        $key = sprintf("%s", $cardNumber->getNumber());
        $prepaidCard = $this->client->get($key);

        return unserialize($prepaidCard);
    }
}
