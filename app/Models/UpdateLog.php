<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UpdateLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'project',
        'roadmap_item_id',
        'version',
        'title',
        'description',
        'type',
        'what_added',
        'what_changed',
        'what_fixed',
        'how_it_changed',
        'commit_hash',
        'commit_message',
        'author',
        'committed_at',
        'files_modified',
        'files_added',
        'files_deleted',
        'screenshots',
        'videos',
    ];

    protected $casts = [
        'committed_at' => 'datetime',
        'files_modified' => 'array',
        'files_added' => 'array',
        'files_deleted' => 'array',
        'screenshots' => 'array',
        'videos' => 'array',
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
     * Scope للمشروع
     */
    public function scopeByProject($query, string $project)
    {
        return $query->where('project', $project);
    }

    /**
     * Scope للنوع
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope للتحديثات الأخيرة
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('committed_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * الحصول على أيقونة النوع
     */
    public function getTypeIcon()
    {
        return match($this->type) {
            'feature' => '✨',
            'enhancement' => '⚡',
            'bugfix' => '🐛',
            'security' => '🔒',
            'performance' => '🚀',
            default => '📝',
        };
    }

    /**
     * الحصول على لون النوع
     */
    public function getTypeColor()
    {
        return match($this->type) {
            'feature' => 'primary',
            'enhancement' => 'info',
            'bugfix' => 'warning',
            'security' => 'danger',
            'performance' => 'success',
            default => 'secondary',
        };
    }

    /**
     * الحصول على اسم النوع بالعربية
     */
    public function getTypeNameAr()
    {
        return match($this->type) {
            'feature' => 'ميزة جديدة',
            'enhancement' => 'تحسين',
            'bugfix' => 'إصلاح خطأ',
            'security' => 'تحديث أمني',
            'performance' => 'تحسين الأداء',
            default => 'تحديث',
        };
    }

    /**
     * إنشاء تحديث من commit
     */
    public static function createFromCommit(string $project, array $commitData)
    {
        return self::create([
            'project' => $project,
            'title' => $commitData['message'] ?? 'تحديث',
            'description' => $commitData['description'] ?? '',
            'type' => self::detectTypeFromMessage($commitData['message'] ?? ''),
            'commit_hash' => $commitData['hash'] ?? null,
            'commit_message' => $commitData['message'] ?? null,
            'author' => $commitData['author'] ?? null,
            'committed_at' => $commitData['date'] ?? now(),
            'files_modified' => $commitData['files_modified'] ?? [],
            'files_added' => $commitData['files_added'] ?? [],
            'files_deleted' => $commitData['files_deleted'] ?? [],
        ]);
    }

    /**
     * اكتشاف نوع التحديث من رسالة الـ commit
     */
    private static function detectTypeFromMessage(string $message)
    {
        $message = strtolower($message);
        
        if (str_contains($message, 'feat') || str_contains($message, 'feature') || str_contains($message, '✨')) {
            return 'feature';
        } elseif (str_contains($message, 'fix') || str_contains($message, 'bug') || str_contains($message, '🐛')) {
            return 'bugfix';
        } elseif (str_contains($message, 'security') || str_contains($message, '🔒')) {
            return 'security';
        } elseif (str_contains($message, 'perf') || str_contains($message, 'performance') || str_contains($message, '🚀')) {
            return 'performance';
        } elseif (str_contains($message, 'enhance') || str_contains($message, 'improve') || str_contains($message, '⚡')) {
            return 'enhancement';
        }
        
        return 'feature';
    }
}
