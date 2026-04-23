<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\WasteReport;
use App\WasteType;
use App\District;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('admin') || $user->hasRole('petugas')) {
            return $this->adminDashboard();
        } else {
            return $this->userDashboard();
        }
    }

    private function adminDashboard()
    {
        $totalUsers = User::count();
        $totalReports = WasteReport::count();
        $pendingReports = WasteReport::where('status', 'pending')->count();
        $completedReports = WasteReport::where('status', 'completed')->count();

        $reportsByStatus = [
            'pending' => WasteReport::where('status', 'pending')->count(),
            'processed' => WasteReport::where('status', 'processed')->count(),
            'completed' => WasteReport::where('status', 'completed')->count(),
            'rejected' => WasteReport::where('status', 'rejected')->count(),
        ];

        $reportsByType = WasteType::withCount('wasteReports')->get();
        $recentReports = WasteReport::with(['user', 'wasteType'])->latest()->take(5)->get();

        return view('dashboard.admin', compact(
            'totalUsers',
            'totalReports',
            'pendingReports',
            'completedReports',
            'reportsByStatus',
            'reportsByType',
            'recentReports'
        ));
    }

    private function userDashboard()
    {
        $user = auth()->user();
        $userReports = $user->wasteReports()->with('wasteType')->latest()->get();

        $reportsByStatus = [
            'pending' => $userReports->where('status', 'pending')->count(),
            'processed' => $userReports->where('status', 'processed')->count(),
            'completed' => $userReports->where('status', 'completed')->count(),
            'rejected' => $userReports->where('status', 'rejected')->count(),
        ];

        $recentReports = $userReports->take(5);

        return view('dashboard.user', compact('userReports', 'reportsByStatus', 'recentReports'));
    }

    // AJAX Methods untuk fitur dashboard user
    public function getWasteTypes()
    {
        $wasteTypes = WasteType::where('is_active', true)->get();
        return response()->json($wasteTypes);
    }

    public function storeWasteReport(Request $request)
    {
        try {
            // Debug incoming data
            Log::info('Waste report submission', [
                'user_id' => auth()->id(),
                'description' => $request->description,
                'waste_type_id' => $request->waste_type_id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'address' => $request->address,
                'has_image' => $request->hasFile('image'),
                'image_size' => $request->file('image') ? $request->file('image')->getSize() : null
            ]);

            $validator = Validator::make($request->all(), [
                'waste_type_id' => 'required|exists:waste_types,id',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'address' => 'nullable|string',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ], [
                'description.required' => 'Deskripsi laporan wajib diisi.',
                'description.min' => 'Deskripsi minimal 10 karakter.',
                'waste_type_id.required' => 'Jenis sampah wajib dipilih.',
                'waste_type_id.exists' => 'Jenis sampah tidak valid.',
                'latitude.required' => 'Lokasi pada peta wajib ditentukan.',
                'longitude.required' => 'Lokasi pada peta wajib ditentukan.',
                'image.required' => 'Foto bukti sampah wajib diunggah.',
                'image.image' => 'File harus berupa gambar.',
                'image.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif.',
                'image.max' => 'Ukuran gambar maksimal 2MB.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $imagePath = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '_' . $file->getClientOriginalName();

                // Simpan ke storage dan public
                $file->move(public_path('storage/waste-reports'), $filename);
                $imagePath = 'waste-reports/' . $filename;
            }

            // Auto-detect district
            $detectedDistrict = $this->detectDistrictForCoordinates($request->latitude, $request->longitude);

            $report = WasteReport::create([
                'user_id' => auth()->id(),
                'waste_type_id' => $request->waste_type_id ?: null,
                'district_id' => $detectedDistrict ? $detectedDistrict->id : null,
                'title' => 'Laporan Sampah - ' . now()->format('d/m/Y H:i'),
                'description' => $request->description ?: null,
                'image_path' => $imagePath,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'address' => $request->address,
                'status' => 'pending'
            ]);

            Log::info('Waste report created successfully', [
                'report_id' => $report->id,
                'district_id' => $report->district_id,
                'latitude' => $report->latitude,
                'longitude' => $report->longitude
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laporan sampah berhasil dikirim!',
                'report' => $report->load('wasteType')
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in waste report', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating waste report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getUserReports()
    {
        $reports = auth()->user()->wasteReports()->with('wasteType')->latest()->get();

        // Add accessors to each report
        foreach ($reports as $report) {
            $report->append(['status_text', 'status_badge_class']);
        }

        return response()->json($reports);
    }

    public function getReportDetail($id)
    {
        $report = WasteReport::with(['user', 'wasteType'])->findOrFail($id);

        // Pastikan user hanya bisa melihat laporannya sendiri
        if ($report->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Add accessors
        $report->append(['status_text', 'status_badge_class']);

        return response()->json($report);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . auth()->id(),
            'email' => 'required|email|unique:users,email,' . auth()->id(),
            'phone' => 'required|string|max:20',
            'address' => 'required|string'
        ]);

        $user = auth()->user();
        $user->update($request->only(['name', 'username', 'email', 'phone', 'address']));

        return response()->json([
            'success' => true,
            'message' => 'Profile berhasil diperbarui!',
            'user' => $user->fresh()
        ]);
    }

    public function getAllReportsForMap()
    {
        $reports = WasteReport::with(['user', 'wasteType'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('created_at', 'desc') // Order by latest first
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'title' => $report->title,
                    'description' => $report->description,
                    'latitude' => $report->latitude,
                    'longitude' => $report->longitude,
                    'status' => $report->status,
                    'status_text' => $report->status_text,
                    'created_at' => $report->created_at->format('d/m/Y H:i'),
                    'user_name' => $report->user->name,
                    'waste_type' => $report->wasteType ? $report->wasteType->name : 'Tidak ditentukan',
                    'waste_type_color' => $report->wasteType ? $report->wasteType->color : '#6c757d',
                    'image_url' => $report->image_path ? asset('storage/' . $report->image_path) : null
                ];
            });

        return response()->json($reports);
    }

    /**
     * Detect district for given coordinates
     */
    private function detectDistrictForCoordinates($latitude, $longitude)
    {
        $districts = District::where('is_active', true)->get();

        foreach ($districts as $district) {
            if ($district->containsPoint($latitude, $longitude)) {
                Log::info('District detected for coordinates', [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'district_id' => $district->id,
                    'district_name' => $district->name
                ]);
                return $district;
            }
        }

        Log::info('No district found for coordinates', [
            'latitude' => $latitude,
            'longitude' => $longitude
        ]);

        return null;
    }
}
