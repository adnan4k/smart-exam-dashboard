<?php

// database/seeders/SubjectSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Type;

class SubjectSeeder extends Seeder
{
    public function run()
    {
        // Get a type if available, otherwise use null
        $type = Type::first();
        
        $subjects = [
            [
                'name' => 'Mathematics',
                'year' => '2024',
                'type_id' => $type ? $type->id : null,
                'default_duration' => 60, // 60 minutes
            ],
            [
                'name' => 'Science',
                'year' => '2024',
                'type_id' => $type ? $type->id : null,
                'default_duration' => 60,
            ],
            [
                'name' => 'History',
                'year' => '2024',
                'type_id' => $type ? $type->id : null,
                'default_duration' => 45,
            ],
            [
                'name' => 'Literature',
                'year' => '2024',
                'type_id' => $type ? $type->id : null,
                'default_duration' => 45,
            ],
        ];

        foreach ($subjects as $subjectData) {
            // Only create if subject with same name and year doesn't exist
            $exists = Subject::where('name', $subjectData['name'])
                ->where('year', $subjectData['year'])
                ->exists();
            
            if (!$exists) {
                Subject::create($subjectData);
            }
        }
    }
}
