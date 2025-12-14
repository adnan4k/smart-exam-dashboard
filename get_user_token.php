<?php

/**
 * Quick script to get/create token for a specific user
 * Usage: php get_user_token.php 354
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$userId = $argv[1] ?? null;

if (!$userId) {
    echo "Usage: php get_user_token.php {user_id}\n";
    echo "Example: php get_user_token.php 354\n";
    exit(1);
}

$user = \App\Models\User::find($userId);

if (!$user) {
    echo "User with ID {$userId} not found.\n";
    exit(1);
}

// Create a new token
$token = $user->createToken('manual_token_' . now()->format('Y-m-d_H-i-s'))->plainTextToken;

echo "\n";
echo "========================================\n";
echo "User: {$user->name} (ID: {$user->id})\n";
echo "Email: {$user->email}\n";
echo "Phone: {$user->phone_number}\n";
echo "========================================\n";
echo "Token: {$token}\n";
echo "========================================\n";
echo "\n";
echo "Use this token in Authorization header:\n";
echo "Authorization: Bearer {$token}\n";
echo "\n";

