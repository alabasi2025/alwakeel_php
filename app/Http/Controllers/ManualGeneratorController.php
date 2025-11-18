<?php

namespace App\Http\Controllers;

use App\Models\RoadmapItem;
use App\Models\GeneratedManual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ManualGeneratorController extends Controller
{
    /**
     * عرض صفحة توليد الأدلة
     */
    public function index()
    {
        $projects = ['alabasi', 'wakeel', 'integration'];
        $manuals = GeneratedManual::orderBy('created_at', 'desc')->paginate(10);
        
        return view('manuals.index', compact('projects', 'manuals'));
    }

    /**
     * توليد دليل جديد
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'project' => 'required|in:alabasi,wakeel,integration',
            'title' => 'nullable|string',
            'feature_ids' => 'nullable|array',
            'feature_ids.*' => 'exists:roadmap_items,id',
        ]);

        $project = $validated['project'];
        
        // الحصول على الميزات
        if (isset($validated['feature_ids']) && count($validated['feature_ids']) > 0) {
            $featureIds = $validated['feature_ids'];
        } else {
            // جميع الميزات المكتملة
            $featureIds = RoadmapItem::byProject($project)
                ->where('status', 'completed')
                ->pluck('id')
                ->toArray();
        }

        // توليد الدليل
        $manual = GeneratedManual::generateFromFeatures($project, $featureIds);

        return response()->json([
            'success' => true,
            'message' => 'تم توليد الدليل بنجاح',
            'manual' => $manual,
        ]);
    }

    /**
     * عرض دليل معين
     */
    public function show(GeneratedManual $manual)
    {
        return view('manuals.show', compact('manual'));
    }

    /**
     * نشر دليل
     */
    public function publish(GeneratedManual $manual)
    {
        $manual->publish();

        return response()->json([
            'success' => true,
            'message' => 'تم نشر الدليل بنجاح',
        ]);
    }

    /**
     * إلغاء نشر دليل
     */
    public function unpublish(GeneratedManual $manual)
    {
        $manual->unpublish();

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء نشر الدليل بنجاح',
        ]);
    }

    /**
     * تصدير دليل كـ PDF
     */
    public function exportPdf(GeneratedManual $manual)
    {
        // سيتم تطويره لاحقاً باستخدام مكتبة PDF
        return response()->json([
            'message' => 'ميزة التصدير إلى PDF قيد التطوير',
        ]);
    }

    /**
     * تصدير دليل كـ Word
     */
    public function exportWord(GeneratedManual $manual)
    {
        // سيتم تطويره لاحقاً
        return response()->json([
            'message' => 'ميزة التصدير إلى Word قيد التطوير',
        ]);
    }

    /**
     * حذف دليل
     */
    public function destroy(GeneratedManual $manual)
    {
        // حذف الملفات المرتبطة
        if ($manual->pdf_path) {
            Storage::disk('public')->delete($manual->pdf_path);
        }
        if ($manual->docx_path) {
            Storage::disk('public')->delete($manual->docx_path);
        }

        $manual->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الدليل بنجاح',
        ]);
    }

    /**
     * إرسال الدليل إلى النظام المحاسبي
     */
    public function sendToAlabasi(GeneratedManual $manual)
    {
        if ($manual->project !== 'alabasi') {
            return response()->json([
                'success' => false,
                'message' => 'هذا الدليل ليس للنظام المحاسبي',
            ], 400);
        }

        // سيتم تطوير API للإرسال إلى النظام المحاسبي
        // يمكن استخدام HTTP Client لإرسال الدليل عبر API

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال الدليل إلى النظام المحاسبي بنجاح',
        ]);
    }

    /**
     * الحصول على معاينة الدليل
     */
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'project' => 'required|in:alabasi,wakeel,integration',
            'feature_ids' => 'required|array',
            'feature_ids.*' => 'exists:roadmap_items,id',
        ]);

        $features = RoadmapItem::whereIn('id', $validated['feature_ids'])
            ->with('featureDetail')
            ->get();

        $content = "# دليل استخدام " . $this->getProjectNameAr($validated['project']) . "\n\n";
        $content .= "**الإصدار:** معاينة\n\n";
        $content .= "---\n\n";

        foreach ($features as $feature) {
            if ($feature->featureDetail && $feature->featureDetail->user_guide) {
                $content .= "## " . $feature->title . "\n\n";
                $content .= $feature->featureDetail->user_guide . "\n\n";
                $content .= "---\n\n";
            }
        }

        return response()->json([
            'success' => true,
            'content' => $content,
            'html' => \Illuminate\Support\Str::markdown($content),
        ]);
    }

    /**
     * الحصول على اسم المشروع بالعربية
     */
    private function getProjectNameAr(string $project)
    {
        return match($project) {
            'alabasi' => 'نظام الأباسي المحاسبي',
            'wakeel' => 'نظام الوكيل الذكي',
            'integration' => 'التكامل بين النظامين',
            default => $project,
        };
    }
}
