<?php

namespace App\Twig\Components;

use App\Enum\DraftRole;
use App\Enum\DraftSide;
use App\Repository\DraftRepository;
use App\ValueObject\Mercure\ReadyCheck;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Uid\Uuid;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
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

    public \App\Entity\Draft $draft {
        get => $this->draftRepository->findOneBy([
            'identifier' => $this->identifier,
        ]);
    }

    public function __construct(
        private readonly DraftRepository $draftRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly HubInterface $hub,
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

        $this->entityManager->flush();

        $this->hub->publish(new Update(
            topics: 'http://localhost/draft/' . $this->draft->identifier,
            data: \json_encode(new ReadyCheck($side)),
        ));
    }
}
