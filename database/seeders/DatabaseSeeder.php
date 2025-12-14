<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $schema = DB::getSchemaBuilder();
        
        // Create or update admin user
        $adminExists = DB::table('users')->where('email', 'admin@gmail.com')->exists();
        
        if (!$adminExists) {
            $adminData = [
                'name' => 'admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('admin'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            if ($schema->hasColumn('users', 'referral_code')) {
                $adminData['referral_code'] = Str::upper(Str::random(8));
            }
            
            DB::table('users')->insert($adminData);
        }

        // Create or update test user
        $testUserExists = DB::table('users')->where('email', 'test@example.com')->exists();
        
        if (!$testUserExists) {
            $testUserData = [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => Hash::make('password'),
                'role' => 'student',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            if ($schema->hasColumn('users', 'phone')) {
                $testUserData['phone'] = '1234567890';
            }
            
            if ($schema->hasColumn('users', 'phone_number')) {
                $testUserData['phone_number'] = '1234567890';
            }
            
            if ($schema->hasColumn('users', 'referral_code')) {
                // Generate unique referral code
                do {
                    $code = Str::upper(Str::random(8));
                } while (DB::table('users')->where('referral_code', $code)->exists());
                $testUserData['referral_code'] = $code;
            }
            
            DB::table('users')->insert($testUserData);
        }
    }
}
