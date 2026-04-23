@extends('layouts.app')

@section('title', 'Dashboard User')

@section('content')
<div class="container mt-2">

    <!-- Welcome Message -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-2">
                                <i class="fas fa-hand-wave mr-2"></i>
                                Selamat datang, {{ auth()->user()->name }}!
                            </h4>
                            <p class="mb-0">
                                Mari bersama-sama menjaga lingkungan kita tetap bersih dan sehat.
                                Laporkan sampah yang Anda temukan untuk membantu tim pembersihan.
                            </p>
                        </div>
                        <div class="col-md-4 text-center">
                            <img src="{{ asset('img/waste.png') }}" alt="Waste App Icon" class="img-fluid" style="max-width: 120px; filter: brightness(0) invert(1);">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Laporan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-reports">{{ $userReports->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Menunggu
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="pending-reports">{{ $reportsByStatus['pending'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Diproses
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="processed-reports">{{ $reportsByStatus['processed'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cogs fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Selesai
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="completed-reports">{{ $reportsByStatus['completed'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt mr-2"></i>Aksi Cepat
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <button class="btn btn-secondary btn-block btn-lg btn-report" onclick="openReportModal()" disabled>
                                <i class="fas fa-plus mr-2"></i>Laporkan Sampah
                            </button>
                        </div>
                        <div class="col-md-4 mb-3">
                            <button class="btn btn-info btn-block btn-lg" onclick="openMapModal()">
                                <i class="fas fa-map-marker-alt mr-2"></i>Lihat Peta
                            </button>
                        </div>
                        <div class="col-md-4 mb-3">
                            <button class="btn btn-primary btn-block btn-lg" onclick="openProfileModal()">
                                <i class="fas fa-user mr-2"></i>Profile Saya
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Tips Section -->
    <div class="col-12">
        <div class="card shadow mb-4 w-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-lightbulb mr-2"></i>Tips Melaporkan Sampah
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-camera text-primary mr-3 mt-1"></i>
                            <div>
                                <strong>Ambil Foto yang Jelas</strong>
                                <p class="text-muted mb-0">Pastikan foto sampah terlihat jelas dan menunjukkan lokasi yang tepat.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-map-marker-alt text-success mr-3 mt-1"></i>
                            <div>
                                <strong>Aktifkan GPS</strong>
                                <p class="text-muted mb-0">Izinkan aplikasi mengakses lokasi untuk mendapatkan koordinat yang akurat.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-edit text-info mr-3 mt-1"></i>
                            <div>
                                <strong>Isi Deskripsi Lengkap</strong>
                                <p class="text-muted mb-0">Berikan keterangan detail tentang jenis dan kondisi sampah.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-clock text-warning mr-3 mt-1"></i>
                            <div>
                                <strong>Laporkan Segera</strong>
                                <p class="text-muted mb-0">Laporkan sampah segera setelah ditemukan untuk penanganan yang cepat.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Recent Reports -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4 w-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list mr-2"></i>Laporan Terbaru Saya
                    </h6>
                    <button class="btn btn-primary btn-sm" onclick="loadAllReports()">
                        <i class="fas fa-eye mr-1"></i>Lihat Semua
                    </button>
                </div>
                <div class="card-body" id="recent-reports-container">
                    @if($recentReports->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Judul</th>
                                    <th>Jenis Sampah</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentReports as $report)
                                <tr>
                                    <td>{{ $report->created_at->format('j M Y') }}</td>
                                    <td>{{ $report->title }}</td>
                                    <td>
                                        @if($report->wasteType)
                                        <i class="{{ $report->wasteType->icon }} mr-1" style="color: {{ $report->wasteType->color }};"></i>
                                        {{ $report->wasteType->name }}
                                        @else
                                        <span class="text-muted">Tidak ditentukan</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $report->status_badge_class }} badge-pill">
                                            {{ $report->status_text }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-info btn-sm" onclick="viewReportDetail({{ $report->id }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Anda belum memiliki laporan sampah</p>
                        <button class="btn btn-success" onclick="openReportModal()">
                            <i class="fas fa-plus mr-2"></i>Laporkan Sampah Pertama
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Laporkan Sampah -->
<div class="modal fade" id="reportModal" tabindex="-1" role="dialog" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">
                    <i class="fas fa-plus mr-2"></i>Laporkan Sampah
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="reportForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="waste_type_id">Jenis Sampah</label>
                        <select class="form-control" id="waste_type_id" name="waste_type_id">
                            <option value="">Pilih Jenis Sampah</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Tambahkan deskripsi sampah (opsional)"></textarea>
                    </div>

                    <!-- Hidden location fields -->
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">
                    <input type="hidden" id="address" name="address">

                    <!-- Peta Lokasi Report -->
                    <div class="form-group">
                        <label>
                            <i class="fas fa-map-marker-alt mr-1"></i>Peta Lokasi Sampah
                        </label>
                        <div id="reportLocationMap" style="height: 300px; width: 100%; border-radius: 8px; border: 2px solid #e9ecef;"></div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Peta menampilkan lokasi Anda saat ini. Klik pada peta untuk mengubah lokasi.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="image">Foto Sampah</label>
                        <div class="file-upload-container">
                            <div class="file-upload-area" id="fileUploadArea">
                                <div class="file-upload-content">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Drag & Drop foto di sini</h5>
                                    <p class="text-muted mb-3">atau</p>
                                    <button type="button" class="btn btn-outline-primary" onclick="selectFile()">
                                        <i class="fas fa-folder-open mr-2"></i>Pilih File
                                    </button>
                                    <small class="form-text text-muted mt-2 d-block">Format: JPG, PNG, GIF. Maksimal 2MB. (Opsional)</small>
                                </div>
                                <input type="file" class="form-control-file d-none" id="image" name="image" accept="image/*">
                            </div>

                            <!-- Camera Section -->
                            <div class="camera-section mt-3" id="cameraSection" style="display: none;">
                                <div class="camera-container">
                                    <video id="cameraVideo" autoplay muted playsinline></video>
                                    <div class="camera-controls mt-2">
                                        <button type="button" class="btn btn-success" id="snapBtn">
                                            <i class="fas fa-camera mr-2"></i>Ambil Foto
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="closeCamera()">
                                            <i class="fas fa-times mr-2"></i>Tutup Kamera
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Camera Button -->
                            <div class="text-center mt-2" id="cameraButtonContainer">
                                <button type="button" class="btn btn-outline-success" onclick="openCamera()">
                                    <i class="fas fa-camera mr-2"></i>Buka Kamera
                                </button>
                            </div>

                            <div class="file-preview" id="filePreview" style="display: none;">
                                <img id="imagePreview" class="img-fluid rounded" alt="Preview">
                                <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeImage()">
                                    <i class="fas fa-trash mr-1"></i>Hapus Foto
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Hidden location button - location is handled automatically by map -->
                    <div class="form-group" style="display: none;">
                        <button type="button" class="btn btn-info btn-sm" onclick="getCurrentLocationWithAlert()">
                            <i class="fas fa-map-marker-alt mr-1"></i>Ambil Lokasi Saat Ini
                        </button>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Lokasi akan diisi otomatis jika sudah diizinkan sebelumnya
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane mr-1"></i>Kirim Laporan
                    </button>
                </div>
                <div class="loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Mengirim laporan...</p>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Peta -->
<div class="modal fade" id="mapModal" tabindex="-1" role="dialog" aria-labelledby="mapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mapModalLabel">
                    <i class="fas fa-map mr-2"></i>Peta Laporan Sampah
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="map" style="height: 500px; width: 100%;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Profile -->
<div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">

        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profileModalLabel">
                    <i class="fas fa-user mr-2"></i>Profile Saya
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="profileForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="profile_name">Nama Lengkap *</label>
                        <input type="text" class="form-control" id="profile_name" name="name" value="{{ auth()->user()->name }}" required>
                    </div>
                    <div class="form-group">
                        <label for="profile_username">Username *</label>
                        <input type="text" class="form-control" id="profile_username" name="username" value="{{ auth()->user()->username }}" required>
                    </div>
                    <div class="form-group">
                        <label for="profile_email">Email *</label>
                        <input type="email" class="form-control" id="profile_email" name="email" value="{{ auth()->user()->email }}" required>
                    </div>
                    <div class="form-group">
                        <label for="profile_phone">Nomor Telepon *</label>
                        <input type="text" class="form-control" id="profile_phone" name="phone" value="{{ auth()->user()->phone }}" required>
                    </div>
                    <div class="form-group">
                        <label for="profile_address">Alamat *</label>
                        <textarea class="form-control" id="profile_address" name="address" rows="3" required>{{ auth()->user()->address }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Simpan Perubahan
                    </button>
                </div>
                <div class="loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Menyimpan perubahan...</p>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Laporan -->
<div class="modal fade" id="reportDetailModal" tabindex="-1" role="dialog" aria-labelledby="reportDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportDetailModalLabel">
                    <i class="fas fa-file-alt mr-2"></i>Detail Laporan
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="reportDetailContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }

    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }

    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }

    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }

    .text-gray-300 {
        color: #dddfeb !important;
    }

    .text-gray-800 {
        color: #5a5c69 !important;
    }

    .card {
        border: none;
        border-radius: 10px;
    }

    .card-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
        border-radius: 10px 10px 0 0 !important;
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }

    .modal-xl {
        max-width: 90%;
    }

    .leaflet-popup-content {
        margin: 10px;
    }

    .report-image {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
    }

    /* Report Location Map Styles */
    #reportLocationMap {
        z-index: 1000;
    }

    #reportLocationMap .leaflet-control-zoom {
        border: 2px solid #007bff;
        border-radius: 4px;
    }

    #reportLocationMap .leaflet-control-zoom a {
        background-color: #007bff;
        color: white;
        border: none;
    }

    #reportLocationMap .leaflet-control-zoom a:hover {
        background-color: #0056b3;
    }

    /* Marker Cluster Styles */
    .marker-cluster-small {
        background-color: rgba(181, 226, 140, 0.6);
    }

    .marker-cluster-small div {
        background-color: rgba(110, 204, 57, 0.6);
    }

    .marker-cluster-medium {
        background-color: rgba(241, 211, 87, 0.6);
    }

    .marker-cluster-medium div {
        background-color: rgba(240, 194, 12, 0.6);
    }

    .marker-cluster-large {
        background-color: rgba(253, 156, 115, 0.6);
    }

    .marker-cluster-large div {
        background-color: rgba(241, 128, 23, 0.6);
    }

    .marker-cluster {
        background-clip: padding-box;
        border-radius: 20px;
    }

    .marker-cluster div {
        width: 30px;
        height: 30px;
        margin-left: 5px;
        margin-top: 5px;
        text-align: center;
        border-radius: 15px;
        font: 12px "Helvetica Neue", Arial, Helvetica, sans-serif;
        color: white;
        font-weight: bold;
    }

    .marker-cluster span {
        line-height: 30px;
    }

    /* File Upload Styles */
    .file-upload-container {
        position: relative;
    }

    .file-upload-area {
        border: 3px dashed #dee2e6;
        border-radius: 10px;
        padding: 40px 20px;
        text-align: center;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
        cursor: pointer;
        min-height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .file-upload-area:hover {
        border-color: #007bff;
        background-color: #e3f2fd;
    }

    .file-upload-area.dragover {
        border-color: #28a745;
        background-color: #d4edda;
        transform: scale(1.02);
    }

    .file-upload-content {
        width: 100%;
    }

    .file-preview {
        text-align: center;
        padding: 20px;
        border: 2px solid #dee2e6;
        border-radius: 10px;
        background-color: #f8f9fa;
    }

    .file-preview img {
        max-width: 100%;
        max-height: 200px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 15px;
    }

    .file-preview .btn {
        display: block;
        width: 100%;
        margin-top: 10px;
    }

    .btn-group .btn {
        margin: 0 5px;
        position: relative;
        z-index: 10;
    }

    .btn-group {
        position: relative;
        z-index: 10;
    }

    /* Camera Styles */
    .camera-section {
        border: 2px solid #dee2e6;
        border-radius: 10px;
        padding: 20px;
        background-color: #f8f9fa;
    }

    .camera-container {
        text-align: center;
    }

    #cameraVideo {
        width: 100%;
        max-width: 400px;
        height: 300px;
        border: 2px solid #444;
        border-radius: 8px;
        background-color: #000;
        object-fit: cover;
    }

    .camera-controls {
        display: flex;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .camera-controls .btn {
        min-width: 120px;
    }

    /* Modal blur effect */
    .modal-backdrop {
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
    }

    .modal {
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
    }

    .modal-content {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        border: none;
        border-radius: 15px;
    }

    .modal-header {
        border-bottom: 1px solid #e9ecef;
        border-radius: 15px 15px 0 0;
    }

    .modal-footer {
        border-top: 1px solid #e9ecef;
        border-radius: 0 0 15px 15px;
    }

    /* Loading spinner */
    .loading {
        display: none;
        text-align: center;
        padding: 20px;
    }

    .loading.show {
        display: block;
    }

    /* Form improvements */
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .btn:focus {
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

</style>
@endpush

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Leaflet MarkerCluster -->
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
<script src="{{ asset('js/dashboard-user.js') }}?v={{ time() }}"></script>

<!-- Location Permission Modal -->
<div class="modal fade" id="locationPermissionModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-map-marker-alt mr-2"></i>Izin Lokasi Diperlukan
                </h5>
            </div>
            <div class="modal-body text-center">
                <div class="mb-4">
                    <i class="fas fa-location-arrow fa-3x text-primary mb-3"></i>
                    <h5>Aplikasi membutuhkan akses lokasi Anda</h5>
                    <p class="text-muted">
                        Untuk dapat melaporkan sampah, aplikasi memerlukan izin untuk mengakses lokasi Anda.
                        Lokasi akan digunakan untuk menandai lokasi sampah yang Anda laporkan.
                    </p>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Penting:</strong> Tanpa izin lokasi, Anda tidak dapat melaporkan sampah.
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary btn-lg" onclick="requestLocationPermission()">
                    <i class="fas fa-check mr-2"></i>Izinkan Akses Lokasi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Location Denied Modal -->
<div class="modal fade" id="locationDeniedModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Akses Lokasi Ditolak
                </h5>
            </div>
            <div class="modal-body text-center">
                <div class="mb-4">
                    <i class="fas fa-ban fa-3x text-danger mb-3"></i>
                    <h5>Anda menolak akses lokasi</h5>
                    <p class="text-muted">
                        Tanpa izin lokasi, Anda tidak dapat menggunakan fitur pelaporan sampah.
                        Silakan refresh halaman dan izinkan akses lokasi.
                    </p>
                </div>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Cara mengizinkan lokasi:</strong>
                    <ol class="text-left mt-2 mb-0">
                        <li>Klik ikon kunci di address bar browser</li>
                        <li>Pilih "Allow" untuk lokasi</li>
                        <li>Refresh halaman ini</li>
                    </ol>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" onclick="location.reload()">
                    <i class="fas fa-refresh mr-2"></i>Refresh Halaman
                </button>
            </div>
        </div>
    </div>
</div>
@endpush
