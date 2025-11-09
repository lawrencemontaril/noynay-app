<?php

namespace App\Enums;

enum LaboratoryResultStatus: string
{
    case PENDING = 'pending';
    case RELEASED = 'released';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::RELEASED => 'Released',
        };
    }

    public function options()
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
