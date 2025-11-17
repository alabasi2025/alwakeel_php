# 📦 دليل بناء تطبيق Windows (.exe)

## 🎯 المتطلبات

1. **PHP Desktop Chrome**
   - حمّل من: https://github.com/cztomczak/phpdesktop/releases
   - اختر: `phpdesktop-chrome-xxx-php-xxx.zip`

2. **المشروع الحالي** (alwakeel_php)

3. **SQLite** (بدلاً من MySQL للتطبيق المستقل)

---

## 📋 خطوات البناء

### 1️⃣ تحميل PHP Desktop

```powershell
# حمّل آخر إصدار
# https://github.com/cztomczak/phpdesktop/releases/latest

# فك الضغط في مجلد مؤقت
# مثال: C:\phpdesktop-build
```

---

### 2️⃣ نسخ المشروع

```powershell
# انتقل لمجلد PHP Desktop
cd C:\phpdesktop-build

# احذف مجلد www الافتراضي
Remove-Item -Path www -Recurse -Force

# انسخ مشروعك
Copy-Item -Path C:\xampp\htdocs\alwakeel_php -Destination www -Recurse

# انسخ ملف settings.json
Copy-Item -Path www\settings.json -Destination . -Force
```

---

### 3️⃣ تحويل إلى SQLite

```powershell
# ادخل لمجلد المشروع
cd www

# حدّث .env
(Get-Content .env) -replace 'DB_CONNECTION=mysql', 'DB_CONNECTION=sqlite' | Set-Content .env
(Get-Content .env) -replace 'DB_HOST=.*', '#DB_HOST=127.0.0.1' | Set-Content .env
(Get-Content .env) -replace 'DB_PORT=.*', '#DB_PORT=3306' | Set-Content .env
(Get-Content .env) -replace 'DB_DATABASE=.*', '#DB_DATABASE=alwakeel_db' | Set-Content .env
(Get-Content .env) -replace 'DB_USERNAME=.*', '#DB_USERNAME=root' | Set-Content .env
(Get-Content .env) -replace 'DB_PASSWORD=.*', '#DB_PASSWORD=' | Set-Content .env

# أنشئ ملف قاعدة البيانات
New-Item -Path database\database.sqlite -ItemType File -Force

# شغّل migrations
..\php\php.exe artisan migrate --force
```

---

### 4️⃣ تثبيت المكتبات

```powershell
# تأكد من وجود composer
# إذا لم يكن موجود، حمّله من: https://getcomposer.org/download/

# ثبّت المكتبات
composer install --no-dev --optimize-autoloader

# حسّن الأداء
..\php\php.exe artisan config:cache
..\php\php.exe artisan route:cache
..\php\php.exe artisan view:cache
```

---

### 5️⃣ تنظيف الملفات

```powershell
# احذف الملفات غير الضرورية
Remove-Item -Path .git -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -Path node_modules -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -Path tests -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -Path .github -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -Path storage\logs\* -Force -ErrorAction SilentlyContinue

# امسح cache
Remove-Item -Path storage\framework\cache\data\* -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -Path storage\framework\sessions\* -Force -ErrorAction SilentlyContinue
Remove-Item -Path storage\framework\views\* -Force -ErrorAction SilentlyContinue
```

---

### 6️⃣ تخصيص التطبيق

```powershell
# ارجع لمجلد phpdesktop
cd ..

# أعد تسمية الملف التنفيذي
Rename-Item -Path phpdesktop-chrome.exe -NewName "AlWakeel.exe"

# أضف أيقونة مخصصة (اختياري)
# استخدم Resource Hacker: http://www.angusj.com/resourcehacker/
# لتغيير أيقونة AlWakeel.exe
```

---

### 7️⃣ اختبار التطبيق

```powershell
# شغّل التطبيق
.\AlWakeel.exe

# تأكد من:
# ✅ التطبيق يفتح
# ✅ الصفحة الرئيسية تظهر
# ✅ قاعدة البيانات تعمل
# ✅ جميع الميزات تعمل
```

---

### 8️⃣ إنشاء حزمة التوزيع

```powershell
# أنشئ مجلد للتوزيع
New-Item -Path C:\AlWakeel-Distribution -ItemType Directory -Force

# انسخ جميع الملفات
Copy-Item -Path C:\phpdesktop-build\* -Destination C:\AlWakeel-Distribution -Recurse

# أنشئ ملف README
@"
# الوكيل - مساعدك الذكي

## التشغيل
1. شغّل AlWakeel.exe
2. استمتع!

## المتطلبات
- Windows 7 أو أحدث
- لا يحتاج تثبيت

## الدعم
https://github.com/alabasi2025/alwakeel_php
"@ | Out-File -FilePath C:\AlWakeel-Distribution\README.txt -Encoding UTF8

# اضغط الملفات
Compress-Archive -Path C:\AlWakeel-Distribution\* -DestinationPath C:\AlWakeel-v1.0.0.zip
```

---

## 🎯 النتيجة النهائية

**ملف واحد جاهز للتوزيع:**
```
AlWakeel-v1.0.0.zip (~50 MB)
```

**محتويات الحزمة:**
- ✅ `AlWakeel.exe` - التطبيق الرئيسي
- ✅ `www/` - ملفات المشروع
- ✅ `php/` - PHP مدمج
- ✅ `database.sqlite` - قاعدة البيانات
- ✅ `settings.json` - الإعدادات
- ✅ `README.txt` - التعليمات

---

## 🚀 التوزيع

### طريقة 1: ملف ZIP
```
1. ارفع AlWakeel-v1.0.0.zip إلى موقعك
2. المستخدم يحمّل ويفك الضغط
3. يشغّل AlWakeel.exe
```

### طريقة 2: Installer (NSIS)
```powershell
# حمّل NSIS: https://nsis.sourceforge.io/Download
# أنشئ installer script
# ينتج: AlWakeel-Setup.exe
```

---

## 📝 ملاحظات مهمة

### ✅ المميزات:
- لا يحتاج XAMPP
- لا يحتاج تثبيت
- يعمل مباشرة
- حجم صغير (~50 MB)
- قاعدة بيانات مدمجة

### ⚠️ القيود:
- Windows فقط
- SQLite بدلاً من MySQL
- لا يمكن الوصول من أجهزة أخرى

### 🔧 التخصيص:
- غيّر الأيقونة في `settings.json`
- غيّر العنوان في `settings.json`
- غيّر المنفذ في `settings.json`

---

## 🎊 جاهز!

**الآن لديك تطبيق Windows كامل!**

**للتحديثات المستقبلية:**
1. حدّث المشروع
2. أعد البناء
3. وزّع النسخة الجديدة

---

## 📦 سكريبت تلقائي كامل

```powershell
# سكريبت البناء التلقائي الكامل
# احفظه كـ: build-app.ps1

# المتغيرات
$phpDesktopUrl = "https://github.com/cztomczak/phpdesktop/releases/download/chrome-v57.0-rc/phpdesktop-chrome-57.0-final-php-7.1.3.zip"
$projectPath = "C:\xampp\htdocs\alwakeel_php"
$buildPath = "C:\AlWakeel-Build"
$outputPath = "C:\AlWakeel-v1.0.0.zip"

# 1. تحميل PHP Desktop
Write-Host "تحميل PHP Desktop..." -ForegroundColor Green
Invoke-WebRequest -Uri $phpDesktopUrl -OutFile "$env:TEMP\phpdesktop.zip"
Expand-Archive -Path "$env:TEMP\phpdesktop.zip" -DestinationPath $buildPath -Force

# 2. نسخ المشروع
Write-Host "نسخ المشروع..." -ForegroundColor Green
Remove-Item -Path "$buildPath\www" -Recurse -Force -ErrorAction SilentlyContinue
Copy-Item -Path $projectPath -Destination "$buildPath\www" -Recurse

# 3. تحويل إلى SQLite
Write-Host "تحويل إلى SQLite..." -ForegroundColor Green
cd "$buildPath\www"
(Get-Content .env) -replace 'DB_CONNECTION=mysql', 'DB_CONNECTION=sqlite' | Set-Content .env
New-Item -Path database\database.sqlite -ItemType File -Force
& "$buildPath\php\php.exe" artisan migrate --force

# 4. تثبيت المكتبات
Write-Host "تثبيت المكتبات..." -ForegroundColor Green
composer install --no-dev --optimize-autoloader

# 5. تحسين الأداء
Write-Host "تحسين الأداء..." -ForegroundColor Green
& "$buildPath\php\php.exe" artisan config:cache
& "$buildPath\php\php.exe" artisan route:cache
& "$buildPath\php\php.exe" artisan view:cache

# 6. تنظيف
Write-Host "تنظيف الملفات..." -ForegroundColor Green
Remove-Item -Path .git -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -Path node_modules -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -Path tests -Recurse -Force -ErrorAction SilentlyContinue

# 7. تخصيص
Write-Host "تخصيص التطبيق..." -ForegroundColor Green
cd $buildPath
Copy-Item -Path www\settings.json -Destination . -Force
Rename-Item -Path phpdesktop-chrome.exe -NewName "AlWakeel.exe"

# 8. ضغط
Write-Host "إنشاء حزمة التوزيع..." -ForegroundColor Green
Compress-Archive -Path "$buildPath\*" -DestinationPath $outputPath -Force

Write-Host "`n✅ تم! الملف جاهز في: $outputPath" -ForegroundColor Green
Write-Host "الحجم: $((Get-Item $outputPath).Length / 1MB) MB" -ForegroundColor Cyan
```

---

**احفظ السكريبت وشغّله:**
```powershell
.\build-app.ps1
```

**ينتج:**
```
AlWakeel-v1.0.0.zip
```

**جاهز للتوزيع! 🎊**
