# 🏗️ الهيكل المعماري - Architecture Documentation

> **وثائق تفصيلية للبنية المعمارية لمشروع الوكيل الذكي المحلي**

---

## 📋 جدول المحتويات

- [نظرة عامة](#نظرة-عامة)
- [الطبقات المعمارية](#الطبقات-المعمارية)
- [مخطط التدفق](#مخطط-التدفق)
- [المكونات الرئيسية](#المكونات-الرئيسية)
- [قاعدة البيانات](#قاعدة-البيانات)
- [APIs والتكامل](#apis-والتكامل)
- [الأمان](#الأمان)

---

## 🌐 نظرة عامة

### البنية الكلية

```
┌─────────────────────────────────────────────────────┐
│              User Interface Layer                    │
│  (alwakeel.php, chat.php, integrations.php, etc.)   │
└──────────────────┬──────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────┐
│           Business Logic Layer                       │
│  (sync_engine.php, ai_engine.php, api.php)          │
└──────────────────┬──────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────┐
│            Data Access Layer                         │
│              (config.php, PDO)                       │
└──────────────────┬──────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────┐
│              Database Layer                          │
│           (MySQL - alwakeel_db)                      │
└─────────────────────────────────────────────────────┘
```

---

## 🏛️ الطبقات المعمارية

### 1. طبقة واجهة المستخدم (UI Layer)

**الملفات:**
- `alwakeel.php` - الواجهة الرئيسية
- `chat.php` - واجهة الدردشة
- `integrations.php` - إدارة الإعدادات
- `sync_engine.php` - واجهة المزامنة
- `ai_engine.php` - واجهة اختبار AI
- `backup.php` - واجهة النسخ الاحتياطي
- `migrate.php` - واجهة الترحيل

**المسؤوليات:**
- عرض البيانات للمستخدم
- استقبال المدخلات
- التفاعل مع طبقة المنطق

### 2. طبقة المنطق التجاري (Business Logic Layer)

**المكونات:**

#### SyncEngine Class
```php
class SyncEngine {
    - loadIntegrations()
    - githubPull()
    - githubPush()
    - hostingerDeploy()
    - createBackup()
    - logSync()
}
```

#### AIEngine Class
```php
class AIEngine {
    - routeRequest()
    - processOllama()
    - processCopilot()
    - processLocal()
    - learn()
    - extractPatterns()
    - logCommand()
}
```

### 3. طبقة الوصول للبيانات (Data Access Layer)

**الملف:** `config.php`

```php
// اتصال PDO آمن
$conn = new PDO(
    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
    $username,
    $password,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);
```

### 4. طبقة قاعدة البيانات (Database Layer)

**النظام:** MySQL 5.7+  
**الترميز:** UTF8MB4  
**المحرك:** InnoDB

---

## 🔄 مخطط التدفق

### تدفق معالجة الأوامر

```
User Input (chat.php)
       ↓
   AJAX Request
       ↓
   chat.php (POST)
       ↓
Save to commands table
       ↓
processMessage()
       ↓
    ┌──────┴──────┐
    │             │
Simple?      Complex?
    │             │
    ↓             ↓
Local       ai_engine.php
Processing       ↓
    │        routeRequest()
    │             ↓
    │      ┌──────┴──────┐
    │      │             │
    │   Ollama      Copilot
    │      │             │
    │      └──────┬──────┘
    │             │
    └──────┬──────┘
           ↓
  Save to results table
           ↓
   Update command status
           ↓
    Learn from pattern
           ↓
   Return JSON response
           ↓
   Display in chat UI
```

### تدفق المزامنة مع GitHub

```
User clicks "Pull"
       ↓
sync_engine.php (AJAX)
       ↓
SyncEngine::githubPull()
       ↓
Log sync (status: running)
       ↓
GitHub API Request
       ↓
Download ZIP
       ↓
Extract files
       ↓
Log sync (status: success)
       ↓
Return result
       ↓
Update UI
```

---

## 🧩 المكونات الرئيسية

### 1. محرك المزامنة (Sync Engine)

**الوظائف:**

| الوظيفة | الوصف | التقنية |
|---------|-------|---------|
| `githubPull()` | سحب من GitHub | GitHub API + cURL |
| `githubPush()` | رفع إلى GitHub | Git CLI |
| `hostingerDeploy()` | نشر على Hostinger | FTP (ftp_*) |
| `createBackup()` | نسخة احتياطية | ZipArchive |

**التدفق:**

```php
// مثال: GitHub Pull
1. تحميل الإعدادات من integrations
2. بناء URL للـ API
3. إرسال طلب مع Token
4. تحميل ZIP
5. فك الضغط
6. تسجيل النتيجة في sync_logs
7. إرجاع النتيجة
```

### 2. محرك الذكاء الاصطناعي (AI Engine)

**استراتيجية الاختيار:**

```php
function routeRequest($message) {
    $is_complex = strlen($message) > 200 || 
                 contains_analysis_keywords($message);
    
    if ($is_complex && copilot_enabled) {
        return processCopilot($message);
    } elseif (ollama_enabled) {
        return processOllama($message);
    } else {
        return processLocal($message);
    }
}
```

**نظام التعلم:**

```
Command Execution
       ↓
Extract Patterns
       ↓
Check if pattern exists
       ↓
   ┌────┴────┐
   │         │
 Exists   New
   │         │
   ↓         ↓
Update   Insert
frequency  pattern
confidence
   │         │
   └────┬────┘
        ↓
Save to learning_data
```

### 3. نظام الربط (Integrations)

**البنية:**

```json
{
  "service_name": "github",
  "is_enabled": "true",
  "config": {
    "token": "ghp_xxx",
    "repo": "user/repo",
    "branch": "main"
  }
}
```

**الخدمات المدعومة:**

1. **GitHub**
   - Token-based authentication
   - API v3
   - ZIP download

2. **Hostinger**
   - FTP connection
   - Passive mode
   - Binary transfer

3. **Ollama**
   - Local HTTP API
   - Model: llama2
   - Streaming support

4. **Copilot**
   - OpenAI-compatible API
   - Model: gpt-3.5-turbo
   - Token counting

---

## 🗄️ قاعدة البيانات

### مخطط ER (Entity-Relationship)

```
┌─────────────┐
│  commands   │
│─────────────│
│ id (PK)     │──┐
│ command_text│  │
│ status      │  │
│ created_at  │  │
└─────────────┘  │
                 │ 1:N
                 │
      ┌──────────┴──────────┐
      │                     │
      ▼                     ▼
┌─────────────┐      ┌──────────────────┐
│   results   │      │ command_history  │
│─────────────│      │──────────────────│
│ id (PK)     │      │ id (PK)          │
│ command_id  │      │ command_id (FK)  │
│ result_text │      │ context          │
│ created_at  │      │ ai_engine        │
└─────────────┘      │ execution_time   │
                     │ success          │
                     └──────────────────┘

┌──────────────────┐      ┌──────────────┐
│  integrations    │      │ sync_logs    │
│──────────────────│      │──────────────│
│ id (PK)          │      │ id (PK)      │
│ service_name (U) │      │ service      │
│ is_enabled       │      │ action       │
│ config (JSON)    │      │ status       │
│ created_at       │      │ details      │
│ updated_at       │      │ started_at   │
└──────────────────┘      └──────────────┘

┌──────────────────┐
│  learning_data   │
│──────────────────│
│ id (PK)          │
│ pattern (U)      │
│ suggestion       │
│ frequency        │
│ confidence       │
│ category         │
│ created_at       │
│ updated_at       │
└──────────────────┘
```

### الفهارس (Indexes)

```sql
-- commands
INDEX idx_status (status)
INDEX idx_created (created_at)

-- results
INDEX idx_command (command_id)

-- command_history
INDEX idx_command (command_id)
INDEX idx_engine (ai_engine)

-- sync_logs
INDEX idx_service (service)
INDEX idx_status (status)

-- learning_data
UNIQUE INDEX idx_pattern (pattern)
INDEX idx_confidence (confidence)
```

---

## 🔌 APIs والتكامل

### 1. GitHub API

**Endpoint:** `https://api.github.com`

**الطلبات المستخدمة:**

```http
GET /repos/{owner}/{repo}/zipball/{branch}
Authorization: token {personal_access_token}
User-Agent: Alwakeel-PHP-Agent
```

### 2. Ollama API

**Endpoint:** `http://localhost:11434`

**الطلبات المستخدمة:**

```http
POST /api/generate
Content-Type: application/json

{
  "model": "llama2",
  "prompt": "...",
  "stream": false
}
```

### 3. OpenAI API (Copilot)

**Endpoint:** `https://api.openai.com/v1`

**الطلبات المستخدمة:**

```http
POST /chat/completions
Authorization: Bearer {api_key}
Content-Type: application/json

{
  "model": "gpt-3.5-turbo",
  "messages": [...],
  "temperature": 0.7
}
```

### 4. Internal API (api.php)

**Endpoints:**

```php
POST /api.php?action=execute
POST /api.php?action=get_results
POST /api.php?action=get_history
```

---

## 🔒 الأمان

### 1. حماية قاعدة البيانات

```php
// ✅ استخدام PDO Prepared Statements
$stmt = $conn->prepare("SELECT * FROM commands WHERE id = :id");
$stmt->execute([':id' => $id]);

// ❌ تجنب الاستعلامات المباشرة
// $result = $conn->query("SELECT * FROM commands WHERE id = $id");
```

### 2. تشفير البيانات الحساسة

```php
// تخزين الإعدادات كـ JSON
$config = json_encode([
    'token' => $token,
    'repo' => $repo
], JSON_UNESCAPED_UNICODE);
```

### 3. التحقق من المدخلات

```php
// التحقق من وجود البيانات
if (!isset($_POST['action']) || empty($_POST['message'])) {
    http_response_code(400);
    exit(json_encode(['error' => 'Invalid request']));
}

// تنظيف المدخلات
$message = trim($_POST['message']);
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
```

### 4. معالجة الأخطاء

```php
try {
    // العمليات الحساسة
} catch (PDOException $e) {
    // تسجيل الخطأ بدلاً من عرضه
    error_log("Database error: " . $e->getMessage());
    
    // إرجاع رسالة عامة
    return ['success' => false, 'message' => 'حدث خطأ في النظام'];
}
```

---

## 📊 مقاييس الأداء

### أوقات الاستجابة المتوقعة

| العملية | الوقت المتوقع |
|---------|---------------|
| معالجة أمر بسيط | < 100ms |
| معالجة عبر Ollama | 1-3s |
| معالجة عبر Copilot | 2-5s |
| GitHub Pull | 3-10s |
| GitHub Push | 5-15s |
| Hostinger Deploy | 10-30s |
| Backup | 2-5s |

### تحسينات الأداء

✅ **استخدام Indexes** في قاعدة البيانات  
✅ **Caching** للإعدادات  
✅ **Lazy Loading** للبيانات الكبيرة  
✅ **Async Processing** للعمليات الطويلة  

---

## 🔄 التوسعات المستقبلية

### مخطط التوسع

```
Current Architecture
       ↓
   Add Queue System
       ↓
   Add Redis Cache
       ↓
   Add WebSocket
       ↓
   Add Microservices
       ↓
   Add Load Balancer
```

### المميزات المخططة

- [ ] نظام Queues للعمليات الطويلة
- [ ] WebSocket للتحديثات الفورية
- [ ] Redis للـ Caching
- [ ] Docker للـ Deployment
- [ ] Kubernetes للـ Orchestration

---

<div align="center">

**الهيكل المعماري - الإصدار 1.0**

صُنع بـ ❤️ في فلسطين 🇵🇸

</div>
