<?php

namespace App\Bridge\LeagueOfLegends\DataDragon\Dto\Output;

final readonly class ImageOutput
{
    public function __construct(
        public string $full,
        public string $sprite,
        public string $group,
        public int $x,
        public int $y,
        public int $w,
        public int $h,
    ) {}
}
