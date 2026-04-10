<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // Create shifts (only 2: Pagi and Malam)
        $shifts = [
            [
                'name' => 'Pagi',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'is_night_shift' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Malam',
                'start_time' => '21:00:00',
                'end_time' => '06:00:00',
                'is_night_shift' => true,
                'is_active' => true,
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::create($shift);
        }

        // Create location
        Location::create([
            'name' => 'Kantor Pusat',
            'address' => 'Jl. Contoh No. 123, Jakarta',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        // Create admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'employee_id' => 'EMP001',
            'phone' => '081234567890',
            'position' => 'Administrator',
            'department' => 'IT',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create sample employee
        User::create([
            'name' => 'Karyawan Contoh',
            'email' => 'employee@example.com',
            'password' => Hash::make('password'),
            'employee_id' => 'EMP002',
            'phone' => '081234567891',
            'position' => 'Staff',
            'department' => 'Keuangan',
            'shift_id' => 1,
            'location_id' => 1,
            'role' => 'employee',
            'is_active' => true,
        ]);
    }
}
