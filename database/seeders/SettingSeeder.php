<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::firstOrCreate(
            [],
            [
                'max_appointments_per_slot' => 5,
                'vat_percent' => 12,
                'special_discount_percent' => 20,
            ]
        );
    }
}
