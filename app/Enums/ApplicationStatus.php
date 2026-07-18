<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Incomplete = 'incomplete';
    case Eligible = 'eligible';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Waitlisted = 'waitlisted';
    case Archived = 'archived';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Brouillon',
            self::Submitted => 'Soumis',
            self::UnderReview => 'En vérification',
            self::Incomplete => 'Incomplet',
            self::Eligible => 'Recevable',
            self::Accepted => 'Accepté',
            self::Rejected => 'Refusé',
            self::Waitlisted => 'Liste d’attente',
            self::Archived => 'Archivé',
        };
    }
}
