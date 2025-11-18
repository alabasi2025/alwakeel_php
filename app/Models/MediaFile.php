<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'project',
        'mediable_id',
        'mediable_type',
        'title',
        'description',
        'type',
        'category',
        'file_path',
        'thumbnail_path',
        'url',
        'mime_type',
        'file_size',
        'width',
        'height',
        'duration',
        'order',
        'is_featured',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_featured' => 'boolean',
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'duration' => 'integer',
        'order' => 'integer',
    ];

    /**
     * العلاقة polymorphic
     */
    public function mediable()
    {
        return $this->morphTo();
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
     * Scope للفئة
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope للمميزة
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * الحصول على رابط الملف
     */
    public function getFileUrl()
    {
        if ($this->url) {
            return $this->url;
        }
        
        if ($this->file_path) {
            return Storage::url($this->file_path);
        }
        
        return null;
    }

    /**
     * الحصول على رابط الصورة المصغرة
     */
    public function getThumbnailUrl()
    {
        if ($this->thumbnail_path) {
            return Storage::url($this->thumbnail_path);
        }
        
        return $this->getFileUrl();
    }

    /**
     * الحصول على حجم الملف بشكل قابل للقراءة
     */
    public function getReadableFileSize()
    {
        if (!$this->file_size) {
            return 'غير معروف';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * الحصول على أيقونة النوع
     */
    public function getTypeIcon()
    {
        return match($this->type) {
            'image' => '🖼️',
            'gif' => '🎬',
            'video' => '🎥',
            'document' => '📄',
            default => '📎',
        };
    }

    /**
     * التحقق من أن الملف صورة
     */
    public function isImage()
    {
        return $this->type === 'image' || $this->type === 'gif';
    }

    /**
     * التحقق من أن الملف فيديو
     */
    public function isVideo()
    {
        return $this->type === 'video';
    }

    /**
     * حذف الملف من التخزين
     */
    public function deleteFile()
    {
        if ($this->file_path && Storage::exists($this->file_path)) {
            Storage::delete($this->file_path);
        }
        
        if ($this->thumbnail_path && Storage::exists($this->thumbnail_path)) {
            Storage::delete($this->thumbnail_path);
        }
        
        $this->delete();
    }

    /**
     * إنشاء ملف وسائط من رفع
     */
    public static function createFromUpload($file, string $project, $mediable, array $options = [])
    {
        // حفظ الملف
        $path = $file->store('media/' . $project, 'public');
        
        // الحصول على معلومات الملف
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();
        
        // تحديد النوع
        $type = 'other';
        if (str_starts_with($mimeType, 'image/')) {
            $type = str_ends_with($mimeType, 'gif') ? 'gif' : 'image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            $type = 'video';
        } elseif (str_starts_with($mimeType, 'application/')) {
            $type = 'document';
        }
        
        // الحصول على أبعاد الصورة إن أمكن
        $width = null;
        $height = null;
        if ($type === 'image' || $type === 'gif') {
            $imageInfo = getimagesize($file->getRealPath());
            if ($imageInfo) {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
            }
        }
        
        return $mediable->media()->create([
            'project' => $project,
            'title' => $options['title'] ?? $file->getClientOriginalName(),
            'description' => $options['description'] ?? null,
            'type' => $type,
            'category' => $options['category'] ?? 'general',
            'file_path' => $path,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'width' => $width,
            'height' => $height,
            'order' => $options['order'] ?? 0,
            'is_featured' => $options['is_featured'] ?? false,
        ]);
    }
}
