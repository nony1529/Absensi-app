@extends('layouts.app')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan aktivitas dan statistik absensi Anda')

@section('content')
<div class="stats-grid">
    <div class="stat-card fade-in-up stat-primary">
        <div class="stat-icon">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-number">{{ $hadir }}</div>
        <div class="stat-label">Hadir</div>
    </div>
    <div class="stat-card fade-in-up stat-info">
        <div class="stat-icon">
            <i class="fas fa-file-alt"></i>
        </div>
        <div class="stat-number">{{ $izin }}</div>
        <div class="stat-label">Izin</div>
    </div>
    <div class="stat-card fade-in-up stat-warning">
        <div class="stat-icon">
            <i class="fas fa-heartbeat"></i>
        </div>
        <div class="stat-number">{{ $sakit }}</div>
        <div class="stat-label">Sakit</div>
    </div>
    <div class="stat-card fade-in-up stat-success">
        <div class="stat-icon">
            <i class="fas fa-umbrella-beach"></i>
        </div>
        <div class="stat-number">{{ $cuti }}</div>
        <div class="stat-label">Cuti</div>
    </div>
    <div class="stat-card fade-in-up stat-danger">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-number">{{ $terlambat }}</div>
        <div class="stat-label">Terlambat</div>
    </div>
</div>

<!-- Absen Hari Ini -->
<div class="card fade-in-up">
    <div class="card-header">
        <i class="fas fa-clock me-2"></i>Absensi Hari Ini
    </div>
    <div class="card-body">
        <div class="attendance-today">
            <div class="attendance-card">
                <i class="fas fa-sign-in-alt fa-2x text-success mb-3"></i>
                <h5>Masuk</h5>
                <div class="attendance-time">
                    {{ $formattedTimeIn ?? '--:--:--' }}
                </div>
                <p class="text-muted">Waktu absen masuk</p>
            </div>
            <div class="attendance-card">
                <i class="fas fa-sign-out-alt fa-2x text-warning mb-3"></i>
                <h5>Pulang</h5>
                <div class="attendance-time">
                    {{ $formattedTimeOut ?? '--:--:--' }}
                </div>
                <p class="text-muted">Waktu absen pulang</p>
            </div>
        </div>
    </div>
</div>

<!-- 1 Minggu Terakhir -->
<div class="card fade-in-up">
    <div class="card-header">
        <i class="fas fa-history me-2"></i>Riwayat 1 Minggu Terakhir
    </div>
    <div class="card-body">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lastWeekAttendances as $attendance)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($attendance['date'])->format('d M Y') }}</td>
                        <td>{{ $attendance['formatted_time_in'] ?? '-' }}</td>
                        <td>{{ $attendance['formatted_time_out'] ?? '-' }}</td>
                        <td>
                            @if($attendance['time_in'] && $attendance['time_out'])
                                <span class="badge bg-success">Lengkap</span>
                            @elseif($attendance['time_in'])
                                <span class="badge bg-warning">Hanya Masuk</span>
                            @else
                                <span class="badge bg-secondary">Tidak Absen</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            <i class="fas fa-calendar-times fa-2x mb-2"></i>
                            <p>Tidak ada data absensi</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Filter Bulan -->
<div class="card fade-in-up">
    <div class="card-header">
        <i class="fas fa-filter me-2"></i>Filter Statistik Bulanan
    </div>
    <div class="card-body">
        <form method="GET" action="{{ url()->current() }}" class="row g-3 align-items-center">
            <div class="col-md-5">
                <label class="form-label">Bulan</label>
                <select name="month" class="form-select" id="monthSelect">
                    @foreach(range(1, 12) as $month)
                        <option value="{{ $month }}" {{ $selectedMonth === $month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::createFromDate(now()->year, $month, 1)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">Tahun</label>
                <select name="year" class="form-select" id="yearSelect">
                    @foreach(range(now()->year - 1, now()->year + 1) as $year)
                        <option value="{{ $year }}" {{ $selectedYear === $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">Terapkan</button>
            </div>
        </form>
    </div>
</div>
@endsection