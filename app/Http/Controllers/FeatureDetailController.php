<?php

namespace App\Http\Controllers;

use App\Models\RoadmapItem;
use App\Models\FeatureDetail;
use App\Models\MediaFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FeatureDetailController extends Controller
{
    /**
     * عرض صفحة تعديل تفاصيل الميزة
     */
    public function edit(RoadmapItem $item)
    {
        $item->load('featureDetail.media');
        
        return view('features.edit', compact('item'));
    }

    /**
     * تحديث أو إنشاء تفاصيل الميزة
     */
    public function update(Request $request, RoadmapItem $item)
    {
        $validated = $request->validate([
            'detailed_description' => 'nullable|string',
            'benefits' => 'nullable|string',
            'use_cases' => 'nullable|string',
            'what_built' => 'nullable|string',
            'files_added' => 'nullable|array',
            'technical_details' => 'nullable|array',
            'what_remaining' => 'nullable|string',
            'next_steps' => 'nullable|array',
            'before_description' => 'nullable|string',
            'after_description' => 'nullable|string',
            'user_guide' => 'nullable|string',
            'guide_steps' => 'nullable|array',
            'related_features' => 'nullable|array',
        ]);

        $featureDetail = $item->featureDetail()->updateOrCreate(
            ['roadmap_item_id' => $item->id],
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث التفاصيل بنجاح',
            'detail' => $featureDetail,
        ]);
    }

    /**
     * رفع صورة قبل
     */
    public function uploadBeforeImage(Request $request, RoadmapItem $item)
    {
        $request->validate([
            'image' => 'required|image|max:5120', // 5MB
        ]);

        $featureDetail = $item->featureDetail ?? $item->featureDetail()->create([]);

        if ($request->hasFile('image')) {
            // حذف الصورة القديمة إن وجدت
            if ($featureDetail->before_image) {
                Storage::disk('public')->delete($featureDetail->before_image);
            }

            // رفع الصورة الجديدة
            $path = $request->file('image')->store('features/before', 'public');
            $featureDetail->update(['before_image' => $path]);

            // إنشاء سجل في media_files
            MediaFile::createFromUpload(
                $request->file('image'),
                $item->project,
                $featureDetail,
                [
                    'category' => 'before',
                    'title' => 'صورة قبل - ' . $item->title,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'تم رفع الصورة بنجاح',
                'path' => $path,
                'url' => Storage::url($path),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'لم يتم رفع أي صورة',
        ], 400);
    }

    /**
     * رفع صورة بعد
     */
    public function uploadAfterImage(Request $request, RoadmapItem $item)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $featureDetail = $item->featureDetail ?? $item->featureDetail()->create([]);

        if ($request->hasFile('image')) {
            if ($featureDetail->after_image) {
                Storage::disk('public')->delete($featureDetail->after_image);
            }

            $path = $request->file('image')->store('features/after', 'public');
            $featureDetail->update(['after_image' => $path]);

            MediaFile::createFromUpload(
                $request->file('image'),
                $item->project,
                $featureDetail,
                [
                    'category' => 'after',
                    'title' => 'صورة بعد - ' . $item->title,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'تم رفع الصورة بنجاح',
                'path' => $path,
                'url' => Storage::url($path),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'لم يتم رفع أي صورة',
        ], 400);
    }

    /**
     * رفع GIF توضيحي
     */
    public function uploadDemoGif(Request $request, RoadmapItem $item)
    {
        $request->validate([
            'gif' => 'required|mimes:gif|max:10240', // 10MB
        ]);

        $featureDetail = $item->featureDetail ?? $item->featureDetail()->create([]);

        if ($request->hasFile('gif')) {
            if ($featureDetail->demo_gif) {
                Storage::disk('public')->delete($featureDetail->demo_gif);
            }

            $path = $request->file('gif')->store('features/demos', 'public');
            $featureDetail->update(['demo_gif' => $path]);

            MediaFile::createFromUpload(
                $request->file('gif'),
                $item->project,
                $featureDetail,
                [
                    'category' => 'demo',
                    'title' => 'عرض توضيحي - ' . $item->title,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'تم رفع الـ GIF بنجاح',
                'path' => $path,
                'url' => Storage::url($path),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'لم يتم رفع أي ملف',
        ], 400);
    }

    /**
     * رفع لقطة شاشة
     */
    public function uploadScreenshot(Request $request, RoadmapItem $item)
    {
        $request->validate([
            'screenshot' => 'required|image|max:5120',
            'title' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $featureDetail = $item->featureDetail ?? $item->featureDetail()->create([]);

        if ($request->hasFile('screenshot')) {
            $media = MediaFile::createFromUpload(
                $request->file('screenshot'),
                $item->project,
                $featureDetail,
                [
                    'category' => 'screenshot',
                    'title' => $request->input('title', 'لقطة شاشة - ' . $item->title),
                    'description' => $request->input('description'),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'تم رفع لقطة الشاشة بنجاح',
                'media' => $media,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'لم يتم رفع أي ملف',
        ], 400);
    }

    /**
     * حذف وسائط
     */
    public function deleteMedia(MediaFile $media)
    {
        $media->deleteFile();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الملف بنجاح',
        ]);
    }

    /**
     * حساب نسبة اكتمال التفاصيل
     */
    public function getCompletionPercentage(RoadmapItem $item)
    {
        $featureDetail = $item->featureDetail;

        if (!$featureDetail) {
            return response()->json([
                'percentage' => 0,
            ]);
        }

        return response()->json([
            'percentage' => $featureDetail->getCompletionPercentage(),
        ]);
    }
}
