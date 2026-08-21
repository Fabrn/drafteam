<?php

namespace App\Entity;

use App\Enum\DraftPhase;
use App\Enum\DraftSide;
use App\Enum\DraftStatus;
use App\Repository\DraftRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Order;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DraftRepository::class)]
class Draft
{
    /**
     * @var array<int, DraftBan>
     */
    public array $blueSideBans {
        get {
            $bans = $this->bans->filter(static fn(DraftBan $ban) => DraftSide::Blue === $ban->side);
            $result = [];

            /** @var DraftBan $ban */
            foreach ($bans as $ban) {
                $result[$ban->position] = $ban;
            }

            return $result;
        }
    }

    /**
     * @var array<int, DraftBan>
     */
    public array $redSideBans {
        get {
            $bans = $this->bans->filter(static fn(DraftBan $ban) => DraftSide::Red === $ban->side);
            $result = [];

            /** @var DraftBan $ban */
            foreach ($bans as $ban) {
                $result[$ban->position] = $ban;
            }

            return $result;
        }
    }

    /**
     * @var array<int, DraftPick>
     */
    public array $blueSidePicks {
        get {
            $picks = $this->picks->filter(static fn(DraftPick $pick) => DraftSide::Blue === $pick->side);
            $result = [];

            /** @var DraftPick $pick */
            foreach ($picks as $pick) {
                $result[$pick->position] = $pick;
            }

            return $result;
        }
    }

    /**
     * @var array<int, DraftPick>
     */
    public array $redSidePicks {
        get {
            $picks = $this->picks->filter(static fn(DraftPick $pick) => DraftSide::Red === $pick->side);
            $result = [];

            /** @var DraftPick $pick */
            foreach ($picks as $pick) {
                $result[$pick->position] = $pick;
            }

            return $result;
        }
    }

    public bool $requiresTimer {
        get {
            return DraftStatus::Ongoing === $this->status;
        }
    }

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

    #[ORM\Column(nullable: true)]
    public ?int $currentTimer {
        get => $this->currentTimer ?? (0 === $this->maxTimer ? null : $this->maxTimer);
    }

    #[ORM\Column(options: ['default' => false])]
    public bool $blueTeamReadyChecked = false;

    #[ORM\Column(options: ['default' => false])]
    public bool $redTeamReadyChecked = false;

    #[ORM\Column(options: ['default' => false])]
    public bool $isSandbox = false;

    #[ORM\Column(options: ['default' => DraftPhase::BlueBan1->value])]
    public DraftPhase $phase = DraftPhase::BlueBan1;

    #[ORM\Column]
    public array $bannedLolIds = [];

    #[ORM\ManyToOne(inversedBy: 'createdDrafts')]
    public ?User $createdBy = null;

    #[ORM\Column(type: 'date_point', nullable: true)]
    public ?DatePoint $cancelledAt = null;

    #[ORM\OrderBy(['position' => Order::Ascending->value])]
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

    public function isChampionAvailable(Champion $champion): bool
    {
        return (
            $this->picks
                ->filter(static fn(DraftPick $pick) => $pick->champion->id === $champion->id && !$pick->isTemporary)
                ->isEmpty()
            && $this->bans->filter(static fn(DraftBan $ban) => $ban->champion->id === $champion->id)->isEmpty()
            && !\in_array($champion->lolKey, $this->bannedLolIds, strict: true)
        );
    }
}
