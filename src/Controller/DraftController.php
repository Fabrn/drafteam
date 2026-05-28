<?php

namespace App\Controller;

use App\Entity\Draft;
use App\Form\DraftType;
use App\Repository\DraftRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

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
            $this->entityManager->persist($draft);
            $this->entityManager->flush();

            return $this->redirectToRoute('draft_codes', [
                'identifier' => $draft->identifier,
            ]);
        }

        return $this->render('Site/draft/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{identifier}/codes', name: 'codes')]
    public function codes(Uuid $identifier, DraftRepository $draftRepository): Response
    {
        $draft = $draftRepository->findOneBy(['identifier' => $identifier]);

        if (null === $draft) {
            throw $this->createNotFoundException();
        }

        return $this->render('Site/draft/codes.html.twig', [
            'draft' => $draft,
        ]);
    }
}
