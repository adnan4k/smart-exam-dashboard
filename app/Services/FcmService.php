<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    private string $projectId;
    private array $serviceAccount;
    private const FCM_SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';
    private const TOKEN_CACHE_KEY = 'fcm_access_token';
    private const TOKEN_CACHE_TTL = 3300; // 55 minutes (tokens expire in 1 hour)

    public function __construct()
    {
        $this->projectId = config('services.fcm.project_id');
        $serviceAccountPath = config('services.fcm.service_account_path');

        if (!$serviceAccountPath || !file_exists($serviceAccountPath)) {
            throw new \Exception('FCM service account file not found at: ' . $serviceAccountPath);
        }

        $this->serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);

        if (!$this->serviceAccount) {
            throw new \Exception('Invalid FCM service account JSON file');
        }
    }

    /**
     * Get OAuth 2.0 access token for FCM HTTP v1 API
     */
    private function getAccessToken(): string
    {
        return Cache::remember(self::TOKEN_CACHE_KEY, self::TOKEN_CACHE_TTL, function () {
            $now = time();
            $exp = $now + 3600; // Token expires in 1 hour

            $header = json_encode([
                'alg' => 'RS256',
                'typ' => 'JWT',
            ]);

            $payload = json_encode([
                'iss' => $this->serviceAccount['client_email'],
                'sub' => $this->serviceAccount['client_email'],
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $exp,
                'scope' => self::FCM_SCOPE,
            ]);

            $base64UrlHeader = $this->base64UrlEncode($header);
            $base64UrlPayload = $this->base64UrlEncode($payload);

            $signatureInput = $base64UrlHeader . '.' . $base64UrlPayload;

            // Sign with private key
            $privateKey = openssl_pkey_get_private($this->serviceAccount['private_key']);
            openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
            openssl_free_key($privateKey);

            $base64UrlSignature = $this->base64UrlEncode($signature);
            $jwt = $signatureInput . '.' . $base64UrlSignature;

            // Exchange JWT for access token
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if (!$response->successful()) {
                Log::error('FCM OAuth token request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Failed to obtain FCM access token');
            }

            return $response->json('access_token');
        });
    }

    /**
     * Base64 URL encode
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Send notification to a topic
     */
    public function sendToTopic(string $topic, array $data, ?array $notification = null): bool
    {
        try {
            $accessToken = $this->getAccessToken();

            $message = [
                'topic' => $topic,
                'data' => $data,
            ];

            // Add notification payload if provided (displays as system notification)
            if ($notification) {
                $message['notification'] = $notification;
            }

            $payload = ['message' => $message];

            $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            if ($response->successful()) {
                Log::info('FCM notification sent successfully to topic', [
                    'topic' => $topic,
                    'message_id' => $response->json('name'),
                ]);
                return true;
            }

            Log::error('FCM notification failed', [
                'topic' => $topic,
                'status' => $response->status(),
                'error' => $response->json(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('FCM sendToTopic exception', [
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send notification to multiple tokens (for direct targeting without topics)
     * Note: Limited to 500 tokens per request
     */
    public function sendToTokens(array $tokens, array $data, ?array $notification = null): array
    {
        $results = [
            'success' => 0,
            'failure' => 0,
            'errors' => [],
        ];

        // FCM HTTP v1 doesn't support batch sending to multiple tokens in one request
        // We need to send individual messages or use topics
        // For better performance, recommend using topics instead

        foreach ($tokens as $token) {
            try {
                $accessToken = $this->getAccessToken();

                $message = [
                    'token' => $token,
                    'data' => $data,
                ];

                if ($notification) {
                    $message['notification'] = $notification;
                }

                $payload = ['message' => $message];
                $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ])->post($url, $payload);

                if ($response->successful()) {
                    $results['success']++;
                } else {
                    $results['failure']++;
                    $results['errors'][] = [
                        'token' => $token,
                        'error' => $response->json(),
                    ];
                }
            } catch (\Exception $e) {
                $results['failure']++;
                $results['errors'][] = [
                    'token' => $token,
                    'error' => $e->getMessage(),
                ];
            }
        }

        Log::info('FCM batch send completed', [
            'total' => count($tokens),
            'success' => $results['success'],
            'failure' => $results['failure'],
        ]);

        return $results;
    }

    /**
     * Get topic name for exam type
     */
    public static function getTopicName(int $typeId): string
    {
        return (string)$typeId;
    }
}
