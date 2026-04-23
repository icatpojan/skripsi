<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\WasteReport;
use App\WasteType;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Get all reports for admin with pagination and filters
    public function getAllReports(Request $request)
    {
        $query = WasteReport::with(['user', 'wasteType', 'district']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Reporter filter
        if ($request->filled('reporter')) {
            $reporter = $request->reporter;
            $query->whereHas('user', function ($userQuery) use ($reporter) {
                $userQuery->where('name', 'like', "%{$reporter}%")
                    ->orWhere('username', 'like', "%{$reporter}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Waste type filter
        if ($request->filled('type')) {
            $query->where('waste_type_id', $request->type);
        }

        // Feedback filter
        if ($request->filled('feedback')) {
            if ($request->feedback === 'with_feedback') {
                $query->whereNotNull('feedback');
            } elseif ($request->feedback === 'without_feedback') {
                $query->whereNull('feedback');
            }
        }

        // Date filters
        if ($request->filled('date_start') && $request->filled('date_end')) {
            // Both dates filled - use between
            $query->whereBetween('created_at', [
                $request->date_start . ' 00:00:00',
                $request->date_end . ' 23:59:59'
            ]);
        } elseif ($request->filled('date_start')) {
            // Only start date filled - use that date only
            $query->whereDate('created_at', $request->date_start);
        } elseif ($request->filled('date_end')) {
            // Only end date filled - use that date only
            $query->whereDate('created_at', $request->date_end);
        }

        $perPage = $request->get('per_page', 10);
        $reports = $query->latest()->paginate($perPage);

        // Add accessors to each report
        foreach ($reports->items() as $report) {
            $report->append(['status_text', 'status_badge_class']);
        }

        return response()->json($reports);
    }

    // Update report status
    public function updateReportStatus(Request $request, $id)
    {
        // Debug: Log incoming data
        Log::info('Update report status request', [
            'report_id' => $id,
            'request_data' => $request->all(),
            'method' => $request->method(),
            'intended_method' => $request->input('_method'),
            'content_type' => $request->header('Content-Type'),
            'user_id' => auth()->id()
        ]);

        try {
            Log::info('Input data for validation:', $request->all());

            $request->validate([
                'status' => 'required|in:pending,processed,completed,rejected',
                'admin_notes' => 'nullable|string|max:1000'
            ]);

            $report = WasteReport::findOrFail($id);

            $report->update([
                'status' => $request->status,
                'admin_notes' => $request->admin_notes,
                'processed_at' => $request->status === 'processed' ? now() : $report->processed_at
            ]);

            Log::info('Report status updated by admin', [
                'admin_id' => auth()->id(),
                'report_id' => $report->id,
                'old_status' => $report->getOriginal('status'),
                'new_status' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'message' => "Status laporan berhasil diupdate menjadi " . $report->status_text
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error updating report status', [
                'errors' => $e->errors(),
                'report_id' => $id,
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating report status', [
                'error' => $e->getMessage(),
                'report_id' => $id,
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate status laporan'
            ], 500);
        }
    }

    // Add feedback to report
    public function addFeedback(Request $request, $id)
    {
        try {
            $request->validate([
                'feedback' => 'required|string|max:1000',
                'image_feedback' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $report = WasteReport::findOrFail($id);

            $updateData = [
                'feedback' => $request->feedback
            ];

            // Handle feedback image upload
            if ($request->hasFile('image_feedback')) {
                $image = $request->file('image_feedback');
                $imageName = 'feedback_' . time() . '_' . $report->id . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('feedback_images', $imageName, 'public');
                $updateData['image_feedback'] = $imagePath;
            }

            $report->update($updateData);

            Log::info('Feedback added to report', [
                'admin_id' => auth()->id(),
                'report_id' => $report->id,
                'has_image' => $request->hasFile('image_feedback')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Feedback berhasil ditambahkan ke laporan'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error adding feedback', [
                'error' => $e->getMessage(),
                'report_id' => $id,
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan feedback'
            ], 500);
        }
    }

    // Print reports
    public function printReports(Request $request)
    {
        $query = WasteReport::with(['user', 'wasteType', 'district']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Waste type filter
        if ($request->filled('type')) {
            $query->where('waste_type_id', $request->type);
        }

        // Date filters
        if ($request->filled('date_start') && $request->filled('date_end')) {
            // Both dates filled - use between
            $query->whereBetween('created_at', [
                $request->date_start . ' 00:00:00',
                $request->date_end . ' 23:59:59'
            ]);
        } elseif ($request->filled('date_start')) {
            // Only start date filled - use that date only
            $query->whereDate('created_at', $request->date_start);
        } elseif ($request->filled('date_end')) {
            // Only end date filled - use that date only
            $query->whereDate('created_at', $request->date_end);
        }

        $reports = $query->latest()->get();

        // Add accessors to each report
        foreach ($reports as $report) {
            $report->append(['status_text', 'status_badge_class']);
        }

        return view('admin.reports.print', compact('reports'));
    }

    // Get statistics for admin
    public function getStatistics()
    {
        $totalUsers = User::count();
        $totalReports = WasteReport::count();
        $pendingReports = WasteReport::where('status', 'pending')->count();
        $completedReports = WasteReport::where('status', 'completed')->count();

        return response()->json([
            'total_users' => $totalUsers,
            'total_reports' => $totalReports,
            'pending_reports' => $pendingReports,
            'completed_reports' => $completedReports,
        ]);
    }

    // Get all users for admin with pagination and filters
    public function getAllUsers(Request $request)
    {
        $query = User::with('roles');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $perPage = $request->get('per_page', 10);
        $users = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json($users);
    }

    // Get single user
    public function getUser($id)
    {
        $user = User::with('roles')->findOrFail($id);
        return response()->json($user);
    }

    // Store new user
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:user,admin,petugas'
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'is_active' => true
            ]);

            // Assign role
            $user->assignRole($request->role);

            Log::info('User created by admin', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'role' => $request->role
            ]);

            return response()->json([
                'success' => true,
                'message' => "User {$user->name} berhasil ditambahkan"
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating user', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambah user'
            ], 500);
        }
    }

    // Update user
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:user,admin,petugas'
        ]);

        try {
            $user->update([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email
            ]);

            // Update password if provided
            if ($request->filled('password')) {
                $user->update(['password' => bcrypt($request->password)]);
            }

            // Update role
            $user->syncRoles([$request->role]);

            Log::info('User updated by admin', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'role' => $request->role
            ]);

            return response()->json([
                'success' => true,
                'message' => "User {$user->name} berhasil diupdate"
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating user', [
                'error' => $e->getMessage(),
                'user_id' => $id,
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate user'
            ], 500);
        }
    }

    // Delete user
    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent admin from deleting themselves
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak dapat menghapus akun sendiri'
                ], 400);
            }

            $userName = $user->name;
            $user->delete();

            Log::info('User deleted by admin', [
                'admin_id' => auth()->id(),
                'user_id' => $id
            ]);

            return response()->json([
                'success' => true,
                'message' => "User {$userName} berhasil dihapus"
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting user', [
                'error' => $e->getMessage(),
                'user_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus user'
            ], 500);
        }
    }

    // Toggle user status
    public function toggleUserStatus($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent admin from deactivating themselves
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak dapat menonaktifkan akun sendiri'
                ], 400);
            }

            $user->is_active = !$user->is_active;
            $user->save();

            $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

            Log::info('User status toggled', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'new_status' => $user->is_active
            ]);

            return response()->json([
                'success' => true,
                'message' => "User {$user->name} berhasil {$status}"
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling user status', [
                'error' => $e->getMessage(),
                'user_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengubah status user'
            ], 500);
        }
    }

    // Get all waste types for admin
    public function getAllWasteTypes()
    {
        $wasteTypes = WasteType::all();

        return response()->json($wasteTypes);
    }

    // Toggle waste type status
    public function toggleWasteTypeStatus($id)
    {
        try {
            $wasteType = WasteType::findOrFail($id);
            $wasteType->is_active = !$wasteType->is_active;
            $wasteType->save();

            $status = $wasteType->is_active ? 'diaktifkan' : 'dinonaktifkan';

            Log::info('Waste type status toggled', [
                'admin_id' => auth()->id(),
                'waste_type_id' => $wasteType->id,
                'new_status' => $wasteType->is_active
            ]);

            return response()->json([
                'success' => true,
                'message' => "Jenis sampah {$wasteType->name} berhasil {$status}"
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling waste type status', [
                'error' => $e->getMessage(),
                'waste_type_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengubah status jenis sampah'
            ], 500);
        }
    }
}
