<?php

namespace App\ValueObject\Mercure;

final readonly class TickTimer implements \JsonSerializable
{
    public function __construct(
        public int $currentTimer,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'action' => 'tick_timer',
            'current_timer' => $this->currentTimer,
        ];
    }
}
