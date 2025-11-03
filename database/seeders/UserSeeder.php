<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    protected static $password = '@Super123';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'role' => 'admin',
                'first_name' => 'Administrative',
                'last_name' => 'Staff',
                'middle_name' => null,
                'email' => 'admin@kld.edu.ph',
                'is_active' => true,
            ],
            [
                'role' => 'system_admin',
                'first_name' => 'System',
                'last_name' => 'Admin',
                'middle_name' => null,
                'email' => 'system_admin@kld.edu.ph',
                'is_active' => true,
            ],
            [
                'role' => 'laboratory_staff',
                'first_name' => 'Laboratory',
                'last_name' => 'Staff',
                'middle_name' => null,
                'email' => 'laboratory_staff@kld.edu.ph',
                'is_active' => true,
            ],
            [
                'role' => 'cashier',
                'first_name' => 'Cashier',
                'last_name' => 'Staff',
                'middle_name' => null,
                'email' => 'cashier_staff@kld.edu.ph',
                'is_active' => true,
            ],
            [
                'role' => 'doctor',
                'first_name' => 'Doctor',
                'last_name' => 'Staff',
                'middle_name' => null,
                'email' => 'doctor_staff@kld.edu.ph',
                'is_active' => true,
            ],
            [
                'role' => 'patient',
                'first_name' => 'Patient',
                'last_name' => 'One',
                'middle_name' => null,
                'email' => 'patient_1@kld.edu.ph',
                'is_active' => true,
                'patient' => [
                    'gender' => 'male',
                    'civil_status' => 'single',
                    'birthdate' => '2001-01-01',
                    'contact_number' => '09123123123',
                    'address' => 'Kolehiyo ng Lungsod ng Dasmarinas',
                ],
            ],
            [
                'role' => 'patient',
                'first_name' => 'Patient',
                'last_name' => 'Two',
                'middle_name' => null,
                'email' => 'patient_2@kld.edu.ph',
                'is_active' => true,
                'patient' => [
                    'gender' => 'female',
                    'civil_status' => 'single',
                    'birthdate' => '2002-02-02',
                    'contact_number' => '09123123123',
                    'address' => 'Kolehiyo ng Lungsod ng Dasmarinas',
                ],
            ],
            [
                'role' => 'patient',
                'first_name' => 'Patient',
                'last_name' => 'Three',
                'middle_name' => null,
                'email' => 'patient_3@kld.edu.ph',
                'is_active' => true,
                'patient' => [
                    'gender' => 'male',
                    'civil_status' => 'single',
                    'birthdate' => '1995-03-03',
                    'contact_number' => '09123123123',
                    'address' => 'Kolehiyo ng Lungsod ng Dasmarinas',
                ],
            ],
            [
                'role' => 'patient',
                'first_name' => 'Patient',
                'last_name' => 'Four',
                'middle_name' => null,
                'email' => 'patient_4@kld.edu.ph',
                'is_active' => true,
                'patient' => [
                    'gender' => 'male',
                    'civil_status' => 'single',
                    'birthdate' => '1993-04-04',
                    'contact_number' => '09123123123',
                    'address' => 'Kolehiyo ng Lungsod ng Dasmarinas',
                ],
            ],
            [
                'role' => 'patient',
                'first_name' => 'Patient',
                'last_name' => 'Five',
                'middle_name' => null,
                'email' => 'patient_5@kld.edu.ph',
                'is_active' => true,
                'patient' => [
                    'gender' => 'male',
                    'civil_status' => 'married',
                    'birthdate' => '1997-03-02',
                    'contact_number' => '09123123123',
                    'address' => 'Kolehiyo ng Lungsod ng Dasmarinas',
                ],
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'middle_name' => $userData['middle_name'],
                    'is_active' => $userData['is_active'],
                    'password' => static::$password,
                ]
            );

            $user->assignRole($userData['role']);

            if (isset($userData['patient'])) {
                $user->patient()->create([
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'middle_name' => $userData['middle_name'],
                    'gender' => $userData['patient']['gender'],
                    'civil_status' => $userData['patient']['civil_status'],
                    'birthdate' => $userData['patient']['birthdate'],
                    'contact_number' => $userData['patient']['contact_number'],
                    'address' => $userData['patient']['address'],
                ]);
            }
        }
    }
}
