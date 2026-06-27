<?php

namespace App\Twig\Components;

use App\Entity\ChampionData;
use App\Entity\Draft as DraftEntity;
use App\Entity\DraftBan;
use App\Enum\DraftRole;
use App\Enum\DraftSide;
use App\Enum\DraftStatus;
use App\Repository\ChampionDataRepository;
use App\Repository\ChampionRepository;
use App\Repository\DraftRepository;
use App\Twig\Functions\DraftFunctions;
use App\ValueObject\Mercure\Ban;
use App\ValueObject\Mercure\ReadyCheck;
use Doctrine\Common\Collections\Order;
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

    public DraftEntity $draft {
        get => $this->draftRepository->findOneBy([
            'identifier' => $this->identifier,
        ]);
    }

    /**
     * @var list<ChampionData>
     */
    public array $champions {
        get => $this->championDataRepository->findBy(
            criteria: ['language' => 'en_US'],
            orderBy: ['name' => Order::Ascending->value],
        ); // TODO locale
    }

    public function __construct(
        private readonly DraftRepository $draftRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly HubInterface $hub,
        private readonly DraftFunctions $draftFunctions,
        private readonly ChampionDataRepository $championDataRepository,
        private readonly ChampionRepository $championRepository,
    ) {}

    #[LiveAction]
    public function readyCheck(): void
    {
        if (DraftRole::Spectator === $this->role) {
            return;
        }

        if (DraftRole::BlueDrafter === $this->role) {
            $this->draft->blueTeamReadyChecked = true;
            $side = DraftSide::Blue;
        } else {
            $this->draft->redTeamReadyChecked = true;
            $side = DraftSide::Red;
        }

        if ($this->draft->blueTeamReadyChecked && $this->draft->redTeamReadyChecked) {
            $this->draft->status = DraftStatus::Ongoing;
        }

        $this->entityManager->flush();

        $this->hub->publish(new Update(
            topics: $this->draftFunctions->getDraftMercureUrl($this->draft),
            data: \json_encode(new ReadyCheck($side)),
        ));
    }

    #[LiveAction]
    public function ban(#[LiveArg] int $id): void
    {
        $champion = $this->championRepository->find($id);

        $this->entityManager->persist(new DraftBan(
            draft: $this->draft,
            champion: $champion,
            side: $this->draft->phase->getSide(),
            position: $this->draft->phase->getPosition(),
            createdAt: new DatePoint(),
        ));

        $this->draft->phase = $this->draft->phase->getNext();

        $this->entityManager->flush();

        $this->hub->publish(new Update(
            topics: $this->draftFunctions->getDraftMercureUrl($this->draft),
            data: \json_encode(new Ban($this->championDataRepository->findOneBy([
                'champion' => $champion,
                'language' => 'en_US', // TODO locale
            ]))),
        ));
    }
}
