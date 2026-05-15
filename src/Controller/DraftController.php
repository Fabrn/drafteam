<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/draft', name: 'draft_')]
class DraftController extends AbstractController
{
    #[Route('/create', name: 'create')]
    public function create(): Response
    {
        return $this->render('Site/draft/create.html.twig');
    }
}
