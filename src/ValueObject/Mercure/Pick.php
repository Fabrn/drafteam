<?php

namespace App\ValueObject\Mercure;

use App\Entity\ChampionData;

final readonly class Pick implements \JsonSerializable
{
    public function __construct(
        public ChampionData $champion,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'action' => 'pick',
            'championName' => $this->champion->name,
        ];
    }
}
