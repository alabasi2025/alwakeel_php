<?php

namespace App\Http\Controllers;

use App\Models\UpdateLog;
use App\Models\RoadmapItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

class UpdateLogController extends Controller
{
    /**
     * عرض صفحة سجل التحديثات
     */
    public function index(Request $request)
    {
        $project = $request->get('project', 'all');
        $type = $request->get('type');

        $query = UpdateLog::with('roadmapItem')->orderBy('committed_at', 'desc');

        if ($project !== 'all') {
            $query->where('project', $project);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $updates = $query->paginate(20);

        // الإحصائيات
        $stats = [
            'total' => UpdateLog::count(),
            'features' => UpdateLog::where('type', 'feature')->count(),
            'enhancements' => UpdateLog::where('type', 'enhancement')->count(),
            'bugfixes' => UpdateLog::where('type', 'bugfix')->count(),
            'recent' => UpdateLog::recent(7)->count(),
        ];

        return view('updates.index', compact('updates', 'stats', 'project', 'type'));
    }

    /**
     * عرض تفاصيل تحديث معين
     */
    public function show(UpdateLog $update)
    {
        $update->load(['roadmapItem', 'media']);
        
        return view('updates.show', compact('update'));
    }

    /**
     * إنشاء تحديث يدوياً
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project' => 'required|in:alabasi,wakeel,integration',
            'roadmap_item_id' => 'nullable|exists:roadmap_items,id',
            'title' => 'required|string',
            'description' => 'required|string',
            'type' => 'required|in:feature,enhancement,bugfix,security,performance',
            'what_added' => 'nullable|string',
            'what_changed' => 'nullable|string',
            'what_fixed' => 'nullable|string',
            'how_it_changed' => 'nullable|string',
        ]);

        $update = UpdateLog::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة التحديث بنجاح',
            'update' => $update,
        ]);
    }

    /**
     * تحديث سجل تحديث
     */
    public function update(Request $request, UpdateLog $update)
    {
        $validated = $request->validate([
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'type' => 'nullable|in:feature,enhancement,bugfix,security,performance',
            'what_added' => 'nullable|string',
            'what_changed' => 'nullable|string',
            'what_fixed' => 'nullable|string',
            'how_it_changed' => 'nullable|string',
        ]);

        $update->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث السجل بنجاح',
            'update' => $update,
        ]);
    }

    /**
     * حذف تحديث
     */
    public function destroy(UpdateLog $update)
    {
        $update->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف التحديث بنجاح',
        ]);
    }

    /**
     * مزامنة مع Git commits
     */
    public function syncWithGit(Request $request)
    {
        $validated = $request->validate([
            'project' => 'required|in:alabasi,wakeel,integration',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $project = $validated['project'];
        $limit = $validated['limit'] ?? 10;

        // تحديد مسار المستودع
        $repoPath = $this->getRepoPath($project);

        if (!$repoPath || !is_dir($repoPath)) {
            return response()->json([
                'success' => false,
                'message' => 'المستودع غير موجود',
            ], 404);
        }

        // الحصول على آخر commits
        $command = "cd {$repoPath} && git log --pretty=format:'%H|%an|%ad|%s' --date=iso -n {$limit}";
        $result = Process::run($command);

        if (!$result->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في الحصول على commits',
                'error' => $result->errorOutput(),
            ], 500);
        }

        $commits = [];
        $lines = explode("\n", trim($result->output()));

        foreach ($lines as $line) {
            if (empty($line)) continue;

            [$hash, $author, $date, $message] = explode('|', $line, 4);

            // التحقق من عدم وجود التحديث مسبقاً
            $exists = UpdateLog::where('commit_hash', $hash)->exists();
            if ($exists) continue;

            // إنشاء تحديث جديد
            $update = UpdateLog::createFromCommit($project, [
                'hash' => $hash,
                'author' => $author,
                'date' => $date,
                'message' => $message,
                'description' => $message,
            ]);

            $commits[] = $update;
        }

        return response()->json([
            'success' => true,
            'message' => 'تمت المزامنة بنجاح',
            'synced_count' => count($commits),
            'commits' => $commits,
        ]);
    }

    /**
     * تصدير سجل التحديثات
     */
    public function export(Request $request)
    {
        $project = $request->get('project', 'all');
        $format = $request->get('format', 'markdown');

        $query = UpdateLog::with('roadmapItem')->orderBy('committed_at', 'desc');

        if ($project !== 'all') {
            $query->where('project', $project);
        }

        $updates = $query->get();

        if ($format === 'markdown') {
            $content = $this->generateMarkdown($updates, $project);
            
            return response($content)
                ->header('Content-Type', 'text/markdown')
                ->header('Content-Disposition', 'attachment; filename="changelog.md"');
        }

        return response()->json([
            'message' => 'صيغة التصدير غير مدعومة',
        ], 400);
    }

    /**
     * توليد محتوى Markdown
     */
    private function generateMarkdown($updates, $project)
    {
        $projectName = match($project) {
            'alabasi' => 'نظام الأباسي المحاسبي',
            'wakeel' => 'نظام الوكيل الذكي',
            'integration' => 'التكامل بين النظامين',
            default => 'جميع المشاريع',
        };

        $content = "# سجل التحديثات - {$projectName}\n\n";
        $content .= "**تاريخ التوليد:** " . now()->format('Y-m-d H:i') . "\n\n";
        $content .= "---\n\n";

        $currentDate = null;

        foreach ($updates as $update) {
            $date = $update->committed_at?->format('Y-m-d');
            
            if ($date !== $currentDate) {
                $content .= "## {$date}\n\n";
                $currentDate = $date;
            }

            $icon = $update->getTypeIcon();
            $content .= "### {$icon} {$update->title}\n\n";
            $content .= "**النوع:** {$update->getTypeNameAr()}\n\n";
            
            if ($update->description) {
                $content .= "{$update->description}\n\n";
            }

            if ($update->what_added) {
                $content .= "**ما تم إضافته:**\n{$update->what_added}\n\n";
            }

            if ($update->what_changed) {
                $content .= "**ما تم تغييره:**\n{$update->what_changed}\n\n";
            }

            if ($update->what_fixed) {
                $content .= "**ما تم إصلاحه:**\n{$update->what_fixed}\n\n";
            }

            $content .= "---\n\n";
        }

        return $content;
    }

    /**
     * الحصول على مسار المستودع
     */
    private function getRepoPath(string $project)
    {
        return match($project) {
            'alabasi' => env('ALABASI_REPO_PATH'),
            'wakeel' => base_path(),
            'integration' => null,
            default => null,
        };
    }
}
