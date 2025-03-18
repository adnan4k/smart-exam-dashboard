<?php

// database/seeders/SubjectSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run()
    {
        Subject::create(['name' => 'Mathematics']);
        Subject::create(['name' => 'Science']);
        Subject::create(['name' => 'History']);
        Subject::create(['name' => 'Literature']);
        // Add more subjects as needed
    }
}
