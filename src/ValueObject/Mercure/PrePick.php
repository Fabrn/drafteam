<?php

namespace App\ValueObject\Mercure;

use App\Entity\ChampionData;
use App\Enum\DraftSide;

final readonly class PrePick implements \JsonSerializable
{
    public function __construct(
        public ChampionData $champion,
        public DraftSide $side,
        public int $position,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'action' => 'pre_pick',
            'champion_name' => $this->champion->name,
            'champion_lol_id' => $this->champion->champion->lolId,
            'champion_lol_key' => $this->champion->champion->lolKey,
            'position' => $this->position,
            'side' => $this->side->value,
        ];
    }
}
