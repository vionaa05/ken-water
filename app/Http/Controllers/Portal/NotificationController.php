<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->latest()->paginate(15);
        return view('portal.notifications.index', compact('notifications'));
    }

    public function data()
    {
        $count = auth()->user()->notifications()->where('is_read', false)->count();
        return response()->json(['count' => $count]);
    }

    public function markRead(Request $request, Notification $notification)
    {
        if ($notification->user_id === auth()->id()) {
            $notification->update(['is_read' => true]);
            
            // Redirect based on reference
            if ($notification->reference_type === 'order') {
                return redirect()->route('portal.orders.show', $notification->reference_id);
            } elseif ($notification->reference_type === 'complaint') {
                return redirect()->route('portal.complaints.show', $notification->reference_id);
            } elseif ($notification->reference_type === 'reward_redemption') {
                return redirect()->route('portal.rewards.index');
            }
        }
        
        return back();
    }

    public function markAllRead()
    {
        auth()->user()->notifications()->where('is_read', false)->update(['is_read' => true]);
        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }
}
