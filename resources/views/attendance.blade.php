@extends('layouts.app')

@section('content')
<div class="container mt-3">
    <div class="card">
        <div class="card-body text-center">
            @php
                $waktu = now()->format('H');
                $salam = $waktu < 12 ? 'Selamat Pagi' : ($waktu < 15 ? 'Selamat Siang' : 'Selamat Sore');
            @endphp

            <h5 class="card-title">{{ $salam }}</h5>
            <p class="text-muted mb-2">{{ now()->format('d M Y') }}</p>
            <h6 class="text-primary">{{ Auth::user()->name }}</h6>
            <p id="currentTime" class="h4 text-success mb-4"></p>

            <!-- Camera Section -->
            <div class="mb-3">
                <div id="cameraContainer" class="border rounded p-2">
                    <video id="video" width="100%" height="300" autoplay playsinline></video>
                    <canvas id="canvas" style="display:none;"></canvas>
                </div>
                <div id="photoPreview" class="mt-2" style="display:none;">
                    <img id="photo" src="" alt="Foto Absensi" width="100%" class="rounded">
                </div>
            </div>

            <!-- Camera Controls -->
            <div class="mb-3">
                <button id="startCamera" class="btn btn-outline-primary btn-sm">
                    📷 Hidupkan Kamera
                </button>
                <button id="captureBtn" class="btn btn-primary btn-sm" style="display:none;">
                    📸 Ambil Foto
                </button>
                <button id="retakeBtn" class="btn btn-warning btn-sm" style="display:none;">
                    🔁 Ambil Ulang
                </button>
            </div>

            <!-- Location Info -->
            <div id="locationInfo" class="alert alert-info py-2" style="display:none;">
                <small>
                    <strong>Lokasi:</strong> 
                    <span id="locationText">Mendeteksi lokasi...</span>
                </small>
            </div>

            <!-- Attendance Buttons -->
            <div class="d-grid gap-2">
                <button id="btnAbsenMasuk" class="btn btn-success btn-lg" disabled>
                    ✅ Absen Masuk
                </button>
                <button id="btnAbsenPulang" class="btn btn-warning btn-lg" disabled>
                    🏠 Absen Pulang
                </button>
            </div>

            <!-- Message Alert -->
            <div id="message" class="mt-3"></div>
        </div>
    </div>
</div>

<script>
    let stream = null;
    let photoData = null;
    let currentLocation = null;

    // Elements
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const startCameraBtn = document.getElementById('startCamera');
    const captureBtn = document.getElementById('captureBtn');
    const retakeBtn = document.getElementById('retakeBtn');
    const photoPreview = document.getElementById('photoPreview');
    const photo = document.getElementById('photo');
    const locationInfo = document.getElementById('locationInfo');
    const locationText = document.getElementById('locationText');
    const btnAbsenMasuk = document.getElementById('btnAbsenMasuk');
    const btnAbsenPulang = document.getElementById('btnAbsenPulang');
    const messageDiv = document.getElementById('message');

    // Update current time
    function updateTime() {
        const now = new Date();
        document.getElementById('currentTime').textContent = now.toLocaleTimeString('id-ID');
    }
    setInterval(updateTime, 1000);
    updateTime();

    // Start Camera
    startCameraBtn.addEventListener('click', async function() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: 'user',
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                } 
            });
            video.srcObject = stream;
            startCameraBtn.style.display = 'none';
            captureBtn.style.display = 'inline-block';
            
            // Get location after camera starts
            getLocation();
        } catch (err) {
            showMessage('Tidak dapat mengakses kamera. Pastikan Anda memberikan izin.', 'danger');
        }
    });

    // Capture Photo
    captureBtn.addEventListener('click', function() {
        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        photoData = canvas.toDataURL('image/jpeg');
        photo.src = photoData;
        photoPreview.style.display = 'block';
        captureBtn.style.display = 'none';
        retakeBtn.style.display = 'inline-block';

        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }

        if (currentLocation) {
            enableAttendanceButtons();
        }
    });

    // Retake Photo
    retakeBtn.addEventListener('click', function() {
        photoPreview.style.display = 'none';
        retakeBtn.style.display = 'none';
        startCameraBtn.style.display = 'inline-block';
        photoData = null;
    });

    // Get Location
    function getLocation() {
        if (!navigator.geolocation) {
            showMessage('Browser tidak mendukung geolocation.', 'warning');
            return;
        }

        locationInfo.style.display = 'block';
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                currentLocation = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                };
                
                locationText.textContent = `Lat: ${currentLocation.latitude.toFixed(6)}, Long: ${currentLocation.longitude.toFixed(6)}`;

                checkOfficeDistance(currentLocation);

                if (photoData) {
                    enableAttendanceButtons();
                }
            },
            function(error) {
                let errorMessage = 'Tidak dapat mendapatkan lokasi. ';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage += 'Izin lokasi ditolak.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage += 'Lokasi tidak tersedia.';
                        break;
                    case error.TIMEOUT:
                        errorMessage += 'Timeout.';
                        break;
                }
                locationText.textContent = errorMessage;
                showMessage(errorMessage, 'warning');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }

    // Check distance from office
    function checkOfficeDistance(location) {
        const officeLat = -6.814598305333564;
        const officeLng = 107.15748066568167;
        const maxDistance = 2;

        const distance = calculateDistance(
            location.latitude, 
            location.longitude, 
            officeLat, 
            officeLng
        );

        if (distance > maxDistance) {
            showMessage(`Anda ${distance.toFixed(2)} km dari kantor (maks: ${maxDistance}).`, 'danger');
            return false;
        } else {
            showMessage(`Dalam jarak ${distance.toFixed(2)} km. Bisa absen.`, 'success');
            return true;
        }
    }

    // Haversine
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = 
            Math.sin(dLat/2)**2 +
            Math.cos(lat1 * Math.PI / 180) *
            Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon/2)**2;
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    function enableAttendanceButtons() {
        btnAbsenMasuk.disabled = false;
        btnAbsenPulang.disabled = false;
    }

    btnAbsenMasuk.addEventListener('click', () => submitAttendance('in'));
    btnAbsenPulang.addEventListener('click', () => submitAttendance('out'));

    // FINAL — merged submitAttendance (logika tidak diubah)
    function submitAttendance(type) {
        if (!photoData || !currentLocation) {
            showMessage('Silakan ambil foto dan pastikan lokasi terdeteksi.', 'warning');
            return;
        }

        if (!checkOfficeDistance(currentLocation)) {
            return;
        }

        const formData = new FormData();
        formData.append('type', type);
        formData.append('photo', photoData);
        formData.append('latitude', currentLocation.latitude);
        formData.append('longitude', currentLocation.longitude);
        formData.append('_token', '{{ csrf_token() }}');

        showMessage('Mengirim absensi...', 'info');

        fetch('{{ route("attendance.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("home") }}';
                }, 2000);
            } else {
                showMessage(data.message, 'danger');
            }
        })
        .catch(() => {
            showMessage('Terjadi kesalahan saat mengirim absensi.', 'danger');
        });
    }

    function showMessage(message, type) {
        messageDiv.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    }

</script>
@endsection