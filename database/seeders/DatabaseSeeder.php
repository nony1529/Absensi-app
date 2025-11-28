<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Leave;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create user
        $user = User::create([
            'nip' => '123456',
            'name' => 'Suratman',
            'phone' => '081234567890',
            'jabatan' => 'Staff IT',
            'jam_kerja' => '07:50:59 - 17:00:00',
            'lokasi_penempatan' => 'Kantor Pusat',
            'password' => Hash::make('password'),
        ]);

        // Create sample attendance data for the last 30 days
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();
        
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            // Skip weekends (Saturday and Sunday)
            if ($date->isWeekend()) continue;
            
            // Skip some days randomly (10% chance) to simulate absence
            if (rand(1, 10) > 9) continue;
            
            // Determine time in (90% on time, 10% late)
            $isLate = rand(1, 10) > 9;
            if ($isLate) {
                $timeIn = $date->copy()->setTime(8, rand(0, 30), rand(0, 59)); // Late: after 08:00
                $statusMasuk = 'Terlambat';
            } else {
                $timeIn = $date->copy()->setTime(6, rand(14, 45), rand(0, 59)); // On time: before 07:50
                $statusMasuk = 'Tepat Waktu';
            }
            
            // Determine time out (80% on time, 20% early)
            $isEarlyOut = rand(1, 10) > 8;
            if ($isEarlyOut) {
                $timeOut = $date->copy()->setTime(16, rand(0, 50), rand(0, 59)); // Early: before 17:00
                $statusPulang = 'Pulang Cepat';
            } else {
                $timeOut = $date->copy()->setTime(17, rand(0, 30), rand(0, 59)); // On time: after 17:00
                $statusPulang = 'Tepat Waktu';
            }
            
            // For today, only set time_in if it's before current time
            $today = Carbon::now();
            $currentTime = $today->format('H:i:s');
            
            if ($date->isToday()) {
                // If current time is after the scheduled time_in, set time_in
                if ($today->gt($timeIn)) {
                    $timeInValue = $timeIn->format('H:i:s');
                } else {
                    $timeInValue = null;
                    $statusMasuk = null;
                }
                
                // If current time is after the scheduled time_out, set time_out
                if ($today->gt($timeOut)) {
                    $timeOutValue = $timeOut->format('H:i:s');
                } else {
                    $timeOutValue = null;
                    $statusPulang = null;
                }
            } else {
                $timeInValue = $timeIn->format('H:i:s');
                $timeOutValue = $timeOut->format('H:i:s');
            }
            
            Attendance::create([
                'user_id' => $user->id,
                'date' => $date->format('Y-m-d'),
                'time_in' => $timeInValue,
                'time_out' => $timeOutValue,
                'status_masuk' => $statusMasuk,
                'status_pulang' => $statusPulang,
                'lat_long_in' => $timeInValue ? '-6.2' . rand(10000, 99999) . ',106.8' . rand(10000, 99999) : null,
                'lat_long_out' => $timeOutValue ? '-6.2' . rand(10000, 99999) . ',106.8' . rand(10000, 99999) : null,
            ]);
        }

        // Create sample leave data
        Leave::create([
            'user_id' => $user->id,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->subDays(3),
            'reason' => 'Perlu mengurus administrasi keluarga',
            'type' => 'izin',
            'status' => 'approved'
        ]);

        Leave::create([
            'user_id' => $user->id,
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(7),
            'reason' => 'Cuti tahunan',
            'type' => 'cuti',
            'status' => 'pending'
        ]);

        Leave::create([
            'user_id' => $user->id,
            'start_date' => Carbon::now()->subDays(2),
            'end_date' => Carbon::now()->subDays(2),
            'reason' => 'Sakit demam',
            'type' => 'sakit',
            'status' => 'approved'
        ]);

        // Dalam method run(), tambahkan:
        Leave::create([
            'user_id' => $user->id,
            'start_date' => Carbon::now()->addDays(1),
            'end_date' => Carbon::now()->addDays(2),
            'reason' => 'Perlu mengurus keperluan keluarga',
            'type' => 'izin',
            'status' => 'pending'
        ]);
        
    }
}