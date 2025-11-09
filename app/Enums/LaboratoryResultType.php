<?php

namespace App\Enums;

enum LaboratoryResultType: string
{
    case PREGNANCY_TEST = 'pregnancy_test';
    case PAPSMEAR = 'papsmear';
    case CBC = 'cbc';
    case URINALYSIS = 'urinalysis';
    case FECALYSIS = 'fecalysis';

    public function label(): string
    {
        return match ($this) {
            self::PREGNANCY_TEST => 'Pregnancy Test',
            self::PAPSMEAR => 'Papsmear',
            self::CBC => 'Complete Blood Count',
            self::URINALYSIS => 'Urinalysis',
            self::FECALYSIS => 'Fecalysis',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
