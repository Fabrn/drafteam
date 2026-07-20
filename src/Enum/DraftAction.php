<?php

namespace App\Enum;

enum DraftAction: string
{
    case Pick = 'pick';
    case Ban = 'ban';
}
