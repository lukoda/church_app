<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Jumuiya;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function announcement(Request $request)
    {
        $announcements = Announcement::where('status', 'active')->get();

        $announcement_details = [];


        foreach ($announcements as $key => $announcement) {
            if ($announcement->level == 'church') {
                if (str_contains($announcement->church, $request->user()->church->name)) {
                    $announcement_details [] = response()->json([
                        'document' => isset($announcement->document) ? : $announcement->document,
                        'message'  => isset($announcement->message) ? : $announcement->message,
                        'begin_date' => $announcement->begin_date,
                        'end_date' => $announcement->end_date,
                        'duration' => $announcement->duration,
                        'status' => $announcement->status,
                    ]);

                    return $announcement_details;
                }
            }elseif ($announcement->level == 'jumuiya') {
                $churches = Jumuiya::where('church_id', $request->user()->church_id)->where('status', 'active')->pluck('name');
                // return gettype($churches);
                if (str_contains($announcement->jumuiya, $churches)) {
                    return 'ok';
                }
            }
        }
    }
}
