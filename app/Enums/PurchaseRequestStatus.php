<?php

namespace App\Enums;

enum PurchaseRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending approval',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Completed => 'Purchased',
            self::Cancelled => 'Cancelled',
        };
    }

    /** Bootstrap badge colour. */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'text-bg-warning',
            self::Approved => 'text-bg-info',
            self::Rejected => 'text-bg-danger',
            self::Completed => 'text-bg-success',
            self::Cancelled => 'text-bg-secondary',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'bi-hourglass-split',
            self::Approved => 'bi-check-circle',
            self::Rejected => 'bi-x-circle',
            self::Completed => 'bi-bag-check',
            self::Cancelled => 'bi-slash-circle',
        };
    }
}