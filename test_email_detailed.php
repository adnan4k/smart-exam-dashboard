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

// Check From address issue
$fromAddress = config('mail.from.address');
$host = config('mail.mailers.smtp.host');
$username = config('mail.mailers.smtp.username');

echo "⚠ CRITICAL CHECK:\n";
echo "  From Address: $fromAddress\n";
echo "  SMTP Username: $username\n";

if (strpos($fromAddress, 'yourdomain.com') !== false || strpos($fromAddress, 'example.com') !== false) {
    echo "\n✗ PROBLEM FOUND: From address contains placeholder!\n";
    echo "  Current: $fromAddress\n";
    echo "  Should match your domain: noreply@ethioexamhub.com\n";
    echo "\n  Fix this in .env:\n";
    echo "    MAIL_FROM_ADDRESS=noreply@ethioexamhub.com\n";
    echo "    Then run: php artisan config:clear\n\n";
    echo "  This is likely why emails aren't being delivered!\n\n";
} elseif ($fromAddress !== $username) {
    echo "\n⚠ WARNING: From address doesn't match SMTP username\n";
    echo "  This can cause delivery issues.\n";
    echo "  From: $fromAddress\n";
    echo "  Username: $username\n\n";
} else {
    echo "  ✓ From address looks correct\n\n";
}

// Enable detailed error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check From address matches domain
$fromAddress = config('mail.from.address');
$host = config('mail.mailers.smtp.host');
$expectedDomain = str_replace('mail.', '', parse_url('http://' . $host, PHP_URL_HOST));

if (strpos($fromAddress, $expectedDomain) === false && $fromAddress !== 'noreply@ethioexamhub.com') {
    echo "⚠ WARNING: From address ($fromAddress) doesn't match your domain!\n";
    echo "  Expected: noreply@ethioexamhub.com or similar\n";
    echo "  This might cause emails to be rejected or marked as spam.\n\n";
}

try {
    echo "Step 1: Preparing to send email...\n";
    
    // Use the SMTP username as From address if From address is placeholder
    $useFrom = strpos($fromAddress, 'yourdomain.com') !== false || strpos($fromAddress, 'example.com') !== false 
        ? $username 
        : $fromAddress;
    
    if ($useFrom !== $fromAddress) {
        echo "  ⚠ Auto-correcting From address: $fromAddress → $useFrom\n";
        echo "  (Using SMTP username because From address contains placeholder)\n";
    }
    
    echo "\nStep 2: Sending email...\n";
    
    // Enable verbose logging
    Log::info('Email test started', [
        'recipient' => $recipient,
        'host' => config('mail.mailers.smtp.host'),
        'port' => config('mail.mailers.smtp.port'),
        'from' => $useFrom,
        'original_from' => $fromAddress
    ]);
    
    // Send email with corrected from address
    Mail::raw('This is a test email sent at ' . date('Y-m-d H:i:s') . "\n\nIf you receive this, email is working correctly!\n\nThis email was sent using From address: $useFrom", function ($message) use ($recipient, $useFrom) {
        $message->from($useFrom, config('mail.from.name'))
                ->to($recipient)
                ->subject('Test Email - ' . date('Y-m-d H:i:s'));
    });
    
    echo "✓ Laravel reported: Email sent successfully!\n";
    echo "\n";
    echo "═══════════════════════════════════════════════════\n";
    echo "⚠ CRITICAL: You MUST fix your .env file!\n";
    echo "═══════════════════════════════════════════════════\n";
    echo "  1. Edit your .env file\n";
    echo "  2. Change this line:\n";
    echo "     MAIL_FROM_ADDRESS=noreply@ethioexamhub.com\n";
    echo "  3. Run: php artisan config:clear\n";
    echo "  4. Run: php artisan cache:clear\n\n";
    echo "  The From address MUST match your domain for emails to work!\n";
    echo "═══════════════════════════════════════════════════\n\n";
    echo "NOW CHECK:\n";
    echo "  1. ✓ Check Spam/Junk folder (check it NOW!)\n";
    echo "  2. Wait 2-3 minutes and check inbox again\n";
    echo "  3. Check cPanel email logs (Track Delivery)\n";
    echo "  4. Verify noreply@ethioexamhub.com email account exists\n\n";
    echo "If email is in spam folder:\n";
    echo "  - This confirms email is working but needs proper From address\n";
    echo "  - Fix .env file and test again\n";
    
} catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
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

