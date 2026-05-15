<?php

namespace App\Bridge\LeagueOfLegends\DataDragon\Dto\Output;

final readonly class ChampionOutput
{
    public function __construct(
        public string $id,
        public string $name,
        public string $key,
        public string $title,
        public ImageOutput $image,
    ) {}
}
