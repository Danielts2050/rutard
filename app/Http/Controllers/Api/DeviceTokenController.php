<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserDeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'device_token' => 'required|string|max:500',
            'platform' => 'nullable|string|max:20',
        ]);

        UserDeviceToken::updateOrCreate(
            ['device_token' => $request->device_token],
            [
                'user_id' => $request->user()->id,
                'platform' => $request->platform,
            ]
        );

        return response()->json(['message' => 'Token registrado correctamente.']);
    }

    public function unregister(Request $request): JsonResponse
    {
        $request->validate([
            'device_token' => 'required|string|max:500',
        ]);

        UserDeviceToken::where('device_token', $request->device_token)
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['message' => 'Token eliminado.']);
    }
}
