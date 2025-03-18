<?php

// database/seeders/TypeSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Type;

class TypeSeeder extends Seeder
{
    public function run()
    {
        Type::create(['name' => 'Multiple Choice']);
        Type::create(['name' => 'True/False']);
        Type::create(['name' => 'Short Answer']);
        // Add more types as needed
    }
}
