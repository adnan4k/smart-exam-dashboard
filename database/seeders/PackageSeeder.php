<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => '1st Semester',
                'slug' => Package::SLUG_SEMESTER_1,
                'description' => 'Unlocks all subjects, questions, notes, and videos for the 1st Semester curriculum.',
                'price' => 300.00,
                'duration_days' => 365,
                'is_active' => true,
                'order' => 1,
            ],
            [
                'name' => '2nd Semester',
                'slug' => Package::SLUG_SEMESTER_2,
                'description' => 'Unlocks all subjects, questions, notes, and videos for the 2nd Semester curriculum.',
                'price' => 300.00,
                'duration_days' => 365,
                'is_active' => true,
                'order' => 2,
            ],
            [
                'name' => 'COC Exam',
                'slug' => Package::SLUG_COC,
                'description' => 'Comprehensive preparation package for Certificate of Competency (COC) and Exit Exams.',
                'price' => 400.00,
                'duration_days' => 365,
                'is_active' => true,
                'order' => 3,
            ],
            [
                'name' => 'All Access',
                'slug' => Package::SLUG_ALL_ACCESS,
                'description' => 'Complete bundle unlocking everything: 1st Semester, 2nd Semester, and COC preparation materials.',
                'price' => 700.00,
                'duration_days' => 365,
                'is_active' => true,
                'order' => 4,
            ],
        ];

        foreach ($packages as $pkg) {
            Package::updateOrCreate(
                ['slug' => $pkg['slug'], 'type_id' => null],
                $pkg
            );
        }
    }
}
