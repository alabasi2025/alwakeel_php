<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'roadmap_item_id',
        'detailed_description',
        'benefits',
        'use_cases',
        'what_built',
        'files_added',
        'technical_details',
        'what_remaining',
        'next_steps',
        'before_description',
        'after_description',
        'before_image',
        'after_image',
        'demo_gif',
        'user_guide',
        'guide_steps',
        'screenshots',
        'videos',
        'related_features',
    ];

    protected $casts = [
        'files_added' => 'array',
        'technical_details' => 'array',
        'next_steps' => 'array',
        'guide_steps' => 'array',
        'screenshots' => 'array',
        'videos' => 'array',
        'related_features' => 'array',
    ];

    /**
     * العلاقة مع RoadmapItem
     */
    public function roadmapItem()
    {
        return $this->belongsTo(RoadmapItem::class);
    }

    /**
     * العلاقة مع الوسائط
     */
    public function media()
    {
        return $this->morphMany(MediaFile::class, 'mediable');
    }

    /**
     * الحصول على صور قبل
     */
    public function getBeforeImages()
    {
        return $this->media()->where('category', 'before')->get();
    }

    /**
     * الحصول على صور بعد
     */
    public function getAfterImages()
    {
        return $this->media()->where('category', 'after')->get();
    }

    /**
     * الحصول على GIF التوضيحية
     */
    public function getDemoGifs()
    {
        return $this->media()->where('category', 'demo')->where('type', 'gif')->get();
    }

    /**
     * الحصول على لقطات الشاشة
     */
    public function getScreenshots()
    {
        return $this->media()->where('category', 'screenshot')->get();
    }

    /**
     * الحصول على الفيديوهات
     */
    public function getVideos()
    {
        return $this->media()->where('type', 'video')->get();
    }

    /**
     * التحقق من اكتمال التفاصيل
     */
    public function isComplete()
    {
        return !empty($this->detailed_description) &&
               !empty($this->what_built) &&
               !empty($this->user_guide);
    }

    /**
     * حساب نسبة الاكتمال
     */
    public function getCompletionPercentage()
    {
        $fields = [
            'detailed_description',
            'benefits',
            'use_cases',
            'what_built',
            'files_added',
            'technical_details',
            'before_description',
            'after_description',
            'user_guide',
        ];

        $completed = 0;
        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $completed++;
            }
        }

        return round(($completed / count($fields)) * 100);
    }
}
