<?php

namespace App\Bridge\Discord\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use KnpU\OAuth2ClientBundle\Security\Exception\IdentityProviderAuthenticationException;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wohali\OAuth2\Client\Provider\DiscordResourceOwner;

final class DiscordAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly EntityManagerInterface $entityManager,
        private readonly RouterInterface $router,
        private readonly UserRepository $userRepository,
        #[Autowire(param: 'kernel.enabled_locales')]
        private readonly array $locales,
        private readonly TranslatorInterface $translator,
    ) {}

    public function start(Request $request, ?AuthenticationException $authException = null): RedirectResponse
    {
        return new RedirectResponse($this->router->generate("auth_discord_start"), Response::HTTP_TEMPORARY_REDIRECT);
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get("_route") === "auth_discord_login";
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $client = $this->clientRegistry->getClient("discord");
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var DiscordResourceOwner $discordUser */
                $discordUser = $client->fetchUserFromToken($accessToken);

                $user = $this->userRepository->findOneBy(["discordProfile.id" => $discordUser->getId()]);

                if (null === $user) {
                    $user = new User();
                    $user->discordProfile->id = $discordUser->getId();

                    $this->entityManager->persist($user);
                }

                $arrayData = $discordUser->toArray();

                // Updating user data at each connection
                $user->discordProfile->username = $discordUser->getUsername();
                $user->discordProfile->globalUsername = $arrayData['global_name'] ?? null;
                $user->discordProfile->avatarHash = $discordUser->getAvatarHash();
                $user->discordProfile->locale = $arrayData['locale'] ?? null;

                $this->entityManager->flush();

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        /** @var User $user */
        $user = $token->getUser();
        $user->lastlyLoggedInAt = new DatePoint();

        $this->entityManager->flush();

        // TODO ajout le fallback dans les params
        $locale = $request->getPreferredLanguage($this->locales) ?? $user->discordProfile->locale;

        return new RedirectResponse($this->router->generate('profile_index', [
            '_locale' => $locale,
        ]));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = \strtr($exception->getMessageKey(), $exception->getMessageData());

        if ($exception instanceof IdentityProviderAuthenticationException) {
            $message = $this->translator->trans('auth.error');
        }

        $request->getSession()->getFlashBag()->add('error', $message);

        return new RedirectResponse($this->router->generate('index', [
            // TODO ajout le fallback dans les params
            '_locale' => $request->getPreferredLanguage($this->locales),
        ]));
    }
}
