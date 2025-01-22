<?php

namespace App\Traits\Moderation;


class Status
{
    const DRAFT = 'draft';
    const PENDING = 'moderation';
    const ACCEPTED = 'accepted';
    const REJECTED = 'rejected';
}
