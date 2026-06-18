<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class FcmService
{
    /**
     * Send Push Notification to a device fcm token
     */
    public function sendPush(string $token, array $data): bool
    {
        Log::info("FCM Notification Request triggered", [
            'token' => $token,
            'payload' => $data
        ]);

        $credentialPath = config('services.fcm.credential_file');
        
        if (empty($credentialPath) || !file_exists($credentialPath)) {
            // Simulated Success fallback for dev environments
            Log::info("FCM SIMULATED SUCCESS: Device Credential File not configured or not found. Logging data: ", $data);
            return true;
        }

        try {
            $accessToken = $this->getGoogleAccessToken($credentialPath);
            $projectId = $this->getProjectId($credentialPath);

            if (!$accessToken || !$projectId) {
                Log::error("Failed to generate Google Access Token or retrieve Project ID.");
                return false;
            }

            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            // FCM v1 message structure
            $payload = [
                'message' => [
                    'token' => $token,
                    'data' => $data,
                    // Android-specific settings to guarantee delivery when backgrounded
                    'android' => [
                        'priority' => 'high',
                        'ttl' => '0s' // Deliver immediately
                    ]
                ]
            ];

            $response = Http::withToken($accessToken)
                ->post($url, $payload);

            if ($response->successful()) {
                Log::info("FCM Push delivered successfully via HTTP v1 API.");
                return true;
            } else {
                Log::error("FCM Push request failed: " . $response->body());
                return false;
            }

        } catch (\Exception $e) {
            Log::error("Error sending FCM Push: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Parse service account file to extract Project ID
     */
    private function getProjectId(string $path): ?string
    {
        $json = json_decode(file_get_contents($path), true);
        return $json['project_id'] ?? null;
    }

    /**
     * Generate Google OAuth2 Access Token from Service Account JSON
     */
    private function getGoogleAccessToken(string $path): ?string
    {
        $json = json_decode(file_get_contents($path), true);
        if (!$json) {
            return null;
        }

        $privateKey = $json['private_key'] ?? null;
        $clientEmail = $json['client_email'] ?? null;

        if (!$privateKey || !$clientEmail) {
            return null;
        }

        // We construct a simple JWT assertion token manually to avoid forcing heavy Google Client libraries
        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        
        $now = time();
        $payload = json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ]);

        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload);

        $signature = '';
        $success = openssl_sign(
            "$base64UrlHeader.$base64UrlPayload",
            $signature,
            $privateKey,
            'SHA256'
        );

        if (!$success) {
            return null;
        }

        $base64UrlSignature = $this->base64UrlEncode($signature);
        $jwt = "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";

        // Request token from Google OAuth2 server
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]);

        if ($response->successful()) {
            return $response->json()['access_token'] ?? null;
        }

        return null;
    }

    private function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
