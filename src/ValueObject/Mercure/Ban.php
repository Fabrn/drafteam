<?php

namespace App\ValueObject\Mercure;

use App\Entity\ChampionData;

final readonly class Ban implements \JsonSerializable
{
    public function __construct(
        public ChampionData $champion,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'action' => 'ban',
            'championName' => $this->champion->name,
        ];
    }
}
