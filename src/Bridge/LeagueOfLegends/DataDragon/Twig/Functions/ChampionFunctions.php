<?php

namespace App\Bridge\LeagueOfLegends\DataDragon\Twig\Functions;

use App\Bridge\LeagueOfLegends\DataDragon\DataDragonService;
use Twig\Attribute\AsTwigFunction;

final readonly class ChampionFunctions
{
    public function __construct(
        private DataDragonService $dataDragonService,
    ) {}

    #[AsTwigFunction(name: 'lol_champion_square_image_url')]
    public function getSquareImageUrl(string $lolId): string
    {
        return \sprintf('https://ddragon.leagueoflegends.com/cdn/%s/img/champion/%s.png',
            $this->dataDragonService->getLatestVersion(),
            $lolId,
        );
    }
}
