<?php

namespace App\Twig\Functions;

use App\Entity\Draft;
use App\Enum\DraftStatus;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Attribute\AsTwigFunction;

final readonly class DraftFunctions
{
    public function __construct(
        private RequestStack $requestStack,
        private TranslatorInterface $translator,
    ) {}

    #[AsTwigFunction(name: 'draft_mercure_url')]
    public function getDraftMercureUrl(Draft $draft): string
    {
        $request = $this->requestStack->getCurrentRequest();

        return $request->getSchemeAndHttpHost() . '/draft/' . $draft->identifier;
    }

    #[AsTwigFunction(name: 'draft_status')]
    public function getDraftStatus(Draft $draft): string
    {
        if (DraftStatus::Pending === $draft->status) {
            return $this->translator->trans('draft.status.pending');
        }

        if (DraftStatus::Finished === $draft->status) {
            return $this->translator->trans('draft.status.finished');
        }

        if (DraftStatus::Creating === $draft->status) {
            return $this->translator->trans('draft.status.creating');
        }

        return $this->translator->trans('draft.status.' . $draft->phase->value);
    }
}
