<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PackageServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $services = [
            'View up to 180 Contact Details',
            'Unlimited Private Chatting',
            'Basic customer support',
            'Priority Listing',
            'Advance Search Option',
            'Full Profile access',
            'Premium member badge',
            'Matches Suggestions',
            'Send your interest',
            'Trusted badge access',
            'Translation option'
        ];

        foreach ($services as $service) {
            DB::table('package_services')->insert([
                'name' => $service,
                'slug' => Str::slug($service),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
//php artisan db:seed --class=PackageServiceSeeder
