<?php

namespace App\Http\Controllers;

use App\Models\RoadmapItem;
use Illuminate\Http\Request;

class RoadmapController extends Controller
{
    /**
     * Display the roadmap page
     */
    public function index()
    {
        $alabasiItems = RoadmapItem::byProject('alabasi');
        $integrationItems = RoadmapItem::byProject('integration');
        $wakeelItems = RoadmapItem::byProject('wakeel');

        // حساب الإحصائيات
        $stats = [
            'alabasi' => $this->calculateStats($alabasiItems),
            'integration' => $this->calculateStats($integrationItems),
            'wakeel' => $this->calculateStats($wakeelItems),
        ];

        return view('roadmap', compact('alabasiItems', 'integrationItems', 'wakeelItems', 'stats'));
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
