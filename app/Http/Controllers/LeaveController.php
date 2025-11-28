<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveController extends Controller
{
    // Method untuk izin
    public function izin()
    {
        return $this->getLeaveData('izin');
    }

    // Method untuk sakit
    public function sakit()
    {
        return $this->getLeaveData('sakit');
    }

    // Method untuk cuti
    public function cuti()
    {
        return $this->getLeaveData('cuti');
    }

    // Method umum untuk mengambil data leave
    private function getLeaveData($type)
    {
        $user = Auth::user();
        
        $leaves = Leave::where('user_id', $user->id)
            ->where('type', $type)
            ->orderBy('created_at', 'desc')
            ->get();

        // Tentukan view berdasarkan type
        $view = 'leave.' . $type; // akan menggunakan view leave/izin.blade.php, leave/sakit.blade.php, leave/cuti.blade.php

        return view($view, compact('leaves', 'type'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'type' => 'required|in:izin,cuti,sakit',
            'status' => 'required|in:pending,approved,rejected'
        ]);

        $user = Auth::user();

        // Cek apakah sudah ada pengajuan pada tanggal yang sama dengan type yang sama
        $existingLeave = Leave::where('user_id', $user->id)
            ->where('type', $request->type)
            ->where(function($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                      ->orWhere(function($q) use ($request) {
                          $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                      });
            })
            ->where('id', '!=', $request->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingLeave) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memiliki pengajuan ' . $request->type . ' pada tanggal tersebut'
            ], 400);
        }

        // Jika ada ID, berarti edit existing record
        if ($request->id) {
            $leave = Leave::where('user_id', $user->id)
                        ->where('id', $request->id)
                        ->firstOrFail();
        } else {
            $leave = new Leave();
            $leave->user_id = $user->id;
        }

        $leave->start_date = $request->start_date;
        $leave->end_date = $request->end_date;
        $leave->reason = $request->reason;
        $leave->type = $request->type;
        $leave->status = $request->status;
        $leave->save();

        $action = $request->id ? 'diupdate' : 'dikirim';

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan ' . $request->type . ' berhasil ' . $action
        ]);
    }

    public function edit($id)
    {
        $leave = Leave::where('user_id', Auth::id())->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'leave' => $leave
        ]);
    }

    public function destroy($id)
    {
        $leave = Leave::where('user_id', Auth::id())->findOrFail($id);
        $leave->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan berhasil dihapus'
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected'
        ]);

        $leave = Leave::where('user_id', Auth::id())->findOrFail($id);
        $leave->status = $request->status;
        $leave->save();

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diupdate'
        ]);
    }
}