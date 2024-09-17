<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder2 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Sample data arrays for random assignment
        $maritalStatuses = ['Single', 'Married', 'Divorced', 'Widowed'];
        $genders = ['Male', 'Female', 'Other'];
        $religions = ['Christianity', 'Islam', 'Hinduism', 'Buddhism', 'Atheism'];
        $communities = ['Community A', 'Community B', 'Community C'];
        $motherTongues = ['English', 'Spanish', 'French', 'Hindi', 'Chinese'];
        $professions = ['Engineer', 'Doctor', 'Teacher', 'Lawyer', 'Artist'];
        $countries = ['USA', 'India', 'Canada', 'UK', 'Australia'];
        $states = ['California', 'New York', 'Texas', 'Ontario', 'Queensland'];

        // Create 50 test users
        for ($i = 1; $i <= 50; $i++) {
            // Generate random subsets for partner fields
            $partnerQualifications = array_slice($professions, 0, rand(1, count($professions)));
            $partnerWorkingWith = array_slice($countries, 0, rand(1, count($countries)));
            $partnerProfessions = array_slice($professions, 0, rand(1, count($professions)));

            User::create([
                'active_package_id' => null,
                'name' => 'User ' . $i,
                'email' => 'user' . $i . '@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'role_id' => rand(1, 5),
                'profile_for' => 'Self',
                'profile_created_by' => 'User ' . $i,
                'mobile_number' => '123456789' . $i,
                'whatsapp' => '123456789' . $i,
                'date_of_birth' => now()->subYears(rand(18, 50)),
                'gender' => $genders[array_rand($genders)],
                'first_name' => 'First' . $i,
                'last_name' => 'Last' . $i,
                'father_name' => 'Father' . $i,
                'mother_name' => 'Mother' . $i,
                'marital_status' => $maritalStatuses[array_rand($maritalStatuses)],
                'religion' => $religions[array_rand($religions)],
                'community' => $communities[array_rand($communities)],
                'mother_tongue' => $motherTongues[array_rand($motherTongues)],
                'sub_community' => 'SubCommunity' . $i,
                'nationality' => $countries[array_rand($countries)],
                'highest_qualification' => 'Bachelor\'s Degree',
                'college_name' => 'University' . $i,
                'working_sector' => 'Private',
                'profession' => $professions[array_rand($professions)],
                'profession_details' => 'Professional in ' . $professions[array_rand($professions)],
                'monthly_income' => rand(3000, 10000),
                'father_occupation' => 'Businessman',
                'mother_occupation' => 'Homemaker',
                'living_country' => $countries[array_rand($countries)],
                'currently_living_in' => $states[array_rand($states)],
                'city_living_in' => 'City ' . $i,
                'family_details' => 'Details about family ' . $i,
                'family_values' => 'Traditional',
                'family_location' => 'Location ' . $i,
                'family_type' => 'Joint',
                'family_native_place' => 'NativePlace ' . $i,
                'total_siblings' => rand(1, 5),
                'siblings_married' => rand(0, 3),
                'siblings_not_married' => rand(0, 3),
                'height' => rand(150, 180) . ' cm',
                'birth_place' => 'City ' . $i,
                'personal_values' => 'Moderate',
                'disability' => 'None',
                'posted_by' => 'User',
                'weight' => rand(50, 90) . ' kg',
                'bodyType' => 'Athletic',
                'race' => 'Race ' . $i,
                'blood_group' => 'O+',
                'mother_status' => 'Alive',
                'state' => $states[array_rand($states)],
                'about_myself' => 'About myself ' . $i,
                'partner_age' => rand(25, 35),
                'partner_marital_status' => $maritalStatuses[array_rand($maritalStatuses)],
                'partner_religion' => $religions[array_rand($religions)],
                'partner_community' => $communities[array_rand($communities)],
                'partner_mother_tongue' => $motherTongues[array_rand($motherTongues)],
                'partner_qualification' => json_encode($partnerQualifications),
                'partner_working_with' => json_encode($partnerWorkingWith),
                'partner_profession' => json_encode($partnerProfessions),
                'partner_professional_details' => 'Partner profession details ' . $i,
                'partner_country' => $countries[array_rand($countries)],
                'partner_state' => $states[array_rand($states)],
                'partner_city' => 'Partner City ' . $i,
                'username' => 'user_' . $i,
                'step' => rand(1, 5),
                'smoking' => rand(0, 1) ? 'Yes' : 'No',
                'other_lifestyle_preferences' => 'Lifestyle preferences ' . $i,
                'drinking' => rand(0, 1) ? 'Yes' : 'No',
                'diet' => rand(0, 1) ? 'Veg' : 'Non-Veg',
                'email_verification_hash' => null,
            ]);
        }
    }
}
