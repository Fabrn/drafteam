<?php

namespace App\Bridge\Discord\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class DiscordProfile
{
    #[ORM\Column(length: 255, unique: true, nullable: true)]
    public ?string $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $username = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $globalUsername = null;

    #[ORM\Column(length: 2, nullable: true)]
    public ?string $locale = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $avatarHash = null;
}
