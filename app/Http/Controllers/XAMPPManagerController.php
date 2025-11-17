<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class XAMPPManagerController extends Controller
{
    private $xamppPath = 'C:/xampp';
    
    public function index()
    {
        $data = [
            'apache_status' => $this->checkApacheStatus(),
            'mysql_status' => $this->checkMySQLStatus(),
            'php_version' => phpversion(),
            'mysql_version' => $this->getMySQLVersion(),
            'apache_port' => $this->getApachePort(),
            'mysql_port' => $this->getMySQLPort(),
            'php_extensions' => $this->getPhpExtensions(),
        ];
        
        return view('xampp-manager', $data);
    }

    // Service Status
    public function checkApacheStatus()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = shell_exec('tasklist /FI "IMAGENAME eq httpd.exe" 2>&1');
            return strpos($output, 'httpd.exe') !== false;
        }
        return false;
    }

    public function checkMySQLStatus()
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getMySQLVersion()
    {
        try {
            $version = DB::select('SELECT VERSION() as version')[0]->version;
            return $version;
        } catch (\Exception $e) {
            return 'غير متصل';
        }
    }

    // Port Information
    public function getApachePort()
    {
        $httpdConf = $this->xamppPath . '/apache/conf/httpd.conf';
        if (File::exists($httpdConf)) {
            $content = File::get($httpdConf);
            preg_match('/Listen\s+(\d+)/', $content, $matches);
            return $matches[1] ?? '80';
        }
        return '80';
    }

    public function getMySQLPort()
    {
        $myIni = $this->xamppPath . '/mysql/bin/my.ini';
        if (File::exists($myIni)) {
            $content = File::get($myIni);
            preg_match('/port\s*=\s*(\d+)/', $content, $matches);
            return $matches[1] ?? '3306';
        }
        return '3306';
    }

    // PHP Extensions
    public function getPhpExtensions()
    {
        $extensions = get_loaded_extensions();
        $required = ['pdo_mysql', 'mysqli', 'mbstring', 'openssl', 'fileinfo', 'tokenizer', 'xml', 'ctype', 'json'];
        
        $result = [];
        foreach ($required as $ext) {
            $result[$ext] = in_array($ext, $extensions);
        }
        
        return $result;
    }

    public function enableExtension(Request $request)
    {
        $extension = $request->input('extension');
        $phpIni = $this->xamppPath . '/php/php.ini';
        
        if (!File::exists($phpIni)) {
            return response()->json([
                'success' => false,
                'error' => 'ملف php.ini غير موجود'
            ], 404);
        }
        
        $content = File::get($phpIni);
        $pattern = "/;extension={$extension}/";
        $replacement = "extension={$extension}";
        
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $replacement, $content);
            File::put($phpIni, $content);
            
            return response()->json([
                'success' => true,
                'message' => "تم تفعيل {$extension}. يرجى إعادة تشغيل Apache"
            ]);
        }
        
        return response()->json([
            'success' => false,
            'error' => 'Extension مفعل بالفعل أو غير موجود'
        ]);
    }

    // Database Operations
    public function getDatabases()
    {
        try {
            $databases = DB::select('SHOW DATABASES');
            return response()->json([
                'success' => true,
                'databases' => array_map(function($db) {
                    return $db->Database;
                }, $databases)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createDatabase(Request $request)
    {
        $dbName = $request->input('database_name');
        
        try {
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            return response()->json([
                'success' => true,
                'message' => "تم إنشاء قاعدة البيانات {$dbName} بنجاح"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function testConnection(Request $request)
    {
        $host = $request->input('host', '127.0.0.1');
        $port = $request->input('port', '3306');
        $username = $request->input('username', 'root');
        $password = $request->input('password', '');
        
        try {
            $pdo = new \PDO("mysql:host={$host};port={$port}", $username, $password);
            return response()->json([
                'success' => true,
                'message' => 'الاتصال ناجح!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Configuration Files
    public function getPhpInfo()
    {
        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();
        
        return response($phpinfo);
    }

    public function getPhpIniPath()
    {
        return response()->json([
            'path' => php_ini_loaded_file()
        ]);
    }

    // Quick Setup
    public function quickSetup(Request $request)
    {
        $results = [];
        
        // 1. تفعيل Extensions المطلوبة
        $requiredExtensions = ['pdo_mysql', 'mysqli', 'mbstring', 'openssl', 'fileinfo'];
        foreach ($requiredExtensions as $ext) {
            $this->enableExtension(new Request(['extension' => $ext]));
        }
        $results['extensions'] = 'تم تفعيل Extensions';
        
        // 2. إنشاء قاعدة البيانات
        try {
            DB::statement("CREATE DATABASE IF NOT EXISTS alwakeel_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $results['database'] = 'تم إنشاء قاعدة البيانات';
        } catch (\Exception $e) {
            $results['database'] = 'خطأ: ' . $e->getMessage();
        }
        
        // 3. تحديث .env
        $envFile = base_path('.env');
        if (File::exists($envFile)) {
            $content = File::get($envFile);
            $content = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE=alwakeel_db', $content);
            $content = preg_replace('/DB_USERNAME=.*/', 'DB_USERNAME=root', $content);
            $content = preg_replace('/DB_PASSWORD=.*/', 'DB_PASSWORD=', $content);
            File::put($envFile, $content);
            $results['env'] = 'تم تحديث .env';
        }
        
        return response()->json([
            'success' => true,
            'results' => $results,
            'message' => 'تم الإعداد بنجاح! يرجى إعادة تشغيل Apache'
        ]);
    }

    // Paths
    public function getPaths()
    {
        return response()->json([
            'xampp_path' => $this->xamppPath,
            'htdocs_path' => $this->xamppPath . '/htdocs',
            'php_ini_path' => php_ini_loaded_file(),
            'apache_conf_path' => $this->xamppPath . '/apache/conf/httpd.conf',
            'mysql_ini_path' => $this->xamppPath . '/mysql/bin/my.ini',
            'logs_path' => $this->xamppPath . '/apache/logs',
        ]);
    }
}
