<?php

namespace App\Enum;

enum DraftStatus: string
{
    case Creating = 'creating';
    case Pending = 'pending';
    case Ongoing = 'ongoing';
    case Finished = 'finished';
}
