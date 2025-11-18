<?php

namespace App\Http\Controllers;

use App\Models\RoadmapItem;
use App\Models\FeatureDetail;
use App\Models\UpdateLog;
use Illuminate\Http\Request;

class RoadmapController extends Controller
{
    /**
     * Display the roadmap page
     */
    public function index(Request $request)
    {
        $project = $request->get('project', 'all');
        
        $alabasiItems = RoadmapItem::byProject('alabasi')->with('featureDetail')->get();
        $integrationItems = RoadmapItem::byProject('integration')->with('featureDetail')->get();
        $wakeelItems = RoadmapItem::byProject('wakeel')->with('featureDetail')->get();

        // حساب الإحصائيات
        $stats = [
            'alabasi' => $this->calculateStats($alabasiItems),
            'integration' => $this->calculateStats($integrationItems),
            'wakeel' => $this->calculateStats($wakeelItems),
        ];
        
        // آخر التحديثات
        $recentUpdates = UpdateLog::with('roadmapItem')
            ->orderBy('committed_at', 'desc')
            ->limit(10)
            ->get();

        return view('roadmap.index', compact('alabasiItems', 'integrationItems', 'wakeelItems', 'stats', 'recentUpdates', 'project'));
    }

    /**
     * Display Alabasi roadmap
     */
    public function alabasi()
    {
        $items = RoadmapItem::byProject('alabasi')->with('featureDetail')->get();
        $stats = $this->calculateStats($items);
        $recentUpdates = UpdateLog::with('roadmapItem')
            ->whereHas('roadmapItem', function($q) {
                $q->where('project', 'alabasi');
            })
            ->orderBy('committed_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('roadmap.project', compact('items', 'stats', 'recentUpdates'))->with('project', 'alabasi');
    }

    /**
     * Display Wakeel roadmap
     */
    public function wakeel()
    {
        $items = RoadmapItem::byProject('wakeel')->with('featureDetail')->get();
        $stats = $this->calculateStats($items);
        $recentUpdates = UpdateLog::with('roadmapItem')
            ->whereHas('roadmapItem', function($q) {
                $q->where('project', 'wakeel');
            })
            ->orderBy('committed_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('roadmap.project', compact('items', 'stats', 'recentUpdates'))->with('project', 'wakeel');
    }

    /**
     * Display Integration roadmap
     */
    public function integration()
    {
        $items = RoadmapItem::byProject('integration')->with('featureDetail')->get();
        $stats = $this->calculateStats($items);
        $recentUpdates = UpdateLog::with('roadmapItem')
            ->whereHas('roadmapItem', function($q) {
                $q->where('project', 'integration');
            })
            ->orderBy('committed_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('roadmap.project', compact('items', 'stats', 'recentUpdates'))->with('project', 'integration');
    }

    /**
     * Show feature details
     */
    public function show(RoadmapItem $item)
    {
        $item->load(['featureDetail.media', 'updateLogs']);
        
        return view('roadmap.show', compact('item'));
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

        // عدد الميزات التي لها تفاصيل كاملة
        $withDetails = $items->filter(function($item) {
            return $item->featureDetail && $item->featureDetail->isComplete();
        })->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'pending' => $pending,
            'with_details' => $withDetails,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
            'average_progress' => $averageProgress,
            'details_rate' => $total > 0 ? round(($withDetails / $total) * 100, 1) : 0,
        ];
    }
}
