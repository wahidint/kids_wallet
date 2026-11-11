<?php

namespace App\Enums;

/**
 * The two kinds of users in the app.
 * Stored in the users.role column as "parent" or "child".
 */
enum UserRole: string
{
    case Parent = 'parent';
    case Child = 'child';

    public function label(): string
    {
        return match ($this) {
            self::Parent => 'Parent',
            self::Child => 'Child',
        };
    }
}