<?php

namespace App\Http\Controllers\JobSeeker;

use App\Http\Controllers\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function details($id){
        $notification = Notification::find($id);

        if ($notification) {
            $notification->status = 'read';
            $notification->read_at = now();
            $notification->save();
        }
        $decodedObject = json_decode($notification->data);
        
        return view('jobseeker.dashboard.notification_detail', compact('decodedObject'));
    }
}