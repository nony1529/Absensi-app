@extends('layouts.app')

@section('page-title', 'Riwayat')
@section('page-subtitle', 'Riwayat absensi dan aktivitas')

@section('content')
<div class="card fade-in-up">
    <div class="card-header">
        <i class="fas fa-history me-2"></i>Riwayat Absensi
    </div>
    <div class="card-body">
        <!-- Filter Section -->
        <div class="row mb-4">
            <div class="col-md-6">
                <form method="GET" action="{{ route('history') }}" id="filterForm">
                    <div class="input-group">
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </form>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center justify-content-end">
                    <span class="me-2">Search:</span>
                    <input type="text" id="searchInput" class="form-control form-control-sm" style="width: auto;" placeholder="Cari tanggal (contoh: 12)">
                </div>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="table-container">
            <table class="table" id="attendanceTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Absen Masuk</th>
                        <th>Absen Pulang</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $index => $attendance)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="date-column">{{ \Carbon\Carbon::parse($attendance->date)->format('d M Y') }}</td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-bold">{{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('H:i:s') : '--:--:--' }}</span>
                                @if($attendance->time_in)
                                    <small class="badge bg-{{ $attendance->status_masuk == 'Tepat Waktu' ? 'success' : 'danger' }} mt-1">
                                        {{ $attendance->status_masuk }}
                                    </small>
                                @else
                                    <small class="badge bg-secondary mt-1">Tidak Absen</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-bold">{{ $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('H:i:s') : '--:--:--' }}</span>
                                @if($attendance->time_out)
                                    <small class="badge bg-{{ $attendance->status_pulang == 'Tepat Waktu' ? 'success' : 'warning' }} mt-1">
                                        {{ $attendance->status_pulang }}
                                    </small>
                                @else
                                    <small class="badge bg-secondary mt-1">Tidak Absen</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($attendance->time_in && $attendance->time_out)
                                <span class="badge bg-success">Lengkap</span>
                            @elseif($attendance->time_in)
                                <span class="badge bg-warning">Hanya Masuk</span>
                            @else
                                <span class="badge bg-secondary">Tidak Absen</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>Menampilkan {{ $attendances->count() }} entri</div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality by date
    document.getElementById('searchInput').addEventListener('input', function() {
        const searchValue = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#attendanceTable tbody tr');
        
        let visibleCount = 0;
        
        rows.forEach(row => {
            const dateCell = row.querySelector('.date-column');
            if (dateCell) {
                const dateText = dateCell.textContent.toLowerCase();
                
                // Cari berdasarkan angka tanggal (1-31)
                const dateMatch = dateText.match(/\b(\d{1,2})\b/);
                const dayNumber = dateMatch ? dateMatch[1] : '';
                
                if (searchValue === '' || dayNumber.includes(searchValue)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            }
        });
        
        // Update counter
        const counterElement = document.querySelector('.d-flex.justify-content-between.align-items-center.mt-3 div');
        if (counterElement) {
            counterElement.textContent = `Menampilkan ${visibleCount} entri`;
        }
    });

    // Pastikan form filter berfungsi dengan baik
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            // Form akan submit secara normal
            console.log('Filter form submitted');
        });
    }
});
</script>
@endsection