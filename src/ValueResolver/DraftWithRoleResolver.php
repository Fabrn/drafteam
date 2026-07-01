<?php

namespace App\ValueResolver;

use App\Enum\DraftRole;
use App\Repository\DraftRepository;
use App\ValueObject\DraftWithRole;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Uid\Exception\InvalidArgumentException;
use Symfony\Component\Uid\Uuid;

final readonly class DraftWithRoleResolver implements ValueResolverInterface
{
    public function __construct(
        private DraftRepository $draftRepository,
    ) {}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();

        if (!$type || DraftWithRole::class !== $type) {
            return [];
        }

        $identifier = $request->attributes->get('identifier');

        if (!$identifier) {
            return [];
        }

        try {
            $identifier = Uuid::fromString($identifier);
        } catch (InvalidArgumentException) {
            return [];
        }

        $roleIdentifier = $request->attributes->get('role');

        if (!$roleIdentifier) {
            return [];
        }

        try {
            $roleIdentifier = Uuid::fromString($roleIdentifier);
        } catch (InvalidArgumentException) {
            return [];
        }

        $draft = $this->draftRepository->findOneBy([
            'identifier' => $identifier,
            'blueTeamUuid' => $roleIdentifier,
        ]);

        if (null !== $draft) {
            yield new DraftWithRole($draft, DraftRole::BlueDrafter);
        }

        $draft = $this->draftRepository->findOneBy([
            'identifier' => $identifier,
            'redTeamUuid' => $roleIdentifier,
        ]);

        if (null !== $draft) {
            yield new DraftWithRole($draft, DraftRole::RedDrafter);
        }

        $draft = $this->draftRepository->findOneBy([
            'identifier' => $identifier,
            'spectatorUuid' => $roleIdentifier,
        ]);

        if (null !== $draft) {
            yield new DraftWithRole($draft, DraftRole::Spectator);
        }

        return [];
    }
}
