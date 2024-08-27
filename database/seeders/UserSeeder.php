<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 50; $i++) {
            User::create([
                'active_package_id' => $faker->numberBetween(1, 5),
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password'), // default password
                'role' => $faker->randomElement(['admin', 'user']),
                'role_id' => $faker->numberBetween(1, 3),
                'profile_for' => $faker->randomElement(['Self', 'Son/Daughter', 'Friend']),
                'profile_created_by' => $faker->name,
                'mobile_number' => $faker->phoneNumber,
                'whatsapp' => $faker->phoneNumber,
                'date_of_birth' => $faker->date(),
                'gender' => $faker->randomElement(['Male', 'Female']),
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'father_name' => $faker->name('male'),
                'mother_name' => $faker->name('female'),
                'marital_status' => $faker->randomElement(['Single', 'Married', 'Divorced', 'Widowed']),
                'religion' => $faker->randomElement(['Hindu', 'Muslim', 'Christian', 'Buddhist']),
                'community' => $faker->word,
                'mother_tongue' => $faker->languageCode,
                'sub_community' => $faker->word,
                'nationality' => $faker->country,
                'highest_qualification' => $faker->randomElement(['Bachelor', 'Master', 'PhD']),
                'college_name' => $faker->company,
                'working_sector' => $faker->randomElement(['IT', 'Finance', 'Education', 'Health']),
                'profession' => $faker->jobTitle,
                'profession_details' => $faker->sentence,
                'monthly_income' => $faker->numberBetween(1000, 10000),
                'father_occupation' => $faker->jobTitle,
                'mother_occupation' => $faker->jobTitle,
                'living_country' => $faker->country,
                'currently_living_in' => $faker->city,
                'city_living_in' => $faker->city,
                'family_details' => $faker->sentence,
                'family_values' => $faker->randomElement(['Traditional', 'Moderate', 'Liberal']),
                'family_location' => $faker->address,
                'family_type' => $faker->randomElement(['Joint', 'Nuclear']),
                'family_native_place' => $faker->city,
                'total_siblings' => $faker->numberBetween(0, 5),
                'siblings_married' => $faker->numberBetween(0, 3),
                'siblings_not_married' => $faker->numberBetween(0, 3),
                'height' => $faker->randomFloat(2, 4.5, 6.5), // height in feet
                'birth_place' => $faker->city,
                'personal_values' => $faker->word,
                'disability' => $faker->randomElement(['None', 'Visual', 'Hearing', 'Physical']),
                'posted_by' => $faker->name,
                'weight' => $faker->numberBetween(50, 100), // weight in kg
                'bodyType' => $faker->randomElement(['Slim', 'Average', 'Athletic']),
                'race' => $faker->randomElement(['Asian', 'Caucasian', 'African', 'Latino']),
                'blood_group' => $faker->randomElement(['A+', 'B+', 'AB+', 'O+']),
                'mother_status' => $faker->randomElement(['Alive', 'Deceased']),
                'state' => $faker->state,
                'about_myself' => $faker->paragraph,
                'partner_age' => $faker->numberBetween(20, 40),
                'partner_marital_status' => $faker->randomElement(['Single', 'Married', 'Divorced', 'Widowed']),
                'partner_religion' => $faker->randomElement(['Hindu', 'Muslim', 'Christian', 'Buddhist']),
                'partner_community' => $faker->word,
                'partner_mother_tongue' => $faker->languageCode,
                'partner_qualification' => $faker->randomElement(['Bachelor', 'Master', 'PhD']), // Ensure this matches the column type
                'partner_working_with' => $faker->randomElement(['Private', 'Government', 'Self-employed']),
                'partner_profession' => $faker->jobTitle,
                'partner_professional_details' => $faker->sentence,
            ]);
        }
    }
}



// php artisan db:seed --class=UserSeeder
