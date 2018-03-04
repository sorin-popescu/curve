<?php

namespace Curve\Application\Repository\ToFile;

use Curve\Domain\Entity\PrepaidCard;
use Curve\Domain\Repository\PrepaidCardRepository;
use Curve\Domain\ValueObject\CardNumber;

class FileRepository implements PrepaidCardRepository
{
    private $fileName;

    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }

    public function save(PrepaidCard $currentAccount)
    {
        file_put_contents($this->fileName, serialize($currentAccount));
    }

    public function getByCardNumber(CardNumber $currentAccountNumber)
    {
        $content = file_get_contents($this->fileName);
        $prepaidCard = unserialize($content);

        return $prepaidCard;
    }
}
