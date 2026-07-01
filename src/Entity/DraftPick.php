<?php

namespace App\Entity;

use App\Enum\DraftSide;
use App\Repository\DraftPickRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Clock\DatePoint;

#[ORM\Entity(repositoryClass: DraftPickRepository::class)]
class DraftPick
{
    public function __construct(
        #[ORM\Id]
        #[ORM\ManyToOne(inversedBy: 'picks')]
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
        #[ORM\Column(options: ['default' => false])]
        public bool $isTemporary = false,
    ) {}
}
