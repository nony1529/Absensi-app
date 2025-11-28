@extends('layouts.app')

@section('page-title', 'Cuti')
@section('page-subtitle', 'Pengajuan cuti')

@section('content')
<div class="card fade-in-up">
    <div class="card-header">
        <i class="fas fa-umbrella-beach me-2"></i>Data Permohonan Cuti
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-1">Daftar Permohonan Cuti</h5>
                <p class="text-muted mb-0">Kelola permohonan cuti Anda</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus me-2"></i>Ajukan Cuti
            </button>
        </div>

        <!-- Filter Section -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select class="form-control" id="statusFilter">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Disetujui</option>
                    <option value="rejected">Ditolak</option>
                </select>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaves as $index => $leave)
                    <tr data-status="{{ $leave->status }}">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</td>
                        <td>{{ Str::limit($leave->reason, 30) }}</td>
                        <td>
                            <select class="form-select form-select-sm status-select" data-id="{{ $leave->id }}">
                                <option value="pending" {{ $leave->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ $leave->status == 'approved' ? 'selected' : '' }}>Disetujui</option>
                                <option value="rejected" {{ $leave->status == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary edit-leave" 
                                    data-id="{{ $leave->id }}"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#addModal">
                                Edit
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-leave" data-id="{{ $leave->id }}">
                                Hapus
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Permohonan Cuti</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="leaveForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="type" value="cuti">
                    <input type="hidden" name="id" id="leave_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Alasan</label>
                        <textarea name="reason" id="reason" class="form-control" rows="3" placeholder="Masukkan alasan cuti..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="pending">Pending</option>
                            <option value="approved">Disetujui</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submitButton">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filter status
        document.getElementById('statusFilter').addEventListener('change', function() {
            const status = this.value;
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                if (status === '' || row.getAttribute('data-status') === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Submit leave form
        document.getElementById('leaveForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = document.getElementById('submitButton');
            
            submitButton.disabled = true;
            submitButton.textContent = 'Menyimpan...';
            
            fetch('{{ route("leave.create") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.textContent = 'Simpan';
            });
        });

        // Edit leave
        document.querySelectorAll('.edit-leave').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                
                document.getElementById('modalTitle').textContent = 'Edit Permohonan Cuti';
                document.getElementById('submitButton').textContent = 'Update';
                
                fetch(`/leave/${id}/edit`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('leave_id').value = data.leave.id;
                            document.getElementById('start_date').value = data.leave.start_date;
                            document.getElementById('end_date').value = data.leave.end_date;
                            document.getElementById('reason').value = data.leave.reason;
                            document.getElementById('status').value = data.leave.status;
                        } else {
                            alert('Gagal memuat data permohonan');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat memuat data');
                    });
            });
        });

        // Delete leave
        document.querySelectorAll('.delete-leave').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                
                if (confirm('Apakah Anda yakin ingin menghapus permohonan ini?')) {
                    fetch(`/leave/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan');
                    });
                }
            });
        });

        // Update status
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function() {
                const id = this.getAttribute('data-id');
                const status = this.value;
                
                fetch(`/leave/${id}/status`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        status: status
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Status berhasil diupdate');
                        const row = this.closest('tr');
                        row.setAttribute('data-status', status);
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan');
                });
            });
        });

        // Reset modal
        document.getElementById('addModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('leaveForm').reset();
            document.getElementById('leave_id').value = '';
            document.getElementById('status').value = 'pending';
            document.getElementById('modalTitle').textContent = 'Permohonan Cuti';
            document.getElementById('submitButton').textContent = 'Simpan';
        });
    });
</script>
@endpush
@endsection