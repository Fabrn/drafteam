<?php

namespace App\Entity;

use App\Enum\DraftSide;
use App\Repository\DraftBanRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Clock\DatePoint;

#[ORM\Entity(repositoryClass: DraftBanRepository::class)]
class DraftBan
{
    public function __construct(
        #[ORM\Id]
        #[ORM\ManyToOne(inversedBy: 'bans')]
        #[ORM\JoinColumn(nullable: false)]
        public Draft $draft,
        #[ORM\Id]
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false)]
        public Champion $champion,
        #[ORM\Column]
        public DraftSide $side,
        #[ORM\Column]
        public int $position,
        #[ORM\Column(type: 'date_point')]
        public DatePoint $createdAt,
    ) {}
}
