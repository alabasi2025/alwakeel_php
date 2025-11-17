# ========================================
# سكريبت بناء تطبيق الوكيل Windows
# ========================================

param(
    [string]$ProjectPath = "C:\xampp\htdocs\alwakeel_php",
    [string]$BuildPath = "C:\AlWakeel-Build",
    [string]$OutputName = "AlWakeel-v1.0.0"
)

# الألوان
$Green = "Green"
$Yellow = "Yellow"
$Red = "Red"
$Cyan = "Cyan"

Write-Host "`n========================================" -ForegroundColor $Cyan
Write-Host "   بناء تطبيق الوكيل Windows" -ForegroundColor $Cyan
Write-Host "========================================`n" -ForegroundColor $Cyan

# التحقق من المتطلبات
Write-Host "[1/9] التحقق من المتطلبات..." -ForegroundColor $Yellow

if (!(Test-Path $ProjectPath)) {
    Write-Host "❌ المشروع غير موجود في: $ProjectPath" -ForegroundColor $Red
    exit 1
}

if (!(Get-Command composer -ErrorAction SilentlyContinue)) {
    Write-Host "❌ Composer غير مثبت" -ForegroundColor $Red
    exit 1
}

Write-Host "✅ جميع المتطلبات متوفرة`n" -ForegroundColor $Green

# تحميل PHP Desktop
Write-Host "[2/9] تحميل PHP Desktop..." -ForegroundColor $Yellow
$phpDesktopUrl = "https://github.com/cztomczak/phpdesktop/releases/download/chrome-v57.0-rc/phpdesktop-chrome-57.0-final-php-7.1.3.zip"
$tempZip = "$env:TEMP\phpdesktop.zip"

try {
    Invoke-WebRequest -Uri $phpDesktopUrl -OutFile $tempZip -UseBasicParsing
    Write-Host "✅ تم التحميل`n" -ForegroundColor $Green
} catch {
    Write-Host "❌ فشل التحميل: $_" -ForegroundColor $Red
    exit 1
}

# فك الضغط
Write-Host "[3/9] فك ضغط PHP Desktop..." -ForegroundColor $Yellow
if (Test-Path $BuildPath) {
    Remove-Item -Path $BuildPath -Recurse -Force
}
Expand-Archive -Path $tempZip -DestinationPath $BuildPath -Force
Write-Host "✅ تم فك الضغط`n" -ForegroundColor $Green

# نسخ المشروع
Write-Host "[4/9] نسخ المشروع..." -ForegroundColor $Yellow
Remove-Item -Path "$BuildPath\www" -Recurse -Force -ErrorAction SilentlyContinue
Copy-Item -Path $ProjectPath -Destination "$BuildPath\www" -Recurse
Write-Host "✅ تم النسخ`n" -ForegroundColor $Green

# تحويل إلى SQLite
Write-Host "[5/9] تحويل قاعدة البيانات إلى SQLite..." -ForegroundColor $Yellow
Push-Location "$BuildPath\www"

(Get-Content .env) -replace 'DB_CONNECTION=mysql', 'DB_CONNECTION=sqlite' | Set-Content .env
(Get-Content .env) -replace 'DB_HOST=.*', '#DB_HOST=127.0.0.1' | Set-Content .env
(Get-Content .env) -replace 'DB_PORT=.*', '#DB_PORT=3306' | Set-Content .env
(Get-Content .env) -replace 'DB_DATABASE=.*', '#DB_DATABASE=alwakeel_db' | Set-Content .env
(Get-Content .env) -replace 'DB_USERNAME=.*', '#DB_USERNAME=root' | Set-Content .env
(Get-Content .env) -replace 'DB_PASSWORD=.*', '#DB_PASSWORD=' | Set-Content .env

New-Item -Path database\database.sqlite -ItemType File -Force | Out-Null
& "$BuildPath\php\php.exe" artisan migrate --force --no-interaction

Write-Host "✅ تم التحويل`n" -ForegroundColor $Green

# تثبيت المكتبات
Write-Host "[6/9] تثبيت المكتبات..." -ForegroundColor $Yellow
composer install --no-dev --optimize-autoloader --no-interaction
Write-Host "✅ تم التثبيت`n" -ForegroundColor $Green

# تحسين الأداء
Write-Host "[7/9] تحسين الأداء..." -ForegroundColor $Yellow
& "$BuildPath\php\php.exe" artisan config:cache
& "$BuildPath\php\php.exe" artisan route:cache
& "$BuildPath\php\php.exe" artisan view:cache
Write-Host "✅ تم التحسين`n" -ForegroundColor $Green

# تنظيف الملفات
Write-Host "[8/9] تنظيف الملفات غير الضرورية..." -ForegroundColor $Yellow
Remove-Item -Path .git -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -Path node_modules -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -Path tests -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -Path .github -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -Path storage\logs\* -Force -ErrorAction SilentlyContinue
Remove-Item -Path storage\framework\cache\data\* -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -Path storage\framework\sessions\* -Force -ErrorAction SilentlyContinue
Remove-Item -Path storage\framework\views\* -Force -ErrorAction SilentlyContinue
Write-Host "✅ تم التنظيف`n" -ForegroundColor $Green

Pop-Location

# تخصيص التطبيق
Write-Host "[9/9] تخصيص التطبيق..." -ForegroundColor $Yellow
Copy-Item -Path "$BuildPath\www\settings.json" -Destination $BuildPath -Force
Rename-Item -Path "$BuildPath\phpdesktop-chrome.exe" -NewName "AlWakeel.exe" -Force

# إنشاء README
@"
# الوكيل - مساعدك الذكي

## 🚀 التشغيل
1. شغّل AlWakeel.exe
2. استمتع بجميع الميزات!

## ✨ الميزات
- دردشة ذكية مع Gemini (مجاني)
- إدارة Laravel الشاملة
- التحكم بـ XAMPP
- نظام النسخ الاحتياطي
- طرفية تفاعلية

## 💻 المتطلبات
- Windows 7 أو أحدث
- لا يحتاج تثبيت أو XAMPP

## 📞 الدعم
GitHub: https://github.com/alabasi2025/alwakeel_php

## 📝 الإصدار
v1.0.0
"@ | Out-File -FilePath "$BuildPath\README.txt" -Encoding UTF8

Write-Host "✅ تم التخصيص`n" -ForegroundColor $Green

# ضغط الملفات
Write-Host "إنشاء حزمة التوزيع..." -ForegroundColor $Yellow
$outputZip = "C:\$OutputName.zip"
if (Test-Path $outputZip) {
    Remove-Item $outputZip -Force
}
Compress-Archive -Path "$BuildPath\*" -DestinationPath $outputZip -Force

$fileSize = [math]::Round((Get-Item $outputZip).Length / 1MB, 2)

Write-Host "`n========================================" -ForegroundColor $Cyan
Write-Host "   ✅ تم البناء بنجاح!" -ForegroundColor $Green
Write-Host "========================================" -ForegroundColor $Cyan
Write-Host "`nالملف: $outputZip" -ForegroundColor $Cyan
Write-Host "الحجم: $fileSize MB" -ForegroundColor $Cyan
Write-Host "`nجاهز للتوزيع! 🎊`n" -ForegroundColor $Green
