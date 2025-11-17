# 🎯 دليل بناء تطبيق الوكيل Windows

## 📋 المحتويات

1. [المتطلبات](#المتطلبات)
2. [طريقة البناء التلقائية](#طريقة-البناء-التلقائية)
3. [طريقة البناء اليدوية](#طريقة-البناء-اليدوية)
4. [حل المشاكل](#حل-المشاكل)
5. [الاستخدام والتوزيع](#الاستخدام-والتوزيع)

---

## 📦 المتطلبات

### على جهاز التطوير:

- ✅ **Windows 7 أو أحدث**
- ✅ **XAMPP** مثبت في `C:\xampp`
- ✅ **Composer** مثبت ومضاف إلى PATH
- ✅ **PowerShell 5.0** أو أحدث
- ✅ **اتصال بالإنترنت** (لتحميل PHP Desktop)

### على جهاز المستخدم النهائي:

- ✅ **Windows 7 أو أحدث** فقط!
- ❌ **لا يحتاج** XAMPP
- ❌ **لا يحتاج** PHP
- ❌ **لا يحتاج** Composer

---

## 🚀 طريقة البناء التلقائية

### الخطوة 1: التحضير

تأكد أن المشروع موجود في:
```
C:\xampp\htdocs\alwakeel_php
```

### الخطوة 2: فتح PowerShell

1. اضغط `Win + X`
2. اختر **Windows PowerShell**

### الخطوة 3: تنفيذ السكريبت

```powershell
cd C:\xampp\htdocs\alwakeel_php
.\build-app.ps1
```

### الخطوة 4: الانتظار

السكريبت سيقوم بـ:

1. ✅ **[1/9]** التحقق من المتطلبات
2. ✅ **[2/9]** تحميل PHP Desktop (~50 MB)
3. ✅ **[3/9]** فك ضغط PHP Desktop
4. ✅ **[4/9]** نسخ المشروع
5. ✅ **[5/9]** تحويل قاعدة البيانات إلى SQLite
6. ✅ **[6/9]** تثبيت المكتبات
7. ✅ **[7/9]** تحسين الأداء (Cache)
8. ✅ **[8/9]** تنظيف الملفات غير الضرورية
9. ✅ **[9/9]** تخصيص التطبيق وإنشاء ZIP

**المدة المتوقعة:** 5-10 دقائق

### الخطوة 5: النتيجة

ستجد الملف في:
```
C:\AlWakeel-v1.0.0.zip (~50 MB)
```

---

## 🔧 طريقة البناء اليدوية

إذا فشل السكريبت التلقائي، اتبع هذه الخطوات:

### 1. تحميل PHP Desktop

```powershell
# افتح المتصفح
Start-Process "https://github.com/cztomczak/phpdesktop/releases/latest"
```

حمّل: `phpdesktop-chrome-xxx-php-xxx.zip`

### 2. فك الضغط

```powershell
Expand-Archive -Path "$HOME\Downloads\phpdesktop-chrome-*.zip" -DestinationPath "C:\AlWakeel-Build"
```

### 3. نسخ المشروع

```powershell
Remove-Item "C:\AlWakeel-Build\www" -Recurse -Force
Copy-Item "C:\xampp\htdocs\alwakeel_php" "C:\AlWakeel-Build\www" -Recurse
```

### 4. تحويل إلى SQLite

```powershell
cd C:\AlWakeel-Build\www

# تعديل .env
(Get-Content .env) -replace 'DB_CONNECTION=mysql', 'DB_CONNECTION=sqlite' | Set-Content .env
(Get-Content .env) -replace 'DB_HOST=.*', '#DB_HOST=127.0.0.1' | Set-Content .env
(Get-Content .env) -replace 'DB_PORT=.*', '#DB_PORT=3306' | Set-Content .env
(Get-Content .env) -replace 'DB_DATABASE=.*', '#DB_DATABASE=alwakeel_db' | Set-Content .env
(Get-Content .env) -replace 'DB_USERNAME=.*', '#DB_USERNAME=root' | Set-Content .env
(Get-Content .env) -replace 'DB_PASSWORD=.*', '#DB_PASSWORD=' | Set-Content .env

# إنشاء قاعدة البيانات
New-Item -Path database\database.sqlite -ItemType File -Force

# تشغيل Migrations
..\php\php.exe artisan migrate --force
```

### 5. تثبيت المكتبات

```powershell
composer install --no-dev --optimize-autoloader
```

### 6. تحسين الأداء

```powershell
..\php\php.exe artisan config:cache
..\php\php.exe artisan route:cache
..\php\php.exe artisan view:cache
```

### 7. تنظيف الملفات

```powershell
Remove-Item .git -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item node_modules -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item tests -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item storage\logs\* -Force -ErrorAction SilentlyContinue
```

### 8. تخصيص التطبيق

```powershell
cd C:\AlWakeel-Build
Rename-Item phpdesktop-chrome.exe AlWakeel.exe
```

### 9. إنشاء ZIP

```powershell
Compress-Archive -Path "C:\AlWakeel-Build\*" -DestinationPath "C:\AlWakeel-v1.0.0.zip" -Force
```

---

## ⚠️ حل المشاكل

### مشكلة: Execution Policy

**الخطأ:**
```
.\build-app.ps1 : File cannot be loaded because running scripts is disabled
```

**الحل:**
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

### مشكلة: Composer غير موجود

**الخطأ:**
```
❌ Composer غير مثبت
```

**الحل:**
1. حمّل Composer من: https://getcomposer.org/download/
2. ثبّته
3. أعد فتح PowerShell

### مشكلة: فشل تحميل PHP Desktop

**الخطأ:**
```
❌ فشل التحميل
```

**الحل:**
- تحقق من اتصال الإنترنت
- حمّل يدوياً من: https://github.com/cztomczak/phpdesktop/releases
- استخدم الطريقة اليدوية

### مشكلة: المشروع غير موجود

**الخطأ:**
```
❌ المشروع غير موجود في: C:\xampp\htdocs\alwakeel_php
```

**الحل:**
```powershell
# عدّل المسار في السكريبت
.\build-app.ps1 -ProjectPath "C:\المسار\الصحيح"
```

### مشكلة: فشل Migrations

**الأعراض:**
- قاعدة البيانات فارغة
- أخطاء عند فتح التطبيق

**الحل:**
```powershell
cd C:\AlWakeel-Build\www
..\php\php.exe artisan migrate:fresh --force
```

---

## 📦 الاستخدام والتوزيع

### محتويات الحزمة

```
AlWakeel-v1.0.0.zip
├── AlWakeel.exe          # التطبيق الرئيسي
├── php/                  # PHP مدمج
├── www/                  # مشروع Laravel
│   ├── database/
│   │   └── database.sqlite  # قاعدة البيانات
│   ├── app/
│   ├── public/
│   └── ...
├── settings.json         # إعدادات PHP Desktop
└── README.txt           # دليل المستخدم
```

### التوزيع

1. **رفع إلى Google Drive / Dropbox**
   ```
   الحجم: ~50 MB
   ```

2. **إنشاء رابط تحميل**
   ```
   مثال: https://drive.google.com/file/d/xxx/view
   ```

3. **مشاركة مع المستخدمين**

### تعليمات للمستخدمين

```markdown
# كيفية الاستخدام

1. حمّل الملف: AlWakeel-v1.0.0.zip
2. فك الضغط إلى أي مجلد
3. شغّل AlWakeel.exe
4. استمتع!

## المتطلبات
- Windows 7 أو أحدث فقط
- لا يحتاج أي تثبيت إضافي
```

---

## 🎯 الميزات

التطبيق الناتج يحتوي على:

- ✅ **دردشة ذكية** مع Google Gemini (مجاني 100%)
- ✅ **Laravel Manager** - إدارة شاملة (Artisan, Migrations, Routes, Cache)
- ✅ **XAMPP Manager** - التحكم بخدمات XAMPP
- ✅ **Backup System** - نسخ احتياطي للنظام وقاعدة البيانات
- ✅ **Terminal** - طرفية تفاعلية (PowerShell, CMD, Bash)
- ✅ **واجهة عربية كاملة** مع دعم RTL
- ✅ **قاعدة بيانات SQLite** محمولة
- ✅ **لا يحتاج تثبيت** - تشغيل مباشر

---

## 📊 المواصفات التقنية

### البيئة المدمجة

- **PHP:** 7.1.3 (مدمج)
- **Web Server:** Chrome Embedded Framework
- **Database:** SQLite 3
- **Framework:** Laravel 10.x

### الحجم

- **ZIP:** ~50 MB
- **بعد الفك:** ~120 MB

### التوافق

- ✅ Windows 7
- ✅ Windows 8/8.1
- ✅ Windows 10
- ✅ Windows 11
- ❌ macOS (غير مدعوم)
- ❌ Linux (غير مدعوم)

---

## 📞 الدعم

- **GitHub:** https://github.com/alabasi2025/alwakeel_php
- **الموقع المباشر:** mediumturquoise-porcupine-839487.hostingersite.com
- **مفتاح Gemini:** AIzaSyCcjwjKjljAU66S2sxWwrehjmzGnC1lOYg

---

## 📝 ملاحظات مهمة

### الأمان

- ⚠️ **لا تشارك مفتاح Gemini API** في الكود العام
- ⚠️ **غيّر APP_KEY** في `.env` قبل التوزيع
- ⚠️ **احذف ملفات `.git`** من الحزمة النهائية

### الأداء

- ✅ استخدم `composer install --no-dev` لتقليل الحجم
- ✅ نفّذ `artisan cache` لتحسين السرعة
- ✅ احذف `node_modules` إذا لم تكن مطلوبة

### التحديثات

لتحديث التطبيق:

1. عدّل الكود في `C:\xampp\htdocs\alwakeel_php`
2. شغّل `.\build-app.ps1` مرة أخرى
3. غيّر رقم الإصدار في `$OutputName`

---

## 🎉 خلاصة

هذا الدليل يغطي جميع جوانب بناء تطبيق Windows من مشروع Laravel. السكريبت التلقائي يجعل العملية سهلة وسريعة، بينما الطريقة اليدوية توفر تحكم كامل عند الحاجة.

**حظاً موفقاً في التوزيع! 🚀**
