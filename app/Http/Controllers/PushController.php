<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushController extends Controller
{
    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'endpoint' => 'required|url',
            'p256dh' => 'required|string',
            'auth' => 'required|string',
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint' => $data['endpoint']],
            [
                'user_id' => Auth::id(),
                'p256dh' => $data['p256dh'],
                'auth' => $data['auth'],
            ]
        );

        return response()->json(['status' => 'ok']);
    }

    public function unsubscribe(Request $request)
    {
        $request->validate(['endpoint' => 'required|url']);

        PushSubscription::where('endpoint', $request->endpoint)
            ->where('user_id', Auth::id())
            ->delete();

        return response()->json(['status' => 'ok']);
    }
}
