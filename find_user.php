<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$searchName = $argv[1] ?? 'faysal';

echo "Searching for users with name containing: {$searchName}\n\n";

$users = \App\Models\User::where('name', 'like', "%{$searchName}%")
    ->orWhere('email', 'like', "%{$searchName}%")
    ->orWhere('phone_number', 'like', "%{$searchName}%")
    ->select('id', 'name', 'email', 'phone_number')
    ->get();

if ($users->isEmpty()) {
    echo "No users found.\n";
} else {
    echo "Found " . $users->count() . " user(s):\n";
    echo str_repeat("=", 60) . "\n";
    foreach ($users as $user) {
        echo "ID: {$user->id}\n";
        echo "Name: {$user->name}\n";
        echo "Email: " . ($user->email ?? 'N/A') . "\n";
        echo "Phone: " . ($user->phone_number ?? 'N/A') . "\n";
        echo str_repeat("-", 60) . "\n";
    }
}

