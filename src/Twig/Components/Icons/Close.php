<?php

namespace App\Twig\Components\Icons;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Close
{
    public string $color = 'currentColor';
    public int $size = 24;
}
