<?php

namespace Curve\Domain\Repository;

use Curve\Domain\Entity\PrepaidCard;
use Curve\Domain\ValueObject\CardNumber;

interface PrepaidCardRepository
{
    public function save(PrepaidCard $currentAccount);

    public function getByCardNumber(CardNumber $currentAccountNumber);
}
