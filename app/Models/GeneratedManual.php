<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratedManual extends Model
{
    use HasFactory;

    protected $fillable = [
        'project',
        'title',
        'version',
        'content_markdown',
        'content_html',
        'generation_type',
        'included_features',
        'sections',
        'pdf_path',
        'docx_path',
        'is_published',
        'is_latest',
        'published_at',
        'page_count',
        'word_count',
        'metadata',
    ];

    protected $casts = [
        'included_features' => 'array',
        'sections' => 'array',
        'metadata' => 'array',
        'is_published' => 'boolean',
        'is_latest' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Scope للمشروع
     */
    public function scopeByProject($query, string $project)
    {
        return $query->where('project', $project);
    }

    /**
     * Scope للمنشورة
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope لآخر إصدار
     */
    public function scopeLatest($query)
    {
        return $query->where('is_latest', true);
    }

    /**
     * نشر الدليل
     */
    public function publish()
    {
        // إلغاء تعيين الدليل السابق كآخر إصدار
        self::where('project', $this->project)
            ->where('is_latest', true)
            ->update(['is_latest' => false]);

        $this->update([
            'is_published' => true,
            'is_latest' => true,
            'published_at' => now(),
        ]);
    }

    /**
     * إلغاء النشر
     */
    public function unpublish()
    {
        $this->update([
            'is_published' => false,
            'is_latest' => false,
        ]);
    }

    /**
     * تحويل Markdown إلى HTML
     */
    public function convertToHtml()
    {
        // سيتم استخدام مكتبة Markdown
        $this->content_html = \Illuminate\Support\Str::markdown($this->content_markdown);
        $this->save();
    }

    /**
     * حساب عدد الكلمات
     */
    public function calculateWordCount()
    {
        $text = strip_tags($this->content_markdown);
        $this->word_count = str_word_count($text);
        $this->save();
    }

    /**
     * الحصول على حجم الملف
     */
    public function getFileSize()
    {
        if ($this->pdf_path && file_exists(storage_path('app/public/' . $this->pdf_path))) {
            return filesize(storage_path('app/public/' . $this->pdf_path));
        }
        return 0;
    }

    /**
     * الحصول على رابط التحميل
     */
    public function getDownloadUrl($format = 'pdf')
    {
        if ($format === 'pdf' && $this->pdf_path) {
            return asset('storage/' . $this->pdf_path);
        } elseif ($format === 'docx' && $this->docx_path) {
            return asset('storage/' . $this->docx_path);
        }
        return null;
    }

    /**
     * إنشاء دليل جديد من الميزات
     */
    public static function generateFromFeatures(string $project, array $featureIds)
    {
        $features = RoadmapItem::whereIn('id', $featureIds)
            ->with('featureDetail')
            ->get();

        $content = "# دليل استخدام " . self::getProjectNameAr($project) . "\n\n";
        $content .= "**الإصدار:** " . now()->format('Y.m.d') . "\n\n";
        $content .= "**تاريخ الإصدار:** " . now()->format('Y-m-d') . "\n\n";
        $content .= "---\n\n";

        $sections = [];
        foreach ($features as $feature) {
            if ($feature->featureDetail && $feature->featureDetail->user_guide) {
                $content .= "## " . $feature->title . "\n\n";
                $content .= $feature->featureDetail->user_guide . "\n\n";
                $content .= "---\n\n";
                
                $sections[] = [
                    'id' => $feature->id,
                    'title' => $feature->title,
                ];
            }
        }

        $manual = self::create([
            'project' => $project,
            'title' => 'دليل استخدام ' . self::getProjectNameAr($project),
            'version' => now()->format('Y.m.d'),
            'content_markdown' => $content,
            'generation_type' => 'auto',
            'included_features' => $featureIds,
            'sections' => $sections,
        ]);

        $manual->convertToHtml();
        $manual->calculateWordCount();

        return $manual;
    }

    /**
     * الحصول على اسم المشروع بالعربية
     */
    private static function getProjectNameAr(string $project)
    {
        return match($project) {
            'alabasi' => 'نظام الأباسي المحاسبي',
            'wakeel' => 'نظام الوكيل الذكي',
            'integration' => 'التكامل بين النظامين',
            default => $project,
        };
    }
}
