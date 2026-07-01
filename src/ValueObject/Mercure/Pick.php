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
            'champion_name' => $this->champion->name,
        ];
    }
}
