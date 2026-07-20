<?php

namespace App\ValueObject\Mercure;

use App\Entity\Champion;

final readonly class Remove implements \JsonSerializable
{
    public function __construct(
        public Champion $champion,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'action' => 'remove',
            'champion' => $this->champion->id,
        ];
    }
}
