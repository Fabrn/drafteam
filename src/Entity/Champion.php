<?php

namespace App\Entity;

use App\Repository\ChampionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChampionRepository::class)]
class Champion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    public function __construct(
        #[ORM\Column(length: 16)]
        public string $lolId,
        #[ORM\Column(length: 3)]
        public string $lolKey,
        #[ORM\Column(length: 255)]
        public string $imageSquarePath,
        #[ORM\Column(length: 255)]
        public string $imageSplashPath,
    ) {}
}
