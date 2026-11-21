<?php

namespace App\Enums;

enum UserRole: string
{
    case Student = 'student';
    case Staff = 'staff';
    case Admin = 'admin';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Student => 'Student',
            self::Staff => 'Maintenance Staff',
            self::Admin => 'Administrator',
        };
    }
}
