<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $fillable = [
        'name',
        'description',
        'boundaries',
        'color',
        'is_active',
    ];

    protected $casts = [
        'boundaries' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the waste reports for this district
     */
    public function wasteReports()
    {
        return $this->hasMany(WasteReport::class);
    }

    /**
     * Scope for active districts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if a point is inside this district using Ray Casting Algorithm
     * Boundaries are stored as [lng, lat] pairs
     */
    public function containsPoint($latitude, $longitude)
    {
        if (!$this->boundaries || empty($this->boundaries) || count($this->boundaries) < 3) {
            return false;
        }

        $polygon = $this->boundaries;
        $x = $longitude;  // Point longitude
        $y = $latitude;   // Point latitude

        $inside = false;
        $j = count($polygon) - 1;

        for ($i = 0; $i < count($polygon); $i++) {
            $xi = $polygon[$i][0]; // Longitude of vertex i
            $yi = $polygon[$i][1]; // Latitude of vertex i
            $xj = $polygon[$j][0]; // Longitude of vertex j
            $yj = $polygon[$j][1]; // Latitude of vertex j

            // Ray casting algorithm: check if ray crosses edge
            // Skip if edge is horizontal (no intersection possible)
            if (($yi > $y) !== ($yj > $y)) {
                // Calculate intersection point
                $denominator = $yj - $yi;
                if (abs($denominator) > 0.0000001) { // Avoid division by zero
                    $intersectX = ($xj - $xi) * ($y - $yi) / $denominator + $xi;
                    if ($x < $intersectX) {
                        $inside = !$inside;
                    }
                }
            }
            
            $j = $i;
        }

        return $inside;
    }

    /**
     * Get district statistics
     */
    public function getStatistics()
    {
        return [
            'total_reports' => $this->wasteReports()->count(),
            'pending_reports' => $this->wasteReports()->where('status', 'pending')->count(),
            'processed_reports' => $this->wasteReports()->where('status', 'processed')->count(),
            'completed_reports' => $this->wasteReports()->where('status', 'completed')->count(),
            'rejected_reports' => $this->wasteReports()->where('status', 'rejected')->count(),
        ];
    }
}
