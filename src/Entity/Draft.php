<?php

namespace App\Entity;

use App\Enum\DraftStatus;
use App\Repository\DraftRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DraftRepository::class)]
class Draft
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    #[ORM\Column(type: 'uuid', unique: true)]
    public Uuid $identifier;

    #[ORM\Column(type: 'uuid')]
    public Uuid $blueTeamUuid;

    #[ORM\Column(type: 'uuid')]
    public Uuid $redTeamUuid;

    #[ORM\Column(type: 'uuid')]
    public Uuid $spectatorUuid;

    #[ORM\Column(options: ['default' => DraftStatus::Creating->value])]
    public DraftStatus $status = DraftStatus::Creating;

    #[ORM\Column(options: ['default' => 60])]
    public int $maxTimer = 60;

    #[ORM\Column(options: ['default' => false])]
    public bool $blueTeamReadyChecked = false;

    #[ORM\Column(options: ['default' => false])]
    public bool $redTeamReadyChecked = false;

    #[ORM\Column(options: ['default' => false])]
    public bool $isSandbox = false;

    #[ORM\OneToMany(targetEntity: DraftBan::class, mappedBy: 'draft', cascade: ['persist', 'remove'])]
    public Collection $bans;

    #[ORM\OneToMany(targetEntity: DraftPick::class, mappedBy: 'draft', cascade: ['persist', 'remove'])]
    public Collection $picks;

    public function __construct(
        #[ORM\Column(length: 32)]
        public string $name,
        #[ORM\Column(length: 32)]
        public string $blueTeamName,
        #[ORM\Column(length: 32)]
        public string $redTeamName,
        #[ORM\Column(type: 'date_point')]
        public DatePoint $createdAt,
    ) {
        $this->identifier = Uuid::v7();
        $this->blueTeamUuid = Uuid::v7();
        $this->redTeamUuid = Uuid::v7();
        $this->spectatorUuid = Uuid::v7();

        $this->bans = new ArrayCollection();
        $this->picks = new ArrayCollection();
    }
}
