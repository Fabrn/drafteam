<?php

namespace App\Entity;

use App\Repository\ChampionDataRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChampionDataRepository::class)]
class ChampionData
{
    public function __construct(
        #[ORM\Id]
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false)]
        public Champion $champion,
        #[ORM\Id]
        #[ORM\Column(length: 5)]
        public string $language,
        #[ORM\Column(length: 16)]
        public string $name,
        #[ORM\Column(length: 255)]
        public string $title,
    ) {}
}
