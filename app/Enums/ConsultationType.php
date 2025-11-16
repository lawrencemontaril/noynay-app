<?php

namespace App\Enums;

enum ConsultationType: string
{
    case CONSULTATION = 'consultation';
    case FAMILY_PLANNING_COUNSELING = 'family_planning_counseling';
    case NATURAL_METHODS = 'natural_methods';
    case CHELATION_THERAPY = 'chelation_therapy';
    case MAGNETIC_RESONANCE_ANALYSIS = 'magnetic_resonance_analysis';
    case MULTIFUNCTIONAL_THERAPEUTIC_SERVICES = 'multifunctional_high_potential_therapeutic_services';
    case WEIGHT_LOSS_MANAGEMENT = 'weight_loss_management';
    case PSYCHOSOCIAL_AND_SPIRITUAL_COUNSELING = 'psychosocial_and_spiritual_counseling';
    case PRE_NATAL_AND_POST_NATAL = 'pre_natal_and_post_natal';
    case NORMAL_SPONTANEOUS_DELIVERY = 'normal_spontaneous_delivery';
    case IMMUNIZATION = 'immunization';
    case EAR_PIERCING = 'ear_pearcing';
    case NEBULIZATION = 'nebulization';
    case FOLEY_CATHETER_INSERTION = 'foley_catheter_insertion';
    case SURGICAL_WOUND_DRESSING = 'surgical_wound_dressing';
    case CORD_DRESSING = 'cord_dressing';
    case SUTURE_REMOVAL = 'suture_removal';
    case ISSUANCE_OF_BC_NEWBORN_SCREENING = 'issuance_of_bc_newborn_screening';
    case GENERAL_OPD_CONSULTATION = 'general_opd_consultation';
    case MEDICAL_OPD_CONSULTATION = 'medical_opd_consultation';
    case MINOR_SURGICAL_PROCEDURES = 'minor_surgical_procedures';
    case ISSUANCE_OF_MEDICAL_CERTIFICATE = 'issuance_of_medical_certificate';
    case PEDIA_ADULT_VACCINATION_SERVICES = 'pedia_adult_vaccination_services';

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
                // Consultation
                // ---------------------------------------------------------
            self::CONSULTATION => 'consultation',

                // ---------------------------------------------------------
                // Family Planning Services
                // ---------------------------------------------------------
            self::FAMILY_PLANNING_COUNSELING,
            self::NATURAL_METHODS
            => 'family_planning_service',

                // ---------------------------------------------------------
                // Integrative and Wellness Healthcare Services
                // ---------------------------------------------------------
            self::CHELATION_THERAPY,
            self::MAGNETIC_RESONANCE_ANALYSIS,
            self::MULTIFUNCTIONAL_THERAPEUTIC_SERVICES,
            self::WEIGHT_LOSS_MANAGEMENT,
            self::PSYCHOSOCIAL_AND_SPIRITUAL_COUNSELING
            => 'integrative_and_wellness',

                // ---------------------------------------------------------
                // Maternal & Child Health Services
                // ---------------------------------------------------------
            self::PRE_NATAL_AND_POST_NATAL,
            self::NORMAL_SPONTANEOUS_DELIVERY,
            self::IMMUNIZATION,
            self::EAR_PIERCING,
            self::NEBULIZATION,
            self::FOLEY_CATHETER_INSERTION,
            self::SURGICAL_WOUND_DRESSING,
            self::CORD_DRESSING,
            self::SUTURE_REMOVAL,
            self::ISSUANCE_OF_BC_NEWBORN_SCREENING
            => 'maternal_and_child_health_services',

                // ---------------------------------------------------------
                // Medical / Surgical Services
                // ---------------------------------------------------------
            self::GENERAL_OPD_CONSULTATION,
            self::MEDICAL_OPD_CONSULTATION,
            self::MINOR_SURGICAL_PROCEDURES,
            self::ISSUANCE_OF_MEDICAL_CERTIFICATE,
            self::PEDIA_ADULT_VACCINATION_SERVICES
            => 'medical_surgical_services',
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
            self::CONSULTATION => 'Consultation',
            self::FAMILY_PLANNING_COUNSELING => 'Family Planning Counseling',
            self::NATURAL_METHODS => 'Natural Methods (Rhythm), Pills, Depotrust',
            self::CHELATION_THERAPY => 'Chelation Therapy',
            self::MAGNETIC_RESONANCE_ANALYSIS => 'Magnetic Resonance Analysis',
            self::MULTIFUNCTIONAL_THERAPEUTIC_SERVICES => 'Multifunctional High Potential Therapeutic Services',
            self::WEIGHT_LOSS_MANAGEMENT => 'Weight Loss Management',
            self::PSYCHOSOCIAL_AND_SPIRITUAL_COUNSELING => 'Psychosocial and Spiritual Counseling',
            self::PRE_NATAL_AND_POST_NATAL => 'Pre-Natal and Post-Natal Check Up',
            self::NORMAL_SPONTANEOUS_DELIVERY => 'Normal Spontaneous Delivery',
            self::IMMUNIZATION => 'Immunization - BCG, HEP. B Vaccines, etc.',
            self::EAR_PIERCING => 'Ear Piercing With Hypoallergenic Earrings',
            self::NEBULIZATION => 'Nebulization With and Without Medication',
            self::FOLEY_CATHETER_INSERTION => 'Foley Catheter Insertion',
            self::SURGICAL_WOUND_DRESSING => 'Surgical Wound Dressing',
            self::CORD_DRESSING => 'Cord Dressing',
            self::SUTURE_REMOVAL => 'Suture Removal',
            self::ISSUANCE_OF_BC_NEWBORN_SCREENING => 'Issuance of Birth Certificate; Newborn Screening',
            self::GENERAL_OPD_CONSULTATION => 'General OPD Consultation',
            self::MEDICAL_OPD_CONSULTATION => 'Medical / OPD / Pre-Employment Consultations',
            self::MINOR_SURGICAL_PROCEDURES => 'Minor Surgical Procedures',
            self::ISSUANCE_OF_MEDICAL_CERTIFICATE => 'Issuance of Medical Certificate',
            self::PEDIA_ADULT_VACCINATION_SERVICES => 'Pedia / Adult Immunization / Vaccination Services',
        };
    }

    public function fullDescription(): string
    {
        return $this->serviceLabel().'/'.$this->label();
    }
}
