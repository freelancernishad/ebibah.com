<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

            // Seed for blood_group
            $bloodGroup = Setting::create(['name' => 'blood_group']);
            $bloodGroup->values()->createMany([
                ['value_id' => '', 'name' => 'All'],
                ['value_id' => 'A+', 'name' => 'A+'],
                ['value_id' => 'A-', 'name' => 'A-'],
                ['value_id' => 'B+', 'name' => 'B+'],
                ['value_id' => 'B-', 'name' => 'B-'],
                ['value_id' => 'O+', 'name' => 'O+'],
                ['value_id' => 'O-', 'name' => 'O-'],
                ['value_id' => 'AB+', 'name' => 'AB+'],
                ['value_id' => 'AB-', 'name' => 'AB-']
            ]);

            // Seed for marital_status
            $maritalStatus = Setting::create(['name' => 'marital_status']);
            $maritalStatus->values()->createMany([
                ['value_id' => '', 'name' => 'All'],
                ['value_id' => '1', 'name' => 'Divorce'],
                ['value_id' => '2', 'name' => 'Married'],
                ['value_id' => '3', 'name' => 'Never Married'],
                ['value_id' => '4', 'name' => 'Single']
            ]);

            // Seed for religions
            $religions = Setting::create(['name' => 'religions']);
            $religions->values()->createMany([
                ['value_id' => '', 'name' => 'All'],
                ['value_id' => '1', 'name' => 'African'],
                ['value_id' => '2', 'name' => 'Buddhism'],
                ['value_id' => '3', 'name' => 'Chinese'],
                ['value_id' => '4', 'name' => 'Christianity'],
                ['value_id' => '5', 'name' => 'Hinduism'],
                ['value_id' => '6', 'name' => 'Islam'],
                ['value_id' => '7', 'name' => 'Judaism'],
                ['value_id' => '8', 'name' => 'Other']
            ]);

            // Seed for profile_for
            $profileFor = Setting::create(['name' => 'profile_for']);
            $profileFor->values()->createMany([
                ['value_id' => '1', 'name' => 'Myself'],
                ['value_id' => '2', 'name' => 'My Son'],
                ['value_id' => '3', 'name' => 'My Daughter'],
                ['value_id' => '4', 'name' => 'My Brother'],
                ['value_id' => '5', 'name' => 'My Sister'],
                ['value_id' => '6', 'name' => 'My Friend'],
                ['value_id' => '7', 'name' => 'My Relative']
            ]);

            // Seed for monthly_income
            $monthlyIncome = Setting::create(['name' => 'monthly_income']);
            $monthlyIncome->values()->createMany([
                ['value_id' => '', 'name' => 'All'],
                ['value_id' => '1', 'name' => '0-3000'],
                ['value_id' => '2', 'name' => '3001-5000'],
                ['value_id' => '3', 'name' => '5001-7000'],
                ['value_id' => '4', 'name' => '7001-10000'],
                ['value_id' => '5', 'name' => '10001-15000'],
                ['value_id' => '6', 'name' => '15001-20000']
            ]);
        }
    }

