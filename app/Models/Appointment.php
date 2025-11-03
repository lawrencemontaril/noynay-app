<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\AppointmentObserver;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ObservedBy(AppointmentObserver::class)]
class Appointment extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'patient_id',
        'complaints',
        'type',
        'status',
        'scheduled_at',
    ];
    
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'is_reschedulable' => 'boolean',
            'is_cancellable' => 'boolean',
            'is_operatable' => 'boolean',
        ];
    }

    public const TYPE_LABELS = [
        'pregnancy_test' => 'Pregnancy Test',
        'papsmear' => 'Papsmear',
        'cbc' => 'Complete Blood Count',
        'urinalysis' => 'Urinalysis',
        'fecalysis' => 'Fecalysis',
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
    protected function scheduledAt(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Carbon::parse($value)
                ->timezone('Asia/Manila')
        );
    }

    protected function typeLabel(): Attribute
    {
        return Attribute::get(fn () =>
            self::TYPE_LABELS[$this->type]
            ?? ucfirst(str_replace('_', ' ', $this->type))
        );
    }

    protected function isReschedulable(): Attribute
    {
        return Attribute::get(function () {
            return in_array($this->status, ['pending', 'approved'])
                && $this->scheduled_at->greaterThanOrEqualTo(now()->addDay());
        });
    }

    protected function isCancellable(): Attribute
    {
        return Attribute::get(function () {
            return in_array($this->status, ['pending', 'approved'])
                && $this->scheduled_at->greaterThanOrEqualTo(now()->addDay());
        });
    }

    protected function isOperatable(): Attribute
    {
        return Attribute::get(
            fn () => in_array($this->status, ['approved', 'completed']) && $this->invoice()->exists()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    public function laboratoryResults(): HasMany
    {
        return $this->hasMany(LaboratoryResult::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    #[Scope]
    protected function searchPatient(Builder $query, ?string $keyword = '')
    {
        $query->whereHas('patient', fn ($q) => $q->search($keyword));
    }
}
