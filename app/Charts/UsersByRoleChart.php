<?php

namespace App\Charts;

use App\Models\User;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Spatie\Permission\Models\Role;

class UsersByRoleChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build()
    {
        // Map role slugs to readable labels
        $roleLabels = [
            'admin' => 'Administrator',
            'doctor' => 'Doctor',
            'cashier' => 'Cashier',
            'laboratory_staff' => 'Laboratory Staff',
            'patient' => 'Patient',
            'system_admin' => 'System Administrator',
        ];

        // Fetch all roles from DB
        $roles = Role::pluck('name')->toArray();

        $labels = [];
        $counts = [];

        foreach ($roles as $role) {
            $labels[] = $roleLabels[$role] ?? ucfirst(str_replace('_', ' ', $role));
            $counts[] = User::role($role)->count();
        }

        return $this->chart->pieChart()
            ->setTitle('Users by Role')
            ->setSubtitle('Distribution of user roles')
            ->addData($counts)
            ->setLabels($labels)
            ->setColors([
                '#3B82F6', // Admin
                '#22C55E', // Doctor
                '#FACC15', // Cashier
                '#EF4444', // Laboratory Staff
                '#A78BFA', // Patient
                '#F97316', // System Admin
            ])
            ->toVue();
    }
}
