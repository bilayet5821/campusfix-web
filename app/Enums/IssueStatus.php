<?php

namespace App\Enums;

enum IssueStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Rejected = 'rejected';

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
            self::Submitted => 'Submitted',
            self::UnderReview => 'Under Review',
            self::InProgress => 'In Progress',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
            self::Rejected => 'Rejected',
        };
    }

    public function defaultHistoryTitle(): string
    {
        return match ($this) {
            self::Submitted => 'Report submitted',
            self::UnderReview => 'Under administrative review',
            self::InProgress => 'Work in progress',
            self::Resolved => 'Issue resolved',
            self::Closed => 'Issue closed',
            self::Rejected => 'Report rejected',
        };
    }
}
