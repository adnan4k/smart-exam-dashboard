<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

echo "=== Email Testing with Detailed Diagnostics ===\n\n";

// Show configuration
echo "Mail Configuration:\n";
echo "  Driver: " . config('mail.default') . "\n";
echo "  Host: " . config('mail.mailers.smtp.host') . "\n";
echo "  Port: " . config('mail.mailers.smtp.port') . "\n";
echo "  Encryption: " . (config('mail.mailers.smtp.encryption') ?: 'none') . "\n";
echo "  Username: " . (config('mail.mailers.smtp.username') ?: 'NOT SET') . "\n";
echo "  Password: " . (config('mail.mailers.smtp.password') ? '***SET***' : 'NOT SET') . "\n";
echo "  From: " . config('mail.from.address') . "\n";
echo "  Queue Driver: " . config('queue.default') . "\n\n";

// Check if credentials are set
if (empty(config('mail.mailers.smtp.username')) || empty(config('mail.mailers.smtp.password'))) {
    echo "ERROR: SMTP credentials not configured!\n";
    exit(1);
}

$recipient = $argv[1] ?? 'fayomuhe5@gmail.com';
echo "Attempting to send email to: $recipient\n\n";

// Enable detailed error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    echo "Sending email using Laravel Mail facade...\n";
    echo "Note: Laravel may report success even if email fails silently.\n\n";
    
    // Enable verbose logging
    Log::info('Email test started', [
        'recipient' => $recipient,
        'host' => config('mail.mailers.smtp.host'),
        'port' => config('mail.mailers.smtp.port')
    ]);
    
    // Send email
    Mail::raw('This is a test email sent at ' . date('Y-m-d H:i:s') . "\n\nIf you receive this, email is working correctly!", function ($message) use ($recipient) {
        $message->to($recipient)
                ->subject('Test Email - ' . date('Y-m-d H:i:s'));
    });
    
    echo "✓ Laravel reported: Email sent successfully!\n";
    echo "\n";
    echo "IMPORTANT: If you don't receive the email, check:\n";
    echo "  1. Spam/Junk folder\n";
    echo "  2. Laravel logs: storage/logs/laravel.log\n";
    echo "  3. Check if your cPanel email account has sending limits\n";
    echo "  4. Verify SMTP credentials are correct\n";
    echo "  5. Check cPanel email logs for delivery issues\n";
    echo "\n";
    echo "To check logs, run: tail -f storage/logs/laravel.log\n";
    
} catch (\Swift_TransportException $e) {
    echo "✗ SMTP TRANSPORT ERROR:\n";
    echo "  Message: " . $e->getMessage() . "\n";
    echo "\nCommon issues:\n";
    echo "  - Wrong SMTP host or port\n";
    echo "  - Wrong encryption type (try ssl instead of tls or vice versa)\n";
    echo "  - Firewall blocking the connection\n";
    echo "  - Wrong username/password\n";
    echo "  - SMTP server requires authentication on different port\n";
    exit(1);
    
} catch (\Exception $e) {
    echo "✗ ERROR:\n";
    echo "  Type: " . get_class($e) . "\n";
    echo "  Message: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\n";
    echo "Stack trace saved to: storage/logs/laravel.log\n";
    Log::error('Email test failed', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    exit(1);
}

