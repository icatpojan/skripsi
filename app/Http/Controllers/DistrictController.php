<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\District;
use Illuminate\Support\Facades\Log;

class DistrictController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Get all districts for admin with pagination and filters
    public function getAllDistricts(Request $request)
    {
        $query = District::withCount('wasteReports');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $perPage = $request->get('per_page', 10);
        $districts = $query->latest()->paginate($perPage);

        return response()->json($districts);
    }

    // Get single district
    public function getDistrict($id)
    {
        $district = District::withCount('wasteReports')->findOrFail($id);
        return response()->json($district);
    }

    // Store new district
    public function storeDistrict(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'boundaries' => 'required|array|min:3',
            'boundaries.*' => 'required|array|size:2',
            'boundaries.*.0' => 'required|numeric',
            'boundaries.*.1' => 'required|numeric',
            'color' => 'nullable|string|max:7',
        ]);

        try {
            $district = District::create([
                'name' => $request->name,
                'description' => $request->description,
                'boundaries' => $request->boundaries,
                'color' => $request->color ?? '#3b82f6',
                'is_active' => true
            ]);

            Log::info('District created by admin', [
                'admin_id' => auth()->id(),
                'district_id' => $district->id,
                'name' => $district->name
            ]);

            return response()->json([
                'success' => true,
                'message' => "Distrik {$district->name} berhasil ditambahkan"
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating district', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambah distrik'
            ], 500);
        }
    }

    // Update district
    public function updateDistrict(Request $request, $id)
    {
        $district = District::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'boundaries' => 'required|array|min:3',
            'boundaries.*' => 'required|array|size:2',
            'boundaries.*.0' => 'required|numeric',
            'boundaries.*.1' => 'required|numeric',
            'color' => 'nullable|string|max:7',
        ]);

        try {
            $district->update([
                'name' => $request->name,
                'description' => $request->description,
                'boundaries' => $request->boundaries,
                'color' => $request->color ?? $district->color,
            ]);

            Log::info('District updated by admin', [
                'admin_id' => auth()->id(),
                'district_id' => $district->id,
                'name' => $district->name
            ]);

            return response()->json([
                'success' => true,
                'message' => "Distrik {$district->name} berhasil diupdate"
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating district', [
                'error' => $e->getMessage(),
                'district_id' => $id,
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate distrik'
            ], 500);
        }
    }

    // Delete district
    public function deleteDistrict($id)
    {
        try {
            $district = District::findOrFail($id);
            $districtName = $district->name;

            // Check if district has reports
            if ($district->wasteReports()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus distrik yang memiliki laporan sampah'
                ], 400);
            }

            $district->delete();

            Log::info('District deleted by admin', [
                'admin_id' => auth()->id(),
                'district_id' => $id
            ]);

            return response()->json([
                'success' => true,
                'message' => "Distrik {$districtName} berhasil dihapus"
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting district', [
                'error' => $e->getMessage(),
                'district_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus distrik'
            ], 500);
        }
    }

    // Toggle district status
    public function toggleDistrictStatus($id)
    {
        try {
            $district = District::findOrFail($id);
            $district->is_active = !$district->is_active;
            $district->save();

            $status = $district->is_active ? 'diaktifkan' : 'dinonaktifkan';

            Log::info('District status toggled', [
                'admin_id' => auth()->id(),
                'district_id' => $district->id,
                'new_status' => $district->is_active
            ]);

            return response()->json([
                'success' => true,
                'message' => "Distrik {$district->name} berhasil {$status}"
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling district status', [
                'error' => $e->getMessage(),
                'district_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengubah status distrik'
            ], 500);
        }
    }

    // Get district statistics
    public function getDistrictStatistics($id)
    {
        try {
            $district = District::findOrFail($id);
            $statistics = $district->getStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil statistik distrik'
            ], 500);
        }
    }

    // Auto-detect district for coordinates
    public function detectDistrict(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        $latitude = $request->latitude;
        $longitude = $request->longitude;

        $districts = District::where('is_active', true)->get();
        $detectedDistrict = null;

        foreach ($districts as $district) {
            if ($district->containsPoint($latitude, $longitude)) {
                $detectedDistrict = $district;
                break;
            }
        }

        return response()->json([
            'success' => true,
            'district' => $detectedDistrict,
            'is_inside_district' => $detectedDistrict !== null
        ]);
    }
}
