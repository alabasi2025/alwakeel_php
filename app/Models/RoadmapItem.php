<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoadmapItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'project',
        'phase',
        'phase_number',
        'title',
        'description',
        'order',
        'status',
        'progress',
        'estimated_days',
        'start_date',
        'end_date',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'progress' => 'integer',
        'phase_number' => 'integer',
        'order' => 'integer',
        'estimated_days' => 'integer',
    ];

    /**
     * Get items by project
     */
    public static function byProject(string $project)
    {
        return self::where('project', $project)
            ->orderBy('phase_number')
            ->orderBy('order')
            ->get();
    }

    /**
     * Get completed items
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Get in progress items
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Get pending items
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'progress' => 100,
            'end_date' => now(),
        ]);
    }

    /**
     * Mark as in progress
     */
    public function markAsInProgress()
    {
        $this->update([
            'status' => 'in_progress',
            'start_date' => $this->start_date ?? now(),
        ]);
    }

    /**
     * Update progress
     */
    public function updateProgress(int $progress)
    {
        $this->update(['progress' => min(100, max(0, $progress))]);
        
        if ($progress >= 100) {
            $this->markAsCompleted();
        } elseif ($progress > 0 && $this->status === 'pending') {
            $this->markAsInProgress();
        }
    }

    /**
     * العلاقة مع تفاصيل الميزة
     */
    public function featureDetail()
    {
        return $this->hasOne(FeatureDetail::class);
    }

    /**
     * العلاقة مع سجل التحديثات
     */
    public function updateLogs()
    {
        return $this->hasMany(UpdateLog::class);
    }

    /**
     * التحقق من وجود تفاصيل
     */
    public function hasDetails()
    {
        return $this->featureDetail !== null && $this->featureDetail->isComplete();
    }
}
