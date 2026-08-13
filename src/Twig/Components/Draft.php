<?php

namespace App\Twig\Components;

use App\Bridge\LeagueOfLegends\DataDragon\DataDragonService;
use App\Entity\ChampionData;
use App\Entity\Draft as DraftEntity;
use App\Entity\DraftBan;
use App\Entity\DraftPick;
use App\Enum\DraftAction;
use App\Enum\DraftPhase;
use App\Enum\DraftRole;
use App\Enum\DraftSide;
use App\Enum\DraftStatus;
use App\Repository\ChampionDataRepository;
use App\Repository\ChampionRepository;
use App\Repository\DraftRepository;
use App\Twig\Functions\DraftFunctions;
use App\ValueObject\Mercure\Ban;
use App\ValueObject\Mercure\Pick;
use App\ValueObject\Mercure\PrePick;
use App\ValueObject\Mercure\ReadyCheck;
use App\ValueObject\Mercure\Remove;
use App\ValueObject\Mercure\TickTimer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Uid\Uuid;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Draft
{
    use DefaultActionTrait;

    #[LiveProp]
    public Uuid $identifier;

    #[LiveProp]
    public DraftRole $role;

    #[LiveProp(writable: true)]
    public string $search = '';

    public DraftEntity $draft {
        get => $this->draftRepository->findOneBy([
            'identifier' => $this->identifier,
        ]);
    }

    public DraftSide $draftSide {
        get => DraftRole::BlueDrafter === $this->role ? DraftSide::Blue : DraftSide::Red;
    }

    /**
     * @var list<ChampionData>
     */
    public array $champions {
        get => $this->championDataRepository->findContainingName($this->search);
    }

    public ?string $latestPatch {
        get {
            try {
                return $this->dataDragonService->getLatestVersion();
            } catch (\Throwable) {
                return null;
            }
        }
    }

    public function __construct(
        private readonly DraftRepository $draftRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly HubInterface $hub,
        private readonly DraftFunctions $draftFunctions,
        private readonly ChampionDataRepository $championDataRepository,
        private readonly ChampionRepository $championRepository,
        private readonly DataDragonService $dataDragonService,
    ) {}

    #[LiveAction]
    public function readyCheck(): void
    {
        if (DraftRole::Spectator === $this->role) {
            return;
        }

        if (DraftRole::BlueDrafter === $this->role) {
            $this->draft->blueTeamReadyChecked = true;
        } else {
            $this->draft->redTeamReadyChecked = true;
        }

        if ($this->draft->blueTeamReadyChecked && $this->draft->redTeamReadyChecked) {
            $this->draft->status = DraftStatus::Ongoing;
        }

        $this->entityManager->flush();

        $this->hub->publish(new Update(
            topics: $this->draftFunctions->getDraftMercureUrl($this->draft),
            data: \json_encode(new ReadyCheck($this->draftSide)),
        ));
    }

    #[LiveAction]
    public function ban(#[LiveArg] int $id): void
    {
        if (!$this->draft->phase->isBanPhase()) {
            // If it is the ban phase, it means it is not the pick phase, so we can't pre-pick
            return;
        }

        $champion = $this->championRepository->find($id);

        $this->entityManager->persist(new DraftBan(
            draft: $this->draft,
            champion: $champion,
            side: $this->draft->phase->getSide(),
            position: $this->draft->phase->getPosition(),
            createdAt: new DatePoint(),
        ));

        $this->draft->phase = $this->draft->phase->getNext();

        $this->draft->currentTimer = $this->draft->maxTimer;

        $this->entityManager->flush();

        $this->hub->publish(new Update(
            topics: $this->draftFunctions->getDraftMercureUrl($this->draft),
            data: \json_encode(new Ban($this->championDataRepository->findOneBy([
                'champion' => $champion,
                'language' => 'en_US', // TODO locale
            ]))),
        ));
    }

    #[LiveAction]
    public function pick(#[LiveArg] int $id): void
    {
        if ($this->draft->phase->isBanPhase()) {
            // If it is the ban phase, it means it is not the pick phase, so we can't pre-pick
            return;
        }

        $champion = $this->championRepository->find($id);

        // Transforms temporary pick or create a pick
        /** @var false|DraftPick $currentTempPick */
        $currentTempPick = $this->draft
            ->picks
            ->filter(fn(DraftPick $pick) => $pick->isTemporary && $pick->side === $this->draft->phase->getSide())
            ->first();

        if (false !== $currentTempPick) {
            $currentTempPick->champion = $champion;
            $currentTempPick->isTemporary = false;
        } else {
            $this->entityManager->persist(new DraftPick(
                draft: $this->draft,
                champion: $champion,
                side: $this->draft->phase->getSide(),
                position: $this->draft->phase->getPosition(),
                createdAt: new DatePoint(),
            ));
        }

        if (DraftPhase::RedPick5 === $this->draft->phase) {
            $this->draft->status = DraftStatus::Finished;
        } else {
            $this->draft->phase = $this->draft->phase->getNext();
        }

        $this->draft->currentTimer = $this->draft->maxTimer;

        $this->entityManager->flush();

        $this->hub->publish(new Update(
            topics: $this->draftFunctions->getDraftMercureUrl($this->draft),
            data: \json_encode(new Pick($this->championDataRepository->findOneBy([
                'champion' => $champion,
                'language' => 'en_US', // TODO locale
            ]))),
        ));
    }

    #[LiveAction]
    public function prePick(#[LiveArg] int $id): void
    {
        if ($this->draft->phase->isBanPhase()) {
            // If it is the ban phase, it means it is not the pick phase, so we can't pre-pick
            return;
        }

        $champion = $this->championRepository->find($id);

        // Replaces current temporary pick if exists, otherwise create a pick
        /** @var false|DraftPick $currentTempPick */
        $currentTempPick = $this->draft
            ->picks
            ->filter(fn(DraftPick $pick) => $pick->isTemporary && $pick->side === $this->draft->phase->getSide())
            ->first();

        if (false !== $currentTempPick) {
            $currentTempPick->champion = $champion;
        } else {
            $this->entityManager->persist(new DraftPick(
                draft: $this->draft,
                champion: $champion,
                side: $this->draft->phase->getSide(),
                position: $this->draft->phase->getPosition(),
                createdAt: new DatePoint(),
                isTemporary: true,
            ));
        }

        $this->entityManager->flush();

        $this->hub->publish(new Update(
            topics: $this->draftFunctions->getDraftMercureUrl($this->draft),
            data: \json_encode(new PrePick(
                champion: $this->championDataRepository->findOneBy([
                    'champion' => $champion,
                    'language' => 'en_US', // TODO locale
                ]),
                side: $this->draftSide,
                position: $this->draft->phase->getPosition(),
            )),
        ));
    }

    #[LiveAction]
    public function choose(
        #[LiveArg] int $championId,
        #[LiveArg] DraftAction $action,
        #[LiveArg] DraftSide $side,
        #[LiveArg] int $index,
    ): void {
        if (!$this->draft->isSandbox) {
            return;
        }

        $champion = $this->championRepository->find($championId);

        if (!$this->draft->isChampionAvailable($champion)) {
            return;
        }

        if (DraftAction::Pick === $action) {
            $this->entityManager->persist(new DraftPick(
                draft: $this->draft,
                champion: $champion,
                side: $side,
                position: $index,
                createdAt: new DatePoint(),
            ));

            $topic = new Pick($this->championDataRepository->findOneBy([
                'champion' => $champion,
                'language' => 'en_US', // TODO locale
            ]));
        } else {
            $this->entityManager->persist(new DraftBan(
                draft: $this->draft,
                champion: $champion,
                side: $side,
                position: $index,
                createdAt: new DatePoint(),
            ));

            $topic = new Ban($this->championDataRepository->findOneBy([
                'champion' => $champion,
                'language' => 'en_US', // TODO locale
            ]));
        }

        $this->entityManager->flush();

        $this->hub->publish(new Update(
            topics: $this->draftFunctions->getDraftMercureUrl($this->draft),
            data: \json_encode($topic),
        ));
    }

    #[LiveAction]
    public function remove(
        #[LiveArg] DraftAction $action,
        #[LiveArg] DraftSide $side,
        #[LiveArg] int $index,
    ): void {
        if (!$this->draft->isSandbox) {
            return;
        }

        if (DraftAction::Pick === $action) {
            $item = $this->draft->picks->findFirst(function (int $i, DraftPick $draftPick) use ($side, $index) {
                return $draftPick->position === $index && $draftPick->side === $side;
            });

            if (!$item instanceof DraftPick) {
                return;
            }
        } else {
            $item = $this->draft->bans->findFirst(function (int $i, DraftBan $draftBan) use ($side, $index) {
                return $draftBan->position === $index && $draftBan->side === $side;
            });

            if (!$item instanceof DraftBan) {
                return;
            }
        }

        $topic = new Remove($item->champion);

        $this->entityManager->remove($item);
        $this->entityManager->flush();

        $this->hub->publish(new Update(
            topics: $this->draftFunctions->getDraftMercureUrl($this->draft),
            data: \json_encode($topic),
        ));
    }

    #[LiveAction]
    public function tickTimer(): void
    {
        if ($this->draft->isSandbox) {
            return;
        }

        $this->draft->currentTimer = \max(0, $this->draft->currentTimer - 1);

        $this->entityManager->flush();

        $this->hub->publish(new Update(
            topics: $this->draftFunctions->getDraftMercureUrl($this->draft),
            data: \json_encode(new TickTimer(
                currentTimer: $this->draft->currentTimer,
            )),
        ));
    }
}
