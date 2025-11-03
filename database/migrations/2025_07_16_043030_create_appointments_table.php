<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Patient;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Patient::class)->constrained()->cascadeOnDelete();
            $table->text('complaints')->nullable();
            $table->enum('type', [
                'consultation',
                'family_planning_counseling', 'natural_methods',
                'chelation_therapy', 'magnetic_resonance_analysis', 'multifunctional_high_potential_therapeutic_services', 'weight_loss_management', 'psychosocial_and_spiritual_counseling',
                'pregnancy_test', 'papsmear', 'cbc', 'urinalysis', 'fecalysis',
                'pre_natal_and_post_natal', 'normal_spontaneous_delivery', 'immunization', 'ear_pearcing', 'nebulization', 'foley_catheter_insertion', 'surgical_wound_dressing', 'cord_dressing', 'suture_removal', 'issuance_of_bc_newborn_screening',
                'general_opd_consultation', 'medical_opd_consultation', 'minor_surgical_procedures', 'issuance_of_medical_certificate', 'pedia_adult_vaccination_services'
            ]);
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'completed'])->default('pending');
            $table->dateTime('scheduled_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
