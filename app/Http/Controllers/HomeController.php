<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today('Asia/Jakarta');
        
        $selectedMonth = (int)$request->input('month', $today->month);
        $selectedYear = (int)$request->input('year', $today->year);
        
        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = $today->month;
        }
        
        if ($selectedYear < 2020 || $selectedYear > 2030) {
            $selectedYear = $today->year;
        }
        
        $attendanceToday = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today->format('Y-m-d'))
            ->first();

        // Format waktu di controller
        $formattedTimeIn = $this->formatTime($attendanceToday->time_in ?? null);
        $formattedTimeOut = $this->formatTime($attendanceToday->time_out ?? null);

        // Ambil data 1 minggu terakhir dan format waktunya
        $lastWeekAttendancesData = Attendance::where('user_id', $user->id)
            ->where('date', '>=', $today->copy()->subDays(7))
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        // Format waktu untuk setiap attendance
        $lastWeekAttendances = $lastWeekAttendancesData->map(function($attendance) {
            return [
                'date' => $attendance->date,
                'time_in' => $attendance->time_in,
                'time_out' => $attendance->time_out,
                'formatted_time_in' => $this->formatTime($attendance->time_in),
                'formatted_time_out' => $this->formatTime($attendance->time_out),
            ];
        });

        $hadir = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $selectedMonth)
            ->whereYear('date', $selectedYear)
            ->whereNotNull('time_in')
            ->count();

        $izin = Leave::where('user_id', $user->id)
            ->where('type', 'izin')
            ->where('status', 'approved')
            ->whereMonth('start_date', $selectedMonth)
            ->whereYear('start_date', $selectedYear)
            ->count();

        $sakit = Leave::where('user_id', $user->id)
            ->where('type', 'sakit')
            ->where('status', 'approved')
            ->whereMonth('start_date', $selectedMonth)
            ->whereYear('start_date', $selectedYear)
            ->count();

        $cuti = Leave::where('user_id', $user->id)
            ->where('type', 'cuti')
            ->where('status', 'approved')
            ->whereMonth('start_date', $selectedMonth)
            ->whereYear('start_date', $selectedYear)
            ->count();

        $terlambat = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $selectedMonth)
            ->whereYear('date', $selectedYear)
            ->where('status_masuk', 'Terlambat')
            ->count();

        return view('home', compact(
            'user', 
            'attendanceToday', 
            'hadir', 
            'izin', 
            'sakit', 
            'cuti',
            'terlambat', 
            'lastWeekAttendances',
            'selectedMonth',
            'selectedYear',
            'formattedTimeIn',
            'formattedTimeOut'
        ));
    }

    /**
     * Format waktu dari berbagai format ke format H:i:s
     */
    private function formatTime($time)
    {
        if (empty($time)) {
            return null;
        }

        // Jika sudah dalam format H:i:s, langsung return
        if (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $time)) {
            return $time;
        }
        
        // Jika dalam format datetime, ambil bagian waktu saja
        if (preg_match('/^\d{4}-\d{2}-\d{2} (\d{1,2}:\d{2}:\d{2})$/', $time, $matches)) {
            return $matches[1];
        }
        
        // Untuk format lainnya, ambil 8 karakter pertama
        return substr($time, 0, 8);
    }
}