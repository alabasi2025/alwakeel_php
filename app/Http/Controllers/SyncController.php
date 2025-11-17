<?php
namespace App\Http\Controllers;

use App\Models\SyncLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class SyncController extends Controller
{
    private $projectPath = '/home/u306850950/laravel_app';
    private $githubRepo = 'alabasi2025/alwakeel_php';
    
    public function index()
    {
        $logs = SyncLog::orderBy('created_at', 'desc')->take(10)->get();
        return view('sync', compact('logs'));
    }

    public function githubPull()
    {
        try {
            // تنفيذ git pull
            $output = shell_exec("cd {$this->projectPath} && git pull origin main 2>&1");
            
            // حفظ السجل
            SyncLog::create([
                'sync_type' => 'github_pull',
                'status' => 'success',
                'message' => 'تم السحب من GitHub بنجاح',
                'details' => $output
            ]);
            
            return redirect()->route('sync')->with('success', 'تم السحب من GitHub بنجاح ✅');
            
        } catch (\Exception $e) {
            SyncLog::create([
                'sync_type' => 'github_pull',
                'status' => 'failed',
                'message' => 'فشل السحب من GitHub',
                'details' => $e->getMessage()
            ]);
            
            return redirect()->route('sync')->with('error', 'فشل السحب: ' . $e->getMessage());
        }
    }

    public function githubPush()
    {
        try {
            // إضافة جميع التغييرات
            shell_exec("cd {$this->projectPath} && git add . 2>&1");
            
            // عمل commit
            $commitMessage = 'تحديث تلقائي من الوكيل - ' . now()->format('Y-m-d H:i:s');
            shell_exec("cd {$this->projectPath} && git commit -m \"{$commitMessage}\" 2>&1");
            
            // رفع التغييرات
            $output = shell_exec("cd {$this->projectPath} && git push origin main 2>&1");
            
            // حفظ السجل
            SyncLog::create([
                'sync_type' => 'github_push',
                'status' => 'success',
                'message' => 'تم الرفع إلى GitHub بنجاح',
                'details' => $output
            ]);
            
            return redirect()->route('sync')->with('success', 'تم الرفع إلى GitHub بنجاح ✅');
            
        } catch (\Exception $e) {
            SyncLog::create([
                'sync_type' => 'github_push',
                'status' => 'failed',
                'message' => 'فشل الرفع إلى GitHub',
                'details' => $e->getMessage()
            ]);
            
            return redirect()->route('sync')->with('error', 'فشل الرفع: ' . $e->getMessage());
        }
    }

    public function hostingerDeploy()
    {
        try {
            // نسخ الملفات إلى public_html
            $commands = [
                "rsync -av --exclude='node_modules' --exclude='.git' --exclude='vendor' {$this->projectPath}/ /home/u306850950/domains/mediumturquoise-porcupine-839487.hostingersite.com/public_html/",
                "cd /home/u306850950/domains/mediumturquoise-porcupine-839487.hostingersite.com/public_html && composer install --no-dev --optimize-autoloader",
                "cd /home/u306850950/domains/mediumturquoise-porcupine-839487.hostingersite.com/public_html && php artisan config:cache",
                "cd /home/u306850950/domains/mediumturquoise-porcupine-839487.hostingersite.com/public_html && php artisan route:cache",
                "cd /home/u306850950/domains/mediumturquoise-porcupine-839487.hostingersite.com/public_html && php artisan view:cache"
            ];
            
            $output = '';
            foreach ($commands as $command) {
                $output .= shell_exec($command . " 2>&1") . "\n";
            }
            
            // حفظ السجل
            SyncLog::create([
                'sync_type' => 'hostinger_deploy',
                'status' => 'success',
                'message' => 'تم النشر على Hostinger بنجاح',
                'details' => $output
            ]);
            
            return redirect()->route('sync')->with('success', 'تم النشر على Hostinger بنجاح ✅');
            
        } catch (\Exception $e) {
            SyncLog::create([
                'sync_type' => 'hostinger_deploy',
                'status' => 'failed',
                'message' => 'فشل النشر على Hostinger',
                'details' => $e->getMessage()
            ]);
            
            return redirect()->route('sync')->with('error', 'فشل النشر: ' . $e->getMessage());
        }
    }
    
    public function getLogs()
    {
        $logs = SyncLog::orderBy('created_at', 'desc')->take(50)->get();
        return response()->json($logs);
    }
}
