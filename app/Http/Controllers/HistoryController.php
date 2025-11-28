<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get start date from request or default to 30 days ago
        $startDate = $request->get('start_date', Carbon::today()->subDays(30)->format('Y-m-d'));
        
        $attendances = Attendance::where('user_id', $user->id)
            ->where('date', '>=', $startDate)
            ->orderBy('date', 'desc')
            ->get();

        return view('history', compact('attendances', 'startDate'));
    }
}