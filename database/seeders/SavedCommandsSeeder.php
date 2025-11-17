<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SavedCommand;

class SavedCommandsSeeder extends Seeder
{
    public function run(): void
    {
        $commands = [
            // Laravel Commands
            [
                'name' => 'تشغيل Migrations',
                'command' => 'php artisan migrate',
                'description' => 'تشغيل جميع migrations وإنشاء الجداول',
                'category' => 'laravel',
                'shell_type' => 'powershell',
                'icon' => '🗄️',
                'is_favorite' => true
            ],
            [
                'name' => 'تشغيل المشروع',
                'command' => 'php artisan serve',
                'description' => 'تشغيل سيرفر Laravel التطويري',
                'category' => 'laravel',
                'shell_type' => 'powershell',
                'icon' => '🚀',
                'is_favorite' => true
            ],
            [
                'name' => 'مسح Cache',
                'command' => 'php artisan cache:clear',
                'description' => 'مسح cache التطبيق',
                'category' => 'laravel',
                'shell_type' => 'powershell',
                'icon' => '🧹'
            ],
            [
                'name' => 'مسح Config Cache',
                'command' => 'php artisan config:clear',
                'description' => 'مسح cache الإعدادات',
                'category' => 'laravel',
                'shell_type' => 'powershell',
                'icon' => '⚙️'
            ],
            [
                'name' => 'مسح Route Cache',
                'command' => 'php artisan route:clear',
                'description' => 'مسح cache المسارات',
                'category' => 'laravel',
                'shell_type' => 'powershell',
                'icon' => '🛣️'
            ],
            [
                'name' => 'توليد مفتاح التطبيق',
                'command' => 'php artisan key:generate',
                'description' => 'توليد APP_KEY جديد',
                'category' => 'laravel',
                'shell_type' => 'powershell',
                'icon' => '🔑'
            ],
            
            // Database Commands
            [
                'name' => 'إنشاء قاعدة بيانات',
                'command' => 'mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS alwakeel_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"',
                'description' => 'إنشاء قاعدة بيانات جديدة',
                'category' => 'database',
                'shell_type' => 'powershell',
                'icon' => '🗃️',
                'is_favorite' => true
            ],
            [
                'name' => 'اختبار اتصال MySQL',
                'command' => 'mysql -u root -p -e "SELECT VERSION();"',
                'description' => 'التحقق من اتصال MySQL',
                'category' => 'database',
                'shell_type' => 'powershell',
                'icon' => '🔌'
            ],
            [
                'name' => 'عرض قواعد البيانات',
                'command' => 'mysql -u root -p -e "SHOW DATABASES;"',
                'description' => 'عرض جميع قواعد البيانات',
                'category' => 'database',
                'shell_type' => 'powershell',
                'icon' => '📊'
            ],
            [
                'name' => 'عرض الجداول',
                'command' => 'mysql -u root -p alwakeel_db -e "SHOW TABLES;"',
                'description' => 'عرض جميع الجداول في قاعدة البيانات',
                'category' => 'database',
                'shell_type' => 'powershell',
                'icon' => '📋'
            ],
            [
                'name' => 'نسخ احتياطي لقاعدة البيانات',
                'command' => 'mysqldump -u root -p alwakeel_db > backup_$(date +%Y%m%d_%H%M%S).sql',
                'description' => 'إنشاء نسخة احتياطية من قاعدة البيانات',
                'category' => 'database',
                'shell_type' => 'bash',
                'icon' => '💾'
            ],
            [
                'name' => 'استعادة قاعدة البيانات',
                'command' => 'mysql -u root -p alwakeel_db < backup.sql',
                'description' => 'استعادة قاعدة البيانات من نسخة احتياطية',
                'category' => 'database',
                'shell_type' => 'bash',
                'icon' => '♻️'
            ],
            
            // Composer Commands
            [
                'name' => 'تثبيت المكتبات',
                'command' => 'composer install',
                'description' => 'تثبيت جميع المكتبات من composer.json',
                'category' => 'composer',
                'shell_type' => 'powershell',
                'icon' => '📦',
                'is_favorite' => true
            ],
            [
                'name' => 'تحديث المكتبات',
                'command' => 'composer update',
                'description' => 'تحديث جميع المكتبات',
                'category' => 'composer',
                'shell_type' => 'powershell',
                'icon' => '🔄'
            ],
            [
                'name' => 'Dump Autoload',
                'command' => 'composer dump-autoload',
                'description' => 'إعادة تحميل autoload',
                'category' => 'composer',
                'shell_type' => 'powershell',
                'icon' => '🔃'
            ],
            
            // Git Commands
            [
                'name' => 'حالة Git',
                'command' => 'git status',
                'description' => 'عرض حالة المستودع',
                'category' => 'git',
                'shell_type' => 'powershell',
                'icon' => '📊'
            ],
            [
                'name' => 'سحب التحديثات',
                'command' => 'git pull origin main',
                'description' => 'سحب آخر التحديثات من GitHub',
                'category' => 'git',
                'shell_type' => 'powershell',
                'icon' => '⬇️'
            ],
            [
                'name' => 'رفع التحديثات',
                'command' => 'git add -A && git commit -m "Update" && git push origin main',
                'description' => 'رفع جميع التغييرات إلى GitHub',
                'category' => 'git',
                'shell_type' => 'powershell',
                'icon' => '⬆️'
            ],
            [
                'name' => 'عرض السجل',
                'command' => 'git log --oneline -10',
                'description' => 'عرض آخر 10 commits',
                'category' => 'git',
                'shell_type' => 'powershell',
                'icon' => '📜'
            ],
            
            // System Commands
            [
                'name' => 'عرض الملفات (PowerShell)',
                'command' => 'Get-ChildItem',
                'description' => 'عرض جميع الملفات والمجلدات',
                'category' => 'system',
                'shell_type' => 'powershell',
                'icon' => '📁'
            ],
            [
                'name' => 'عرض الملفات (CMD)',
                'command' => 'dir',
                'description' => 'عرض جميع الملفات والمجلدات',
                'category' => 'system',
                'shell_type' => 'cmd',
                'icon' => '📂'
            ],
            [
                'name' => 'إصدار PHP',
                'command' => 'php -v',
                'description' => 'عرض إصدار PHP المثبت',
                'category' => 'system',
                'shell_type' => 'powershell',
                'icon' => '🐘'
            ],
            [
                'name' => 'إصدار Node',
                'command' => 'node -v',
                'description' => 'عرض إصدار Node.js المثبت',
                'category' => 'system',
                'shell_type' => 'powershell',
                'icon' => '📗'
            ],
            [
                'name' => 'إصدار Composer',
                'command' => 'composer --version',
                'description' => 'عرض إصدار Composer المثبت',
                'category' => 'system',
                'shell_type' => 'powershell',
                'icon' => '🎼'
            ],
            [
                'name' => 'معلومات النظام',
                'command' => 'systeminfo',
                'description' => 'عرض معلومات تفصيلية عن النظام',
                'category' => 'system',
                'shell_type' => 'cmd',
                'icon' => '💻'
            ],
            
            // .env Configuration
            [
                'name' => 'عرض إعدادات قاعدة البيانات',
                'command' => 'Get-Content .env | Select-String "DB_"',
                'description' => 'عرض إعدادات قاعدة البيانات من .env',
                'category' => 'database',
                'shell_type' => 'powershell',
                'icon' => '⚙️'
            ],
            [
                'name' => 'تحديث اسم قاعدة البيانات',
                'command' => '(Get-Content .env) -replace "DB_DATABASE=.*", "DB_DATABASE=alwakeel_db" | Set-Content .env',
                'description' => 'تحديث DB_DATABASE في .env',
                'category' => 'database',
                'shell_type' => 'powershell',
                'icon' => '✏️'
            ],
            [
                'name' => 'تحديث مستخدم قاعدة البيانات',
                'command' => '(Get-Content .env) -replace "DB_USERNAME=.*", "DB_USERNAME=root" | Set-Content .env',
                'description' => 'تحديث DB_USERNAME في .env',
                'category' => 'database',
                'shell_type' => 'powershell',
                'icon' => '👤'
            ],
            [
                'name' => 'تحديث كلمة مرور قاعدة البيانات',
                'command' => '(Get-Content .env) -replace "DB_PASSWORD=.*", "DB_PASSWORD=" | Set-Content .env',
                'description' => 'تحديث DB_PASSWORD في .env',
                'category' => 'database',
                'shell_type' => 'powershell',
                'icon' => '🔒'
            ],
        ];

        foreach ($commands as $command) {
            SavedCommand::create($command);
        }
    }
}
