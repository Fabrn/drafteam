<?php

namespace App\ValueObject\Mercure;

use App\Enum\DraftSide;

final readonly class ReadyCheck implements \JsonSerializable
{
    public function __construct(
        public DraftSide $side,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'action' => 'ready_check',
            'side' => $this->side->value,
        ];
    }
}
