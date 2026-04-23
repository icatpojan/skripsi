<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WasteType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'icon',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all waste reports for this type
     */
    public function wasteReports()
    {
        return $this->hasMany(WasteReport::class);
    }

    /**
     * Scope for active waste types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the count of reports for this waste type
     */
    public function getReportsCountAttribute()
    {
        return $this->wasteReports()->count();
    }

    /**
     * Get icon HTML
     */
    public function getIconHtmlAttribute()
    {
        return '<i class="' . $this->icon . '" style="color: ' . $this->color . ';"></i>';
    }
}
