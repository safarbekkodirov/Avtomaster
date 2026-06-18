<?php

declare(strict_types=1);

namespace App\Controller\Base\Constants;

class BookingStatus
{
    public const PENDING   = 'pending';
    public const CONFIRMED = 'confirmed';
    public const COMPLETED = 'completed';
    public const CANCELLED = 'cancelled';
    public const REFUNDED  = 'refunded';
}
