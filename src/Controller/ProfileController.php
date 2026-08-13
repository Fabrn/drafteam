<?php

namespace App\Controller;

use App\Enum\Role;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Role::User->value)]
#[Route('/{_locale}/profile', name: 'profile_')]
class ProfileController extends AbstractController
{
    #[Route(name: 'index')]
    public function index(): Response
    {
        return $this->render('Site/profile/index.html.twig');
    }
}
