<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Random created_at within the last 6 months
        $createdAt = fake()->dateTimeBetween('-6 months', 'now');

        // Ensure due_at is AFTER created_at (within 1–30 days)
        $dueAt = Carbon::instance($createdAt)->addDays(fake()->numberBetween(1, 30));

        return [
            'appointment_id' => Appointment::factory(),
            'status' => fake()->randomElement(['unpaid', 'paid', 'cancelled']),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    /**
     * Configure the factory to create related invoice items.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Invoice $invoice) {
            InvoiceItem::factory()
                ->count(rand(1, 5))
                ->create([
                    'invoice_id' => $invoice->id,
                ]);
        });
    }

    /**
     * Optional: state helper for unpaid invoices
     */
    public function unpaid(): static
    {
        return $this->state(fn () => ['status' => 'unpaid']);
    }

    public function paid(): static
    {
        return $this->state(fn () => ['status' => 'paid']);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => 'cancelled']);
    }
}
