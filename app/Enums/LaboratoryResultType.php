<?php

namespace App\Enums;

enum LaboratoryResultType: string
{
    case PREGNANCY_TEST = 'pregnancy_test';
    case PAPSMEAR = 'papsmear';
    case CBC = 'cbc';
    case URINALYSIS = 'urinalysis';
    case FECALYSIS = 'fecalysis';

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }

    public function service(): string
    {
        return match ($this) {
                // ---------------------------------------------------------
                // Laboratory Services
                // ---------------------------------------------------------
            self::PREGNANCY_TEST,
            self::PAPSMEAR,
            self::CBC,
            self::URINALYSIS,
            self::FECALYSIS
            => 'laboratory_services',
        };
    }

    public function serviceLabel(): string
    {
        return match ($this->service()) {
            'consultation' => 'Consultation',
            'family_planning_service' => 'Family Planning Services',
            'integrative_and_wellness' => 'Integrative and Wellness Healthcare Services',
            'laboratory_services' => 'Laboratory Services',
            'maternal_and_child_health_services' => 'Maternal and Child Health Services',
            'medical_surgical_services' => 'Medical/Surgical Services',
        };
    }

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

    public function fullDescription(): string
    {
        return $this->serviceLabel().'/'.$this->label();
    }
}
