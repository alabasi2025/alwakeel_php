<?php
/**
 * واجهة API لنظام الوكيل
 * Agent Interface API
 */

require_once 'config.php';

// السماح بطلبات CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// معالجة طلبات OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// الحصول على المعاملات
$action = isset($_GET['action']) ? cleanInput($_GET['action']) : '';
$method = $_SERVER['REQUEST_METHOD'];

// الحصول على البيانات من POST
$postData = json_decode(file_get_contents('php://input'), true);

// الاتصال بقاعدة البيانات
$pdo = getDBConnection();

try {
    switch ($action) {
        
        // ========== الأوامر (Commands) ==========
        
        case 'get_commands':
            // الحصول على جميع الأوامر
            $stmt = $pdo->query("SELECT * FROM commands ORDER BY created_at DESC");
            $commands = $stmt->fetchAll();
            jsonResponse([
                'success' => true,
                'data' => $commands,
                'count' => count($commands)
            ]);
            break;
            
        case 'add_command':
            // إضافة أمر جديد
            if ($method !== 'POST') {
                jsonResponse(['success' => false, 'error' => 'يجب استخدام POST'], 405);
            }
            
            $commandText = isset($postData['command_text']) ? trim($postData['command_text']) : '';
            
            if (empty($commandText)) {
                jsonResponse(['success' => false, 'error' => 'نص الأمر مطلوب'], 400);
            }
            
            $stmt = $pdo->prepare("INSERT INTO commands (command_text, status) VALUES (?, 'pending')");
            $stmt->execute([$commandText]);
            
            $commandId = $pdo->lastInsertId();
            
            jsonResponse([
                'success' => true,
                'message' => 'تم إضافة الأمر بنجاح',
                'command_id' => $commandId
            ]);
            break;
            
        case 'update_command_status':
            // تحديث حالة أمر
            if ($method !== 'POST') {
                jsonResponse(['success' => false, 'error' => 'يجب استخدام POST'], 405);
            }
            
            $commandId = isset($postData['command_id']) ? (int)$postData['command_id'] : 0;
            $status = isset($postData['status']) ? cleanInput($postData['status']) : '';
            
            if ($commandId <= 0 || empty($status)) {
                jsonResponse(['success' => false, 'error' => 'معرف الأمر والحالة مطلوبان'], 400);
            }
            
            $validStatuses = ['pending', 'processing', 'completed', 'failed'];
            if (!in_array($status, $validStatuses)) {
                jsonResponse(['success' => false, 'error' => 'حالة غير صالحة'], 400);
            }
            
            $stmt = $pdo->prepare("UPDATE commands SET status = ? WHERE id = ?");
            $stmt->execute([$status, $commandId]);
            
            jsonResponse([
                'success' => true,
                'message' => 'تم تحديث حالة الأمر بنجاح'
            ]);
            break;
            
        // ========== النتائج (Results) ==========
        
        case 'get_results':
            // الحصول على جميع النتائج
            $stmt = $pdo->query("
                SELECT r.*, c.command_text, c.status as command_status
                FROM results r
                LEFT JOIN commands c ON r.command_id = c.id
                ORDER BY r.created_at DESC
            ");
            $results = $stmt->fetchAll();
            jsonResponse([
                'success' => true,
                'data' => $results,
                'count' => count($results)
            ]);
            break;
            
        case 'get_results_by_command':
            // الحصول على نتائج أمر معين
            $commandId = isset($_GET['command_id']) ? (int)$_GET['command_id'] : 0;
            
            if ($commandId <= 0) {
                jsonResponse(['success' => false, 'error' => 'معرف الأمر مطلوب'], 400);
            }
            
            $stmt = $pdo->prepare("SELECT * FROM results WHERE command_id = ? ORDER BY created_at DESC");
            $stmt->execute([$commandId]);
            $results = $stmt->fetchAll();
            
            jsonResponse([
                'success' => true,
                'data' => $results,
                'count' => count($results)
            ]);
            break;
            
        case 'add_result':
            // إضافة نتيجة جديدة
            if ($method !== 'POST') {
                jsonResponse(['success' => false, 'error' => 'يجب استخدام POST'], 405);
            }
            
            $commandId = isset($postData['command_id']) ? (int)$postData['command_id'] : 0;
            $resultText = isset($postData['result_text']) ? trim($postData['result_text']) : '';
            
            if ($commandId <= 0 || empty($resultText)) {
                jsonResponse(['success' => false, 'error' => 'معرف الأمر ونص النتيجة مطلوبان'], 400);
            }
            
            // التحقق من وجود الأمر
            $stmt = $pdo->prepare("SELECT id FROM commands WHERE id = ?");
            $stmt->execute([$commandId]);
            if (!$stmt->fetch()) {
                jsonResponse(['success' => false, 'error' => 'الأمر غير موجود'], 404);
            }
            
            $stmt = $pdo->prepare("INSERT INTO results (command_id, result_text) VALUES (?, ?)");
            $stmt->execute([$commandId, $resultText]);
            
            $resultId = $pdo->lastInsertId();
            
            jsonResponse([
                'success' => true,
                'message' => 'تم إضافة النتيجة بنجاح',
                'result_id' => $resultId
            ]);
            break;
            
        // ========== شل SQL ==========
        
        case 'execute_sql':
            // تنفيذ استعلام SQL مخصص
            if ($method !== 'POST') {
                jsonResponse(['success' => false, 'error' => 'يجب استخدام POST'], 405);
            }
            
            $query = isset($postData['query']) ? trim($postData['query']) : '';
            
            if (empty($query)) {
                jsonResponse(['success' => false, 'error' => 'استعلام SQL مطلوب'], 400);
            }
            
            // منع استعلامات خطرة
            $dangerousKeywords = ['DROP', 'TRUNCATE', 'DELETE FROM users', 'ALTER'];
            foreach ($dangerousKeywords as $keyword) {
                if (stripos($query, $keyword) !== false) {
                    jsonResponse([
                        'success' => false,
                        'error' => 'استعلام غير مسموح به لأسباب أمنية'
                    ], 403);
                }
            }
            
            try {
                $stmt = $pdo->query($query);
                
                // إذا كان استعلام SELECT
                if (stripos(trim($query), 'SELECT') === 0) {
                    $data = $stmt->fetchAll();
                    jsonResponse([
                        'success' => true,
                        'data' => $data,
                        'rows_count' => count($data)
                    ]);
                } else {
                    // استعلامات INSERT, UPDATE, DELETE
                    $affectedRows = $stmt->rowCount();
                    jsonResponse([
                        'success' => true,
                        'message' => 'تم تنفيذ الاستعلام بنجاح',
                        'affected_rows' => $affectedRows
                    ]);
                }
            } catch (PDOException $e) {
                jsonResponse([
                    'success' => false,
                    'error' => 'خطأ في تنفيذ الاستعلام: ' . $e->getMessage()
                ], 400);
            }
            break;
            
        // ========== إحصائيات ==========
        
        case 'get_stats':
            // الحصول على إحصائيات عامة
            $stats = [];
            
            // عدد الأوامر
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM commands");
            $stats['total_commands'] = $stmt->fetch()['total'];
            
            // عدد الأوامر حسب الحالة
            $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM commands GROUP BY status");
            $stats['commands_by_status'] = $stmt->fetchAll();
            
            // عدد النتائج
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM results");
            $stats['total_results'] = $stmt->fetch()['total'];
            
            jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
            break;
            
        default:
            jsonResponse([
                'success' => false,
                'error' => 'إجراء غير معروف',
                'available_actions' => [
                    'get_commands',
                    'add_command',
                    'update_command_status',
                    'get_results',
                    'get_results_by_command',
                    'add_result',
                    'execute_sql',
                    'get_stats'
                ]
            ], 400);
    }
    
} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'error' => 'خطأ في الخادم: ' . $e->getMessage()
    ], 500);
}
?>
