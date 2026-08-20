<?php

namespace App\Controller;

use App\Entity\Champion;
use App\Entity\Draft;
use App\Entity\User;
use App\Enum\DraftStatus;
use App\Enum\Role;
use App\Form\DraftType;
use App\Repository\ChampionDataRepository;
use App\Repository\ChampionRepository;
use App\ValueObject\DraftWithRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/{_locale}/draft', name: 'draft_', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class DraftController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    #[Route('/create', name: 'create')]
    public function create(Request $request, ChampionDataRepository $championDataRepository, #[CurrentUser] ?User $user = null): Response
    {
        $draft = new Draft(
            name: '',
            blueTeamName: '',
            redTeamName: '',
            createdAt: DatePoint::createFromMutable(new \DateTime()),
        );

        $form = $this->createForm(DraftType::class, $draft);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $draft->status = DraftStatus::Pending;
            $draft->createdBy = $user;

            if ($draft->isSandbox) {
                // Automatic ready check on sandbox creation
                $draft->blueTeamReadyChecked = true;
                $draft->redTeamReadyChecked = true;

                // Since ready checked is skipped, draft is already ongoing
                $draft->status = DraftStatus::Ongoing;
            }

            if (true === $form->get('disableTimer')->getData()) {
                // Sets timer to 0 to disable it
                $draft->maxTimer = 0;
            }

            $draft->bannedLolIds = \array_map(static fn (Champion $c) => $c->lolKey, $form->get('bannedLolIds')->getData());

            $this->entityManager->persist($draft);
            $this->entityManager->flush();

            if ($draft->isSandbox) {
                return $this->redirectToRoute('draft_view', [
                    'identifier' => $draft->identifier,
                    'role' => $draft->spectatorUuid,
                ]);
            }

            return $this->render('Site/draft/codes.html.twig', [
                'draft' => $draft,
            ]);
        }

        return $this->render('Site/draft/create.html.twig', [
            'form' => $form->createView(),
            'champions' => $championDataRepository->findByRequestLanguage($request),
        ]);
    }

    #[Route('/{identifier}/{role}', name: 'view')]
    public function view(DraftWithRole $draftWithRole, ChampionRepository $championRepository): Response
    {
        if (null !== $draftWithRole->draft->cancelledAt) {
            throw $this->createNotFoundException();
        }

        return $this->render('Site/draft/view.html.twig', [
            'draftWithRole' => $draftWithRole,
            'champions' => $championRepository->findAll(),
        ]);
    }

    #[IsGranted(Role::User->value)]
    #[Route('/{identifier}/cancel', name: 'cancel')]
    public function cancel(Draft $draft, #[CurrentUser] User $user): Response
    {
        if ($draft->createdBy?->id !== $user->id) {
            throw $this->createAccessDeniedException();
        }

        if (null !== $draft->cancelledAt) {
            return new Response(status: Response::HTTP_NO_CONTENT);
        }

        $draft->cancelledAt = new DatePoint();

        $this->entityManager->flush();

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
