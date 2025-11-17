<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class LaravelManagerController extends Controller
{
    public function index()
    {
        $data = [
            'migrations' => $this->getMigrations(),
            'routes_count' => count(Route::getRoutes()),
            'cache_size' => $this->getCacheSize(),
            'logs_size' => $this->getLogsSize(),
            'env_settings' => $this->getEnvSettings(),
        ];
        
        return view('laravel-manager', $data);
    }

    // Artisan Commands
    public function runArtisan(Request $request)
    {
        $command = $request->input('command');
        
        try {
            Artisan::call($command);
            $output = Artisan::output();
            
            return response()->json([
                'success' => true,
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Migrations
    public function getMigrations()
    {
        $migrationsPath = database_path('migrations');
        $files = File::files($migrationsPath);
        
        $migrations = [];
        foreach ($files as $file) {
            $migrations[] = [
                'name' => $file->getFilename(),
                'path' => $file->getPathname(),
                'size' => $file->getSize(),
                'modified' => date('Y-m-d H:i:s', $file->getMTime())
            ];
        }
        
        return $migrations;
    }

    public function runMigrations()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            return response()->json([
                'success' => true,
                'output' => Artisan::output()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function rollbackMigrations()
    {
        try {
            Artisan::call('migrate:rollback');
            return response()->json([
                'success' => true,
                'output' => Artisan::output()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Routes
    public function getRoutes()
    {
        $routes = [];
        foreach (Route::getRoutes() as $route) {
            $routes[] = [
                'method' => implode('|', $route->methods()),
                'uri' => $route->uri(),
                'name' => $route->getName(),
                'action' => $route->getActionName()
            ];
        }
        
        return response()->json($routes);
    }

    // Cache Management
    public function getCacheSize()
    {
        $cachePath = storage_path('framework/cache');
        if (!File::exists($cachePath)) {
            return '0 KB';
        }
        
        $size = 0;
        foreach (File::allFiles($cachePath) as $file) {
            $size += $file->getSize();
        }
        
        return $this->formatBytes($size);
    }

    public function clearCache(Request $request)
    {
        $type = $request->input('type', 'all');
        
        try {
            switch ($type) {
                case 'config':
                    Artisan::call('config:clear');
                    break;
                case 'route':
                    Artisan::call('route:clear');
                    break;
                case 'view':
                    Artisan::call('view:clear');
                    break;
                case 'cache':
                    Artisan::call('cache:clear');
                    break;
                default:
                    Artisan::call('config:clear');
                    Artisan::call('route:clear');
                    Artisan::call('view:clear');
                    Artisan::call('cache:clear');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'تم مسح الـ Cache بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Logs
    public function getLogsSize()
    {
        $logsPath = storage_path('logs');
        if (!File::exists($logsPath)) {
            return '0 KB';
        }
        
        $size = 0;
        foreach (File::allFiles($logsPath) as $file) {
            $size += $file->getSize();
        }
        
        return $this->formatBytes($size);
    }

    public function getLogs()
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (!File::exists($logFile)) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد سجلات'
            ]);
        }
        
        $logs = File::get($logFile);
        $lines = explode("\n", $logs);
        $lastLines = array_slice($lines, -100); // آخر 100 سطر
        
        return response()->json([
            'success' => true,
            'logs' => implode("\n", $lastLines)
        ]);
    }

    public function clearLogs()
    {
        $logsPath = storage_path('logs');
        
        try {
            File::cleanDirectory($logsPath);
            return response()->json([
                'success' => true,
                'message' => 'تم مسح السجلات بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Environment
    public function getEnvSettings()
    {
        $envFile = base_path('.env');
        if (!File::exists($envFile)) {
            return [];
        }
        
        $content = File::get($envFile);
        $lines = explode("\n", $content);
        
        $settings = [];
        foreach ($lines as $line) {
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $settings[$key] = $value;
            }
        }
        
        return $settings;
    }

    public function updateEnv(Request $request)
    {
        $key = $request->input('key');
        $value = $request->input('value');
        
        $envFile = base_path('.env');
        $content = File::get($envFile);
        
        // استبدال القيمة
        $pattern = "/^{$key}=.*/m";
        $replacement = "{$key}={$value}";
        
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $replacement, $content);
        } else {
            $content .= "\n{$replacement}";
        }
        
        File::put($envFile, $content);
        
        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الإعدادات بنجاح'
        ]);
    }

    // Helper
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
