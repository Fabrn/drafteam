<?php

namespace App\Twig\Components;

use App\Repository\DraftRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Uid\Uuid;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Profile
{
    use DefaultActionTrait;

    public function __construct(
        private readonly DraftRepository $draftRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    #[LiveAction]
    public function cancelDraft(#[LiveArg] Uuid $identifier): void
    {
        $draft = $this->draftRepository->findOneBy(['identifier' => $identifier->toString()]);

        if (null === $draft) {
            return;
        }

        $draft->cancelledAt = new DatePoint();

        $this->entityManager->flush();
    }
}
