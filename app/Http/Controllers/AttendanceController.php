<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('attendance');
    }

    public function store(Request $request)
{
    \Log::info('=== ATTENDANCE STORE CALLED ===');
    
    $request->validate([
        'type' => 'required|in:in,out',
        'photo' => 'required',
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric'
    ]);

    $user = Auth::user();
    
    // Gunakan timezone Asia/Jakarta
    $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');
    $now = Carbon::now('Asia/Jakarta');
    
    \Log::info('Time info:', [
        'today' => $today,
        'now' => $now->format('Y-m-d H:i:s'),
        'timezone' => $now->getTimezone()
    ]);

    // Cek jarak dari kantor
    $officeLat = -6.814598305333564; // Ganti dengan latitude kantor Anda
    $officeLng = 107.15748066568167; // Ganti dengan longitude kantor Anda
    $maxDistance = 2;
    
    $distance = $this->calculateDistance(
        $request->latitude, 
        $request->longitude, 
        $officeLat, 
        $officeLng
    );

    if ($distance > $maxDistance) {
        return response()->json([
            'success' => false,
            'message' => "Anda berada di luar jangkauan {$maxDistance} km dari kantor. Jarak: " . round($distance, 2) . " km. Tidak bisa absen."
        ], 400);
    }

    $attendance = Attendance::where('user_id', $user->id)
        ->where('date', $today)
        ->first();

    if ($request->type == 'in') {
        if ($attendance && $attendance->time_in) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah absen masuk hari ini'
            ], 400);
        }

        // Upload foto
        $photoPath = 'debug_photo_in.jpg'; // Sementara pakai debug

        if (!$attendance) {
            $attendance = new Attendance();
            $attendance->user_id = $user->id;
            $attendance->date = $today;
        }

        // Simpan waktu dengan timezone yang benar
        $attendance->time_in = $now->format('H:i:s');
        $attendance->photo_in = $photoPath;
        $attendance->lat_long_in = $request->latitude . ',' . $request->longitude;
        
        // Cek status masuk - PERUBAHAN DI SINI
        // Jika absen setelah jam 08:00:00 maka statusnya Terlambat
        $jamBatasTelat = Carbon::createFromTimeString('08:00:00', 'Asia/Jakarta');
        $attendance->status_masuk = $now->gt($jamBatasTelat) ? 'Terlambat' : 'Tepat Waktu';

        \Log::info('Absen Masuk:', [
            'time_in' => $attendance->time_in,
            'batas_telat' => $jamBatasTelat->format('H:i:s'),
            'status' => $attendance->status_masuk
        ]);

    } else {
        if (!$attendance || !$attendance->time_in) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum absen masuk hari ini'
            ], 400);
        }

        if ($attendance->time_out) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah absen pulang hari ini'
            ], 400);
        }

        // Upload foto
        $photoPath = 'debug_photo_out.jpg'; // Sementara pakai debug

        $attendance->time_out = $now->format('H:i:s');
        $attendance->photo_out = $photoPath;
        $attendance->lat_long_out = $request->latitude . ',' . $request->longitude;
        
        // Cek status pulang
        $jamPulang = Carbon::createFromTimeString('17:00:00', 'Asia/Jakarta');
        $attendance->status_pulang = $now->lt($jamPulang) ? 'Pulang Cepat' : 'Tepat Waktu';

        \Log::info('Absen Pulang:', [
            'time_out' => $attendance->time_out,
            'status' => $attendance->status_pulang
        ]);
    }

    try {
        $attendance->save();
        \Log::info('=== ATTENDANCE SAVED SUCCESSFULLY ===', [
            'id' => $attendance->id,
            'date' => $attendance->date,
            'time_in' => $attendance->time_in,
            'time_out' => $attendance->time_out,
            'status_masuk' => $attendance->status_masuk
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Absen berhasil dicatat!'
        ]);
    } catch (\Exception $e) {
        \Log::error('Error saving attendance: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Gagal menyimpan absensi'
        ], 500);
    }
}

    // ================================
    // UPLOAD FOTO (diambil dari versi kedua)
    // ================================
    private function uploadPhoto($base64Image, $type)
    {
        $image = preg_replace('#^data:image/\w+;base64,#i', '', $base64Image);
        $image = str_replace(' ', '+', $image);

        $imageName = $type . '_' . time() . '_' . uniqid() . '.jpg';
        $path = 'photos/' . $imageName;

        \Storage::disk('public')->put($path, base64_decode($image));

        return $path;
    }

    // ================================
    // PERHITUNGAN JARAK (dari versi pertama)
    // ================================
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}
