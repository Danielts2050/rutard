<?php

namespace App\Services;

use App\Models\UserDeviceToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    public function sendToUser(int $userId, string $title, string $body, array $data = []): bool
    {
        $tokens = UserDeviceToken::where('user_id', $userId)->pluck('device_token');

        if ($tokens->isEmpty()) {
            return false;
        }

        $success = false;
        foreach ($tokens as $token) {
            if ($this->send($token, $title, $body, $data)) {
                $success = true;
            }
        }

        return $success;
    }

    public function sendToAllDrivers(string $title, string $body, array $data = []): bool
    {
        $tokens = UserDeviceToken::whereHas('user', fn($q) => $q->whereHas('role', fn($r) => $r->where('name', 'Chofer')))
            ->pluck('device_token');

        if ($tokens->isEmpty()) {
            return false;
        }

        $success = false;
        foreach ($tokens as $token) {
            if ($this->send($token, $title, $body, $data)) {
                $success = true;
            }
        }

        return $success;
    }

    public function send(string $deviceToken, string $title, string $body, array $data = []): bool
    {
        $serverKey = config('alertas.fcm.server_key');

        if (empty($serverKey) || !config('alertas.fcm.enabled')) {
            Log::debug('FCM: skip (server_key empty or disabled)', [
                'title' => $title,
                'device' => substr($deviceToken, 0, 20) . '...',
            ]);
            return false;
        }

        $payload = [
            'to' => $deviceToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
            ],
            'data' => array_merge($data, ['click_action' => 'FLUTTER_NOTIFICATION_CLICK']),
            'priority' => 'high',
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => "key={$serverKey}",
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', $payload);

            if (!$response->successful()) {
                Log::warning('FCM: send failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            $result = $response->json();
            $successCount = $result['success'] ?? 0;

            if ($successCount === 0) {
                Log::warning('FCM: delivery failed', ['result' => $result]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('FCM: exception', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
