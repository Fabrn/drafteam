<?php

namespace App\Twig\Functions;

use App\Entity\Draft;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Attribute\AsTwigFunction;

final readonly class DraftFunctions
{
    public function __construct(
        private RequestStack $requestStack,
    ) {}

    #[AsTwigFunction(name: 'draft_mercure_url')]
    public function getDraftMercureUrl(Draft $draft): string
    {
        $request = $this->requestStack->getCurrentRequest();

        return $request->getSchemeAndHttpHost() . '/draft/' . $draft->identifier;
    }
}
