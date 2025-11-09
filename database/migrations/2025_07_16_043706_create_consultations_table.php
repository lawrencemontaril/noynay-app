<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Appointment;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Appointment::class)->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->text('chief_complaints');
            $table->text('assessment');
            $table->text('plan');

            // Vital signs
            $table->unsignedTinyInteger('systolic')->nullable();
            $table->unsignedTinyInteger('diastolic')->nullable();
            $table->unsignedTinyInteger('heart_rate')->nullable();
            $table->unsignedTinyInteger('respiratory_rate')->nullable();
            $table->decimal('weight_kg', 6, 2)->nullable();
            $table->decimal('height_cm', 6, 2)->nullable();
            $table->decimal('temperature_c', 4, 2)->nullable();
            $table->decimal('oxygen_saturation', 5, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
