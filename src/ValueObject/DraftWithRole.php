<?php

namespace App\ValueObject;

use App\Entity\Draft;
use App\Enum\DraftRole;

final readonly class DraftWithRole
{
    public function __construct(
        public Draft $draft,
        public DraftRole $role,
    ) {}
}
