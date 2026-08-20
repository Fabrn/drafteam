<?php

namespace App\Twig\Functions;

use App\Entity\User;
use Twig\Attribute\AsTwigFunction;

final readonly class UserFunctions
{
    #[AsTwigFunction('user_avatar')]
    public function getAvatarUrl(User $user): string
    {
        return \sprintf(
            'https://cdn.discordapp.com/avatars/%s/%s.png',
            $user->discordProfile->id,
            $user->discordProfile->avatarHash,
        );
    }
}
