<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushSubscriptionController extends Controller
{
    /**
     * Update user's push subscription.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $request->validate([
            'endpoint'    => 'required',
            'keys.auth'   => 'required',
            'keys.p256dh' => 'required'
        ]);

        $user = Auth::user();

        if ($user) {
            $user->updatePushSubscription(
                $request->endpoint,
                $request->keys['p256dh'],
                $request->keys['auth']
            );
        }

        return response()->json(['success' => true]);
    }

    /**
     * Delete the user's push subscription.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'endpoint' => 'required'
        ]);

        $user = Auth::user();

        if ($user) {
            $user->deletePushSubscription($request->endpoint);
        }

        return response()->json(['success' => true]);
    }
}
