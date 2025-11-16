# 📦 دليل التثبيت الكامل - واجهة الوكيل

## 🎯 المتطلبات

- ✅ **XAMPP** (يحتوي على Apache + MySQL + PHP)
- ✅ **Windows PowerShell** (مدمج في Windows)
- ✅ **متصفح ويب** (Chrome, Firefox, Edge)

---

## 🚀 التثبيت السريع (أمر واحد)

### الخطوة 1: تحميل وتثبيت XAMPP

إذا لم يكن XAMPP مثبتاً:
```powershell
Start-Process "https://www.apachefriends.org/download.html"
```

### الخطوة 2: تحميل المشروع من GitHub

```powershell
# تحميل المشروع
cd $HOME\Downloads
Invoke-WebRequest -Uri "https://github.com/alabasi2025/alwakeel_php/archive/refs/heads/main.zip" -OutFile "alwakeel_php.zip"

# فك الضغط إلى XAMPP
Expand-Archive -Path "alwakeel_php.zip" -DestinationPath "C:\xampp\htdocs\" -Force

# إعادة التسمية
Rename-Item -Path "C:\xampp\htdocs\alwakeel_php-main" -NewName "alwakeel_php" -Force

Write-Host "✅ تم تحميل المشروع!" -ForegroundColor Green
```

### الخطوة 3: تشغيل XAMPP

```powershell
Start-Process "C:\xampp\xampp-control.exe"
```

**في XAMPP Control Panel:**
- شغّل **Apache** (اضغط Start)
- شغّل **MySQL** (اضغط Start)

### الخطوة 4: إنشاء قاعدة البيانات تلقائياً

```powershell
# إنشاء القاعدة واستيراد الجداول
$mysql = "C:\xampp\mysql\bin\mysql.exe"
$db = "alwakeel_db"

# إنشاء القاعدة
& $mysql -u root -e "DROP DATABASE IF EXISTS ``$db``; CREATE DATABASE ``$db`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# استيراد الجداول
Get-Content "C:\xampp\htdocs\alwakeel_php\database.sql" -Raw | & $mysql -u root $db

Write-Host "✅ تم إنشاء قاعدة البيانات!" -ForegroundColor Green
```

### الخطوة 5: فتح الموقع

```powershell
Start-Process "http://localhost/alwakeel_php/alwakeel.php"
```

---

## 🔧 تغيير البورت (اختياري)

إذا كنت تريد استخدام بورت مختلف (مثل 8765):

```powershell
$newPort = 8765
$httpdConf = "C:\xampp\apache\conf\httpd.conf"

# نسخة احتياطية
Copy-Item $httpdConf "$httpdConf.backup" -Force

# تغيير البورت
$content = Get-Content $httpdConf
$content = $content -replace '^Listen\s+\d+', "Listen $newPort"
$content = $content -replace 'ServerName\s+localhost:\d+', "ServerName localhost:$newPort"
$content | Set-Content $httpdConf -Encoding UTF8

Write-Host "✅ تم تغيير البورت إلى $newPort" -ForegroundColor Green
Write-Host "أعد تشغيل Apache من XAMPP Control Panel" -ForegroundColor Yellow
```

---

## 📡 روابط النظام

بعد التثبيت:

| الوظيفة | الرابط |
|---------|--------|
| **الواجهة الرئيسية** | `http://localhost/alwakeel_php/alwakeel.php` |
| **API** | `http://localhost/alwakeel_php/api.php` |
| **النسخ الاحتياطي** | `http://localhost/alwakeel_php/backup.php` |
| **اختبار القاعدة** | `http://localhost/alwakeel_php/test.php` |
| **phpMyAdmin** | `http://localhost/phpmyadmin/` |

*(إذا غيرت البورت، استبدل `localhost` بـ `localhost:8765`)*

---

## 🧪 اختبار API

```powershell
$api = "http://localhost/alwakeel_php/api.php"

# إضافة أمر
$cmd = @{ command_text = "أمر تجريبي" } | ConvertTo-Json
Invoke-RestMethod -Uri "$api?action=add_command" -Method POST -Body $cmd -ContentType "application/json"

# الحصول على الأوامر
Invoke-RestMethod -Uri "$api?action=get_commands"

# الإحصائيات
Invoke-RestMethod -Uri "$api?action=get_stats"
```

---

## 🔄 التحديث من GitHub

```powershell
# نسخة احتياطية
Copy-Item "C:\xampp\htdocs\alwakeel_php" "C:\xampp\htdocs\alwakeel_php_backup" -Recurse -Force

# تحميل التحديثات
cd $HOME\Downloads
Invoke-WebRequest -Uri "https://github.com/alabasi2025/alwakeel_php/archive/refs/heads/main.zip" -OutFile "alwakeel_php_update.zip"
Expand-Archive -Path "alwakeel_php_update.zip" -DestinationPath "C:\xampp\htdocs\" -Force
Remove-Item "C:\xampp\htdocs\alwakeel_php" -Recurse -Force
Rename-Item "C:\xampp\htdocs\alwakeel_php-main" "alwakeel_php" -Force

Write-Host "✅ تم التحديث!" -ForegroundColor Green
```

---

## ❓ حل المشاكل الشائعة

### المشكلة: "Database connection failed"
```powershell
# تأكد من تشغيل MySQL
Get-Process -Name "mysqld" -ErrorAction SilentlyContinue

# إعادة إنشاء القاعدة
$mysql = "C:\xampp\mysql\bin\mysql.exe"
& $mysql -u root -e "DROP DATABASE IF EXISTS alwakeel_db; CREATE DATABASE alwakeel_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
Get-Content "C:\xampp\htdocs\alwakeel_php\database.sql" -Raw | & $mysql -u root alwakeel_db
```

### المشكلة: "Port already in use"
```powershell
# اكتشاف البرنامج المستخدم للبورت 80
Get-NetTCPConnection -LocalPort 80 | Select-Object OwningProcess
Get-Process -Id (Get-NetTCPConnection -LocalPort 80).OwningProcess

# غيّر البورت كما في القسم أعلاه
```

### المشكلة: "File not found"
```powershell
# تحقق من وجود الملفات
Test-Path "C:\xampp\htdocs\alwakeel_php\alwakeel.php"
Test-Path "C:\xampp\htdocs\alwakeel_php\api.php"
Test-Path "C:\xampp\htdocs\alwakeel_php\database.sql"

# إذا كانت مفقودة، أعد التحميل من GitHub
```

---

## 📞 الدعم

- **GitHub**: https://github.com/alabasi2025/alwakeel_php
- **Issues**: https://github.com/alabasi2025/alwakeel_php/issues

---

## 📝 ملاحظات

- كلمة مرور MySQL الافتراضية في XAMPP **فارغة**
- اسم المستخدم الافتراضي: **root**
- قاعدة البيانات: **alwakeel_db**
- الترميز: **UTF-8 (utf8mb4)**

---

✅ **تم! الآن يمكنك استخدام النظام بالكامل**
