<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WasteReport extends Model
{
    protected $fillable = [
        'user_id',
        'waste_type_id',
        'district_id',
        'title',
        'description',
        'feedback',
        'image_feedback',
        'image_path',
        'latitude',
        'longitude',
        'address',
        'status',
        'admin_notes',
        'processed_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the waste report
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the waste type for this report
     */
    public function wasteType()
    {
        return $this->belongsTo(WasteType::class);
    }

    /**
     * Get the district for this report
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Scope for pending reports
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for processed reports
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    /**
     * Scope for completed reports
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for rejected reports
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'pending':
                return 'badge-warning';
            case 'processed':
                return 'badge-info';
            case 'completed':
                return 'badge-success';
            case 'rejected':
                return 'badge-danger';
            default:
                return 'badge-secondary';
        }
    }

    /**
     * Get status text in Indonesian
     */
    public function getStatusTextAttribute()
    {
        switch ($this->status) {
            case 'pending':
                return 'Menunggu';
            case 'processed':
                return 'Diproses';
            case 'completed':
                return 'Selesai';
            case 'rejected':
                return 'Ditolak';
            default:
                return 'Tidak Diketahui';
        }
    }
}
