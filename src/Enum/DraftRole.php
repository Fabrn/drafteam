<?php

namespace App\Enum;

enum DraftRole: string
{
    case BlueDrafter = 'blue_drafter';
    case RedDrafter = 'red_drafter';
    case Spectator = 'spectator';
}
