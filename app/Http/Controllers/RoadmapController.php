<?php

namespace App\Http\Controllers;

use App\Models\RoadmapItem;
use Illuminate\Http\Request;

class RoadmapController extends Controller
{
    /**
     * Display Alabasi roadmap
     */
    public function alabasi()
    {
        $items = RoadmapItem::byProject('alabasi');
        $stats = $this->calculateStats($items);
        $project = [
            'name' => 'نظام الأباسي المحاسبي',
            'icon' => '💼',
            'description' => 'Laravel 10 + PHP 8.2 | https://alabasi.es',
            'type' => 'alabasi'
        ];
        return view('roadmap-single', compact('items', 'stats', 'project'));
    }

    /**
     * Display Wakeel roadmap
     */
    public function wakeel()
    {
        $items = RoadmapItem::byProject('wakeel');
        $stats = $this->calculateStats($items);
        $project = [
            'name' => 'نظام الوكيل الذكي',
            'icon' => '🤖',
            'description' => 'AI-Powered Assistant | التعلم الذاتي',
            'type' => 'wakeel'
        ];
        return view('roadmap-single', compact('items', 'stats', 'project'));
    }

    /**
     * Display Integration roadmap
     */
    public function integration()
    {
        $items = RoadmapItem::byProject('integration');
        $stats = $this->calculateStats($items);
        $project = [
            'name' => 'الربط والتكامل',
            'icon' => '🔗',
            'description' => 'API Integration | الوكيل ↔ الأباسي',
            'type' => 'integration'
        ];
        return view('roadmap-single', compact('items', 'stats', 'project'));
    }

    /**
     * Update item status
     */
    public function updateStatus(Request $request, RoadmapItem $item)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
            'progress' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $item->update($validated);

        if ($validated['status'] === 'completed') {
            $item->markAsCompleted();
        } elseif ($validated['status'] === 'in_progress') {
            $item->markAsInProgress();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الحالة بنجاح',
            'item' => $item->fresh(),
        ]);
    }

    /**
     * Update item progress
     */
    public function updateProgress(Request $request, RoadmapItem $item)
    {
        $validated = $request->validate([
            'progress' => 'required|integer|min:0|max:100',
        ]);

        $item->updateProgress($validated['progress']);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث التقدم بنجاح',
            'item' => $item->fresh(),
        ]);
    }

    /**
     * Calculate statistics for a project
     */
    private function calculateStats($items)
    {
        $total = $items->count();
        $completed = $items->where('status', 'completed')->count();
        $inProgress = $items->where('status', 'in_progress')->count();
        $pending = $items->where('status', 'pending')->count();
        
        $totalProgress = $items->sum('progress');
        $averageProgress = $total > 0 ? round($totalProgress / $total, 1) : 0;

        return [
            'total' => $total,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'pending' => $pending,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
            'average_progress' => $averageProgress,
        ];
    }
}
