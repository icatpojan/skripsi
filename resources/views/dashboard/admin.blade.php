@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="admin-dashboard">
    <!-- Hero Section -->
    <div class="admin-hero">
        <div class="admin-hero-content">
            @role('admin')
            <h1 class="admin-hero-title">Dashboard Admin</h1>
            @endrole
             @role('petugas')
            <h1 class="admin-hero-title">Dashboard Petugas</h1>
            @endrole
            <p class="admin-hero-subtitle">Selamat datang, {{ auth()->user()->name }}</p>
        </div>
    </div>

    <div class="admin-container">
        <!-- Statistics -->
        <div class="admin-stats">
             @role('admin')
             <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                            </div>
                <div class="stat-content">
                    <div class="stat-label">Total Pengguna</div>
                    <div class="stat-value" id="total-users">{{ $totalUsers }}</div>
            </div>
        </div>
        @endrole

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-file-alt"></i>
                            </div>
                <div class="stat-content">
                    <div class="stat-label">Total Laporan</div>
                    <div class="stat-value" id="total-reports">{{ $totalReports }}</div>
            </div>
        </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                            </div>
                <div class="stat-content">
                    <div class="stat-label">Menunggu</div>
                    <div class="stat-value" id="pending-reports">{{ $pendingReports }}</div>
            </div>
        </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                            </div>
                <div class="stat-content">
                    <div class="stat-label">Selesai</div>
                    <div class="stat-value" id="completed-reports">{{ $completedReports }}</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
        <div class="admin-actions">
              @role('admin')
             <button class="action-btn" onclick="openUserManagementModal()">
                <i class="fas fa-users"></i>
                <span>Kelola User</span>
                            </button>
            <button class="action-btn" onclick="openWasteTypeModal()">
                <i class="fas fa-trash"></i>
                <span>Kelola Jenis Sampah</span>
                            </button>
            <button class="action-btn" onclick="openDistrictManagementModal()">
                <i class="fas fa-map-marked-alt"></i>
                <span>Kelola Distrik</span>
                            </button>
            @endrole
            <button class="action-btn" onclick="openMapModal()">
                <i class="fas fa-map"></i>
                <span>Lihat Peta</span>
                            </button>
                        </div>

        <!-- Dashboard Map Section -->
        <div class="admin-section">
            <div class="admin-section-header">
                <h2 class="admin-section-title">Peta Distrik & Laporan Aktif</h2>
                    </div>
            <div class="admin-section-content">
                <div id="dashboardMap" style="height: 600px; width: 100%; border-radius: 12px; overflow: hidden;"></div>
        </div>
    </div>

        <!-- Reports Section -->
        <div class="admin-section mt-3">
            <div class="admin-section-header">
                <h2 class="admin-section-title">Laporan Sampah</h2>
                </div>
            <div class="admin-section-content">
                    <!-- Search and Filter -->
                <div class="admin-filters">
                    <div class="filter-row">
                        <input type="text" class="admin-input" id="reportSearch" placeholder="Cari laporan...">
                        <input type="text" class="admin-input" id="reportReporterFilter" placeholder="Cari pelapor...">
                        <select class="admin-select" id="reportStatusFilter">
                                <option value="">Semua Status</option>
                                <option value="pending">Menunggu</option>
                                <option value="processed">Diproses</option>
                                <option value="completed">Selesai</option>
                                <option value="rejected">Ditolak</option>
                            </select>
                        <select class="admin-select" id="reportTypeFilter">
                                <option value="">Semua Jenis</option>
                                <!-- Waste types will be loaded here -->
                            </select>
                        </div>
                    <div class="filter-row">
                        <input type="date" class="admin-input" id="reportDateStart" placeholder="Tanggal Awal">
                        <input type="date" class="admin-input" id="reportDateEnd" placeholder="Tanggal Akhir">
                        <select class="admin-select" id="reportPerPage">
                                <option value="10">10 per halaman</option>
                                <option value="25">25 per halaman</option>
                                <option value="50">50 per halaman</option>
                            </select>
                        <select class="admin-select" id="reportFeedbackFilter">
                                <option value="">Semua Feedback</option>
                                <option value="with_feedback">Ada Feedback</option>
                                <option value="without_feedback">Belum Ada Feedback</option>
                            </select>
                            @role('admin')
                            <button class="admin-btn admin-btn-primary" onclick="printReports()">
                                <i class="fas fa-print"></i> Print
                            </button>
                            @endrole
                        </div>
                    </div>

                    <div id="reportsTableContainer">
                        <!-- Reports table will be loaded here -->
                    </div>

                    <!-- Pagination -->
                <div class="admin-pagination">
                    <div class="pagination-info">
                            <span id="reportPaginationInfo">Menampilkan 0 dari 0 data</span>
                        </div>
                    <div class="pagination-controls">
                        <button class="admin-btn admin-btn-outline" id="reportPrevPage" onclick="changeReportPage(-1)">Sebelumnya</button>
                        <span id="reportCurrentPage" class="pagination-page">1</span>
                        <button class="admin-btn admin-btn-outline" id="reportNextPage" onclick="changeReportPage(1)">Selanjutnya</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Map Modal -->
<div class="modal fade" id="mapModal" tabindex="-1" role="dialog" aria-labelledby="mapModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mapModalLabel">
                    <i class="fas fa-map-marker-alt mr-2"></i>Peta Laporan Sampah
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

<!-- Report Detail Modal -->
<div class="modal fade" id="reportDetailModal" tabindex="-1" role="dialog" aria-labelledby="reportDetailModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportDetailModalLabel">
                    <i class="fas fa-eye mr-2"></i>Detail Laporan
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

<!-- User Management Modal -->
<div class="modal fade" id="userManagementModal" tabindex="-1" role="dialog" aria-labelledby="userManagementModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userManagementModalLabel">
                    <i class="fas fa-users mr-2"></i>Kelola User
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Add User Button -->
                <div class="mb-3">
                    <button class="btn btn-success" onclick="openAddUserModal()">
                        <i class="fas fa-plus mr-1"></i>Tambah User
                    </button>
                </div>

                <!-- Search and Filter -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="userSearch" placeholder="Cari user...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="userRoleFilter">
                            <option value="">Semua Role</option>
                            <option value="admin">Admin</option>
                            <option value="petugas">Petugas</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="userStatusFilter">
                            <option value="">Semua Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="usersTable">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Users will be loaded here -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <span id="userPaginationInfo">Menampilkan 0 dari 0 data</span>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-outline-primary" id="userPrevPage" onclick="changeUserPage(-1)">Sebelumnya</button>
                        <span id="userCurrentPage" class="mx-2">1</span>
                        <button class="btn btn-sm btn-outline-primary" id="userNextPage" onclick="changeUserPage(1)">Selanjutnya</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">
                    <i class="fas fa-user-plus mr-2"></i>Tambah User Baru
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="form-group">
                        <label for="userName">Nama Lengkap</label>
                        <input type="text" class="form-control" id="userName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="userUsername">Username</label>
                        <input type="text" class="form-control" id="userUsername" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="userEmail">Email</label>
                        <input type="email" class="form-control" id="userEmail" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="userPassword">Password</label>
                        <input type="password" class="form-control" id="userPassword" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="userRole">Role</label>
                        <select class="form-control" id="userRole" name="role" required>
                            <option value="user">User</option>
                            <option value="petugas">Petugas</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveUser()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">
                    <i class="fas fa-user-edit mr-2"></i>Edit User
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="editUserId" name="id">
                    <div class="form-group">
                        <label for="editUserName">Nama Lengkap</label>
                        <input type="text" class="form-control" id="editUserName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="editUserUsername">Username</label>
                        <input type="text" class="form-control" id="editUserUsername" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="editUserEmail">Email</label>
                        <input type="email" class="form-control" id="editUserEmail" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="editUserPassword">Password (kosongkan jika tidak ingin mengubah)</label>
                        <input type="password" class="form-control" id="editUserPassword" name="password">
                    </div>
                    <div class="form-group">
                        <label for="editUserRole">Role</label>
                        <select class="form-control" id="editUserRole" name="role" required>
                            <option value="user">User</option>
                            <option value="petugas">Petugas</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="updateUser()">Update</button>
            </div>
        </div>
    </div>
</div>

<!-- Waste Type Management Modal -->
<div class="modal fade" id="wasteTypeModal" tabindex="-1" role="dialog" aria-labelledby="wasteTypeModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="wasteTypeModalLabel">
                    <i class="fas fa-trash mr-2"></i>Kelola Jenis Sampah
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="wasteTypesTable">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Icon</th>
                                <th>Warna</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Waste types will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Change Report Status Modal -->
<div class="modal fade" id="changeStatusModal" tabindex="-1" role="dialog" aria-labelledby="changeStatusModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeStatusModalLabel">
                    <i class="fas fa-edit mr-2"></i>Ubah Status Laporan
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="changeStatusForm">
                    <input type="hidden" id="statusReportId" name="report_id">
                    <div class="form-group">
                        <label for="reportStatus">Status</label>
                        <select class="form-control" id="reportStatus" name="status" required>
                            <option value="pending">Menunggu</option>
                            <option value="processed">Diproses</option>
                            <option value="completed">Selesai</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="adminNotes">Catatan Admin (Opsional)</label>
                        <textarea class="form-control" id="adminNotes" name="admin_notes" rows="3" placeholder="Tambahkan catatan untuk laporan ini..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="updateReportStatus()">Update Status</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Feedback Modal -->
<div class="modal fade" id="addFeedbackModal" tabindex="-1" role="dialog" aria-labelledby="addFeedbackModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addFeedbackModalLabel">
                    <i class="fas fa-comment-dots mr-2"></i>Tambah Feedback
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addFeedbackForm" enctype="multipart/form-data">
                    <input type="hidden" id="feedbackReportId" name="report_id">
                    <div class="form-group">
                        <label for="feedbackText">Feedback untuk Pelapor</label>
                        <textarea class="form-control" id="feedbackText" name="feedback" rows="4" placeholder="Berikan feedback untuk laporan ini..." required></textarea>
                        <small class="form-text text-muted">Feedback ini akan terlihat oleh pelapor laporan</small>
                    </div>
                    <div class="form-group">
                        <label for="feedbackImage">Foto Feedback (Opsional)</label>
                        <div class="file-upload-container">
                            <div class="file-upload-area" id="feedbackFileUploadArea">
                                <div class="file-upload-content">
                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                    <h6 class="text-muted">Drag & Drop foto di sini</h6>
                                    <p class="text-muted mb-2">atau</p>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectFeedbackFile()">
                                        <i class="fas fa-folder-open mr-1"></i>Pilih File
                                    </button>
                                    <small class="form-text text-muted mt-2 d-block">Format: JPG, PNG, GIF. Maksimal 2MB.</small>
                                </div>
                                <input type="file" class="form-control-file d-none" id="feedbackImage" name="image_feedback" accept="image/*">
                            </div>
                            <div class="file-preview" id="feedbackFilePreview" style="display: none;">
                                <img id="feedbackImagePreview" class="img-fluid rounded" alt="Preview" style="max-height: 200px;">
                                <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeFeedbackImage()">
                                    <i class="fas fa-trash mr-1"></i>Hapus Foto
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="submitFeedback()">
                    <i class="fas fa-paper-plane mr-1"></i>Kirim Feedback
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Admin Dashboard Styles */
    .admin-dashboard {
        min-height: 100vh;
        background: #f5f7fa;
        padding-top: 0;
    }

    /* Ensure main content doesn't get hidden behind navbar */
    main {
        padding-top: 0 !important;
    }

    /* Navbar Transparent Fixed */
    #mainNavbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        background: rgba(255, 255, 255, 0);
        backdrop-filter: blur(0px);
        transition: all 0.3s ease;
        padding: 1.5rem 0;
        box-shadow: none;
    }

    #mainNavbar.navbar-scrolled {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        padding: 1rem 0;
    }

    #mainNavbar.navbar-scrolled .navbar-brand,
    #mainNavbar.navbar-scrolled .nav-link {
        color: #1a1a1a !important;
    }

    #mainNavbar .navbar-brand,
    #mainNavbar .nav-link {
        color: #ffffff !important;
        transition: color 0.3s ease;
    }

    /* Hero Section */
    .admin-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 8rem 2rem 4rem;
        margin-bottom: 3rem;
        position: relative;
        overflow: hidden;
    }

    .admin-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
        opacity: 0.3;
    }

    .admin-hero-content {
        position: relative;
        z-index: 1;
        max-width: 1200px;
        margin: 0 auto;
        text-align: center;
        color: white;
    }

    .admin-hero-title {
        font-size: clamp(2rem, 5vw, 3.5rem);
        font-weight: 700;
        margin-bottom: 1rem;
        letter-spacing: -0.02em;
    }

    .admin-hero-subtitle {
        font-size: 1.25rem;
        opacity: 0.9;
    }

    /* Container */
    .admin-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 2rem 4rem;
    }

    /* Statistics */
    .admin-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 3rem;
    }

    .stat-item {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
    }

    .stat-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        background: #f3f4f6;
        color: #1a1a1a;
    }

    .stat-content {
        flex: 1;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1a1a1a;
        line-height: 1;
    }

    /* Quick Actions */
    .admin-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 3rem;
    }

    .action-btn {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1rem;
        font-weight: 500;
        color: #1a1a1a;
    }

    .action-btn i {
        font-size: 1.75rem;
        color: #667eea;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border-color: #667eea;
        color: #667eea;
    }

    /* Section */
    .admin-section {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        border: 1px solid #e5e7eb;
    }

    .admin-section-header {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .admin-section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
    }

    .admin-section-content {
        /* Content styles */
    }

    /* Filters */
    .admin-filters {
        margin-bottom: 2rem;
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .admin-input,
    .admin-select {
        padding: 0.75rem 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        background: white;
    }

    .admin-input:focus,
    .admin-select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    /* Buttons */
    .admin-btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .admin-btn-primary {
        background: #667eea;
        color: white;
    }

    .admin-btn-primary:hover {
        background: #5568d3;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .admin-btn-outline {
        background: white;
        color: #667eea;
        border: 1px solid #667eea;
    }

    .admin-btn-outline:hover {
        background: #667eea;
        color: white;
    }

    /* Pagination */
    .admin-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e5e7eb;
    }

    .pagination-info {
        color: #6b7280;
        font-size: 0.875rem;
    }

    .pagination-controls {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .pagination-page {
        font-weight: 600;
        color: #1a1a1a;
    }

    /* Modal blur effect */
    .modal-backdrop {
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        background-color: rgba(0, 0, 0, 0.3) !important;
    }

    .modal-content {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    .modal-header {
        border-bottom: 1px solid #e9ecef;
        border-radius: 15px 15px 0 0;
    }

    .modal-footer {
        border-top: 1px solid #e9ecef;
        border-radius: 0 0 15px 15px;
    }

    /* Marker cluster styles */
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

    /* File Upload Styles for Feedback */
    .file-upload-container {
        position: relative;
    }

    .file-upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
        cursor: pointer;
        min-height: 120px;
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
        padding: 15px;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        background-color: #f8f9fa;
    }

    .file-preview img {
        max-width: 100%;
        max-height: 200px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 10px;
    }

    /* Current Location Pulse */
    .current-location-marker {
        background: transparent !important;
        border: none !important;
        width: 24px !important;
        height: 24px !important;
    }

    .location-pulse {
        width: 22px;
        height: 22px;
        background: #4285F4;
        border-radius: 50%;
        border: 4px solid white;
        box-shadow: 0 0 20px rgba(66, 133, 244, 0.8), 0 0 0 2px rgba(255, 255, 255, 0.5);
        animation: pulse-location 2s infinite;
        position: relative;
        z-index: 9999;
    }

    @keyframes pulse-location {
        0% {
            transform: scale(0.9);
            box-shadow: 0 0 0 0 rgba(66, 133, 244, 0.7);
        }
        70% {
            transform: scale(1.1);
            box-shadow: 0 0 0 20px rgba(66, 133, 244, 0);
        }
        100% {
            transform: scale(0.9);
            box-shadow: 0 0 0 0 rgba(66, 133, 244, 0);
        }
    }

    /* District Management Styles */
    .district-boundary {
        stroke: #3b82f6;
        stroke-width: 3;
        fill: rgba(59, 130, 246, 0.1);
        stroke-dasharray: 5, 5;
    }

    .district-boundary:hover {
        stroke: #1d4ed8;
        stroke-width: 4;
        fill: rgba(29, 78, 216, 0.2);
    }

    .district-info {
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    /* Marker label styling for district map */
    .marker-label {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        font-weight: 700;
        font-size: 11px;
        color: #ffffff;
        text-align: center;
        padding: 0 !important;
        margin: 0 !important;
    }

    .marker-label::before {
        display: none;
    }

    /* Existing district tooltip styling */
    .existing-district-tooltip {
        background: rgba(0, 0, 0, 0.8) !important;
        border: none !important;
        border-radius: 4px !important;
        color: #ffffff !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        padding: 4px 8px !important;
    }

    .existing-district-tooltip::before {
        border-top-color: rgba(0, 0, 0, 0.8) !important;
    }

    /* Custom marker styling */
    .custom-marker {
        background: transparent !important;
        border: none !important;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .admin-hero {
            padding: 6rem 1.5rem 3rem;
        }

        .admin-container {
            padding: 0 1.5rem 3rem;
        }

        .admin-stats {
            grid-template-columns: 1fr;
        }

        .admin-actions {
            grid-template-columns: repeat(2, 1fr);
        }

        .filter-row {
            grid-template-columns: 1fr;
        }

        .admin-pagination {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }
    }

    @media (max-width: 480px) {
        .admin-actions {
            grid-template-columns: 1fr;
        }

        .stat-item {
            padding: 1.5rem;
        }

        .action-btn {
            padding: 1.25rem;
        }
    }
</style>
@endpush

<!-- District Management Modal -->
<div class="modal fade" id="districtManagementModal" tabindex="-1" role="dialog" aria-labelledby="districtManagementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="districtManagementModalLabel">
                    <i class="fas fa-map-marked-alt mr-2"></i>Kelola Distrik
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Search and Filter -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="districtSearch" placeholder="Cari distrik...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="districtStatusFilter">
                            <option value="">Semua Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="districtPerPage">
                            <option value="10">10 per halaman</option>
                            <option value="25">25 per halaman</option>
                            <option value="50">50 per halaman</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-success btn-block" onclick="openAddDistrictModal()">
                            <i class="fas fa-plus mr-1"></i>Tambah
                        </button>
                    </div>
                </div>

                <!-- Districts Table -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Distrik</th>
                                <th>Deskripsi</th>
                                <th>Status</th>
                                <th>Jumlah Laporan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="districtsTableBody">
                            <!-- Districts will be loaded here -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Districts pagination">
                    <ul class="pagination justify-content-center" id="districtsPagination">
                        <!-- Pagination will be loaded here -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit District Modal -->
<div class="modal fade" id="addDistrictModal" tabindex="-1" role="dialog" aria-labelledby="addDistrictModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDistrictModalLabel">
                    <i class="fas fa-plus mr-2"></i>Tambah Distrik
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addDistrictForm">
                    <input type="hidden" id="districtId" name="id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="districtName">Nama Distrik</label>
                                <input type="text" class="form-control" id="districtName" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="districtColor">Warna Distrik</label>
                                <input type="color" class="form-control" id="districtColor" name="color" value="#3b82f6">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="districtDescription">Deskripsi</label>
                        <textarea class="form-control" id="districtDescription" name="description" rows="3" placeholder="Deskripsi distrik..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Batas Distrik (Polygon)</label>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            Klik pada peta untuk membuat polygon batas distrik. Klik titik pertama lagi untuk menutup polygon.
                        </div>
                        <div class="mb-2">
                            <button type="button" class="btn btn-sm btn-warning" onclick="removeLastPoint()" id="btnRemoveLastPoint" disabled>
                                <i class="fas fa-undo mr-1"></i>Hapus Titik Terakhir
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="resetPolygon()" id="btnResetPolygon" disabled>
                                <i class="fas fa-redo mr-1"></i>Reset Polygon
                            </button>
                            <span class="ml-2 text-muted" id="pointCounter">Titik: 0</span>
                        </div>
                        <div id="districtMap" style="height: 400px; width: 100%; border: 1px solid #dee2e6; border-radius: 8px; position: relative;"></div>
                        <input type="hidden" id="districtBoundaries" name="boundaries">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="submitDistrict()">
                    <i class="fas fa-save mr-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- District Statistics Modal -->
<div class="modal fade" id="districtStatisticsModal" tabindex="-1" role="dialog" aria-labelledby="districtStatisticsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="districtStatisticsModalLabel">
                    <i class="fas fa-chart-bar mr-2"></i>Statistik Distrik
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="districtStatisticsContent">
                    <!-- Statistics will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Leaflet CSS and JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
<script src="{{ asset('js/dashboard-admin.js') }}?v={{ time() }}"></script>
<script>
    // Navbar scroll effect
    document.addEventListener('DOMContentLoaded', function() {
        const navbar = document.getElementById('mainNavbar');
        
        if (navbar) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    navbar.classList.add('navbar-scrolled');
                } else {
                    navbar.classList.remove('navbar-scrolled');
                }
            });
        }
    });
</script>
@endpush
