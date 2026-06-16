<?php

namespace App\Controller;

use App\Entity\Draft;
use App\Enum\DraftRole;
use App\Enum\DraftStatus;
use App\Form\DraftType;
use App\Repository\ChampionRepository;
use App\ValueObject\DraftWithRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/draft', name: 'draft_')]
class DraftController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    #[Route('/create', name: 'create')]
    public function create(Request $request): Response
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

            if ($draft->isSandbox) {
                // Automatic ready check on sandbox creation
                $draft->blueTeamReadyChecked = true;
                $draft->redTeamReadyChecked = true;

                // Since ready checked is skipped, draft is already ongoing
                $draft->status = DraftStatus::Ongoing;
            }

            $this->entityManager->persist($draft);
            $this->entityManager->flush();

            return $this->render('Site/draft/codes.html.twig', [
                'draft' => $draft,
            ]);
        }

        return $this->render('Site/draft/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{identifier}/{role}', name: 'view')]
    public function view(DraftWithRole $draftWithRole, ChampionRepository $championRepository): Response
    {
        return $this->render('Site/draft/view.html.twig', [
            'draftWithRole' => $draftWithRole,
            'champions' => $championRepository->findAll(),
        ]);
    }

    #[Route('/{identifier}/{role}/ready_check', name: 'ready_check')]
    public function readyCheck(DraftWithRole $draftWithRole, HubInterface $hub): JsonResponse
    {
        $draftWithRole->draft->redTeamReadyChecked = true;

        $this->entityManager->flush();

        $hub->publish(new Update(
            topics: 'http://localhost/draft/' . $draftWithRole->draft->identifier,
            data: \json_encode([
                'action' => 'ready_check',
                'side' => $draftWithRole->role === DraftRole::BlueDrafter ? 'blue' : 'red',
            ]),
        ));

        return $this->json([
            'success' => true,
        ]);
    }
}
