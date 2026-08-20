<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/auth', name: 'auth_')]
final class AuthenticationController extends AbstractController
{
    #[Route('/discord/login', name: 'discord_login')]
    public function discordLogin(Request $request, ClientRegistry $clientRegistry): void {}

    #[Route('/discord/start', name: 'discord_start')]
    public function discordStart(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry->getClient('discord')->redirect(['identify']);
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): never
    {
        throw new \Exception('Logging out');
    }
}
