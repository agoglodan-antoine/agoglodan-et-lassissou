<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/** Centre de notifications de l'utilisateur connecté (NOTIFICATIONS). */
class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()->notifications_elevconnect()
            ->latest('date_creation')
            ->paginate(20);

        $request->user()->notifications_elevconnect()->where('lu', false)->update(['lu' => true]);

        return view('notifications.index', compact('notifications'));
    }
}
