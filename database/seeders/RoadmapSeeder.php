<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoadmapItem;

class RoadmapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // حذف البيانات القديمة
        RoadmapItem::truncate();

        // ========================================
        // نظام الأباسي المحاسبي
        // ========================================
        
        // المرحلة 1: التحديث الأساسي
        $this->createItem('alabasi', 'المرحلة 1: التحديث الأساسي', 1, 'Form Requests', 'فصل التحقق من البيانات عن Controllers', 1, 1, 'pending', 0);
        $this->createItem('alabasi', 'المرحلة 1: التحديث الأساسي', 1, 'PHP Enums', 'استخدام Enums للثوابت الآمنة', 2, 1, 'pending', 0);
        $this->createItem('alabasi', 'المرحلة 1: التحديث الأساسي', 1, 'Service Layer', 'فصل منطق العمل عن Controllers', 3, 2, 'pending', 0);
        $this->createItem('alabasi', 'المرحلة 1: التحديث الأساسي', 1, 'Query Scopes', 'تبسيط الاستعلامات المتكررة', 4, 1, 'pending', 0);
        $this->createItem('alabasi', 'المرحلة 1: التحديث الأساسي', 1, 'Activity Log System', 'تتبع جميع العمليات في النظام', 5, 2, 'pending', 0);

        // المرحلة 2: بناء API
        $this->createItem('alabasi', 'المرحلة 2: بناء API', 2, 'Authentication - Sanctum', 'نظام مصادقة API باستخدام Laravel Sanctum', 1, 2, 'pending', 0);
        $this->createItem('alabasi', 'المرحلة 2: بناء API', 2, 'Accounts API', 'API لإدارة دليل الحسابات', 2, 2, 'pending', 0);
        $this->createItem('alabasi', 'المرحلة 2: بناء API', 2, 'Vouchers API', 'API لإدارة السندات (قبض وصرف)', 3, 2, 'pending', 0);
        $this->createItem('alabasi', 'المرحلة 2: بناء API', 2, 'Journal Entries API', 'API لإدارة القيود اليومية', 4, 2, 'pending', 0);
        $this->createItem('alabasi', 'المرحلة 2: بناء API', 2, 'Reports API', 'API للتقارير المالية', 5, 2, 'pending', 0);
        $this->createItem('alabasi', 'المرحلة 2: بناء API', 2, 'API Documentation', 'توثيق شامل لجميع endpoints', 6, 2, 'pending', 0);

        // المرحلة 5: الميزات المتقدمة
        $this->createItem('alabasi', 'المرحلة 5: الميزات المتقدمة', 5, 'الصناديق والبنوك', 'نظام إدارة الصناديق والحسابات البنكية', 1, 7, 'pending', 0);
        $this->createItem('alabasi', 'المرحلة 5: الميزات المتقدمة', 5, 'التقارير المالية المتقدمة', 'ميزان المراجعة، قائمة الدخل، الميزانية العمومية', 2, 14, 'pending', 0);
        $this->createItem('alabasi', 'المرحلة 5: الميزات المتقدمة', 5, 'السندات الذكية', 'ربط تلقائي بالقيود واقتراحات ذكية', 3, 7, 'pending', 0);
        $this->createItem('alabasi', 'المرحلة 5: الميزات المتقدمة', 5, 'لوحة تحكم تفاعلية', 'رسوم بيانية وإحصائيات حية', 4, 7, 'pending', 0);

        // ========================================
        // الربط والتكامل
        // ========================================
        
        // المرحلة 4: الربط والتكامل
        $this->createItem('integration', 'المرحلة 4: الربط والتكامل', 4, 'API Client في الوكيل', 'بناء عميل API للتواصل مع نظام الأباسي', 1, 7, 'pending', 0);
        $this->createItem('integration', 'المرحلة 4: الربط والتكامل', 4, 'Authentication Flow', 'تدفق المصادقة بين النظامين', 2, 2, 'pending', 0);
        $this->createItem('integration', 'المرحلة 4: الربط والتكامل', 4, 'Data Sync', 'مزامنة البيانات بين النظامين', 3, 3, 'pending', 0);
        $this->createItem('integration', 'المرحلة 4: الربط والتكامل', 4, 'تدريب الوكيل - العمليات المحاسبية', 'تعليم الوكيل المفاهيم المحاسبية', 4, 3, 'pending', 0);
        $this->createItem('integration', 'المرحلة 4: الربط والتكامل', 4, 'تدريب الوكيل - إنشاء السندات', 'تعليم الوكيل كيفية إنشاء السندات', 5, 3, 'pending', 0);
        $this->createItem('integration', 'المرحلة 4: الربط والتكامل', 4, 'تدريب الوكيل - القيود اليومية', 'تعليم الوكيل كيفية إنشاء القيود', 6, 3, 'pending', 0);
        $this->createItem('integration', 'المرحلة 4: الربط والتكامل', 4, 'تدريب الوكيل - التقارير', 'تعليم الوكيل كيفية توليد التقارير', 7, 3, 'pending', 0);

        // ========================================
        // نظام الوكيل
        // ========================================
        
        // المرحلة 3: تطوير الوكيل
        $this->createItem('wakeel', 'المرحلة 3: تطوير الوكيل', 3, 'قاعدة بيانات المحادثات', 'جدول لحفظ جميع المحادثات', 1, 2, 'pending', 0);
        $this->createItem('wakeel', 'المرحلة 3: تطوير الوكيل', 3, 'Pattern Recognition Engine', 'محرك التعرف على الأنماط', 2, 5, 'pending', 0);
        $this->createItem('wakeel', 'المرحلة 3: تطوير الوكيل', 3, 'Learning Engine', 'محرك التعلم الذاتي', 3, 7, 'pending', 0);
        $this->createItem('wakeel', 'المرحلة 3: تطوير الوكيل', 3, 'Caching System', 'نظام التخزين المؤقت الذكي', 4, 3, 'pending', 0);
        $this->createItem('wakeel', 'المرحلة 3: تطوير الوكيل', 3, 'UI Testing - Puppeteer', 'نظام اختبار الواجهات التلقائي', 5, 5, 'pending', 0);
        $this->createItem('wakeel', 'المرحلة 3: تطوير الوكيل', 3, 'Test Scenarios', 'سيناريوهات الاختبار', 6, 3, 'pending', 0);
        $this->createItem('wakeel', 'المرحلة 3: تطوير الوكيل', 3, 'Report Generator', 'مولد تقارير الاختبار', 7, 2, 'pending', 0);
        $this->createItem('wakeel', 'المرحلة 3: تطوير الوكيل', 3, 'GitHub Integration', 'تكامل مع GitHub للنشر', 8, 3, 'pending', 0);
        $this->createItem('wakeel', 'المرحلة 3: تطوير الوكيل', 3, 'Hostinger Deployment', 'نشر تلقائي على Hostinger', 9, 3, 'pending', 0);
        $this->createItem('wakeel', 'المرحلة 3: تطوير الوكيل', 3, 'Database Migration', 'ترحيل قواعد البيانات تلقائياً', 10, 2, 'pending', 0);

        // المرحلة 5: الميزات المتقدمة للوكيل
        $this->createItem('wakeel', 'المرحلة 5: الميزات المتقدمة', 5, 'المساعد الصوتي', 'التعرف على الصوت والرد الصوتي', 1, 7, 'pending', 0);
        $this->createItem('wakeel', 'المرحلة 5: الميزات المتقدمة', 5, 'التنبؤ الذكي', 'توقع المصروفات والإيرادات', 2, 7, 'pending', 0);
        $this->createItem('wakeel', 'المرحلة 5: الميزات المتقدمة', 5, 'التوصيات التلقائية', 'اقتراح تحسينات وتحذيرات', 3, 5, 'pending', 0);
        $this->createItem('wakeel', 'المرحلة 5: الميزات المتقدمة', 5, 'Multi-System Support', 'دعم أنظمة متعددة', 4, 10, 'pending', 0);
    }

    private function createItem($project, $phase, $phaseNumber, $title, $description, $order, $estimatedDays, $status = 'pending', $progress = 0)
    {
        RoadmapItem::create([
            'project' => $project,
            'phase' => $phase,
            'phase_number' => $phaseNumber,
            'title' => $title,
            'description' => $description,
            'order' => $order,
            'estimated_days' => $estimatedDays,
            'status' => $status,
            'progress' => $progress,
        ]);
    }
}
