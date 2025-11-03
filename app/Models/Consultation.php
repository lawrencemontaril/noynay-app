<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\ConsultationObserver;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(ConsultationObserver::class)]
class Consultation extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'appointment_id',
        'type',
        'chief_complaints',
        'assessment',
        'plan',
        'respiratory_rate',
        'systolic',
        'diastolic',
        'heart_rate',
        'weight_kg',
        'height_cm',
        'bmi',
        'temperature_c',
        'oxygen_saturation',
    ];

    public const TYPE_LABELS = [
        'consultation' => 'Consultation',
        'family_planning_counseling' => 'Family Planning Counseling',
        'natural_methods' => 'Natural Methods (Rhythm), Pills, Depotrust',
        'chelation_therapy' => 'Chelation Therapy',
        'magnetic_resonance_analysis' => 'Magnetic Resonance Analysis',
        'multifunctional_high_potential_therapeutic_services' => 'Multifunctional High Potential Therapeutic Services',
        'weight_loss_management' => 'Weight Loss Management',
        'psychosocial_and_spiritual_counseling' => 'Psychosocial and Spiritual Counseling',
        'pre_natal_and_post_natal' => 'Pre-Natal and Post-Natal Check Up',
        'normal_spontaneous_delivery' => 'Normal Spontaneous Delivery',
        'immunization' => 'Immunization - BCG, HEP. B Vaccines, etc.',
        'ear_pearcing' => 'Ear Piercing With Hypoallergenic Earrings',
        'nebulization' => 'Nebulization With and Without Medication',
        'foley_catheter_insertion' => 'Foley Catheter Insertion',
        'surgical_wound_dressing' => 'Surgical Wound Dressing',
        'cord_dressing' => 'Cord Dressing',
        'suture_removal' => 'Suture Removal',
        'issuance_of_bc_newborn_screening' => 'Issuance of Birth Certificate; Newborn Screening',
        'general_opd_consultation' => 'General OPD Consultation',
        'medical_opd_consultation' => 'Medical / OPD / Pre-Employment Consultations',
        'minor_surgical_procedures' => 'Minor Surgical Procedures',
        'issuance_of_medical_certificate' => 'Issuance of Medical Certificate',
        'pedia_adult_vaccination_services' => 'Pedia / Adult Immunization / Vaccination Services',
    ];

    /*
    |--------------------------------------------------------------------------
    | Accessors/Mutators
    |--------------------------------------------------------------------------
    */
    protected function typeLabel(): Attribute
    {
        return Attribute::get(fn () =>
            self::TYPE_LABELS[$this->type]
            ?? ucfirst(str_replace('_', ' ', $this->type))
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    #[Scope]
    protected function searchPatient(Builder $query, ?string $keyword = '')
    {
        $query->whereHas('appointment.patient', fn ($q) => $q->search($keyword));
    }
}
