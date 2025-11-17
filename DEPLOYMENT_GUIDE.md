# دليل النشر التلقائي - الوكيل الذكي

## نظام النشر التلقائي عبر GitHub Actions

تم إعداد نظام نشر تلقائي احترافي يقوم بنشر التحديثات إلى خادم Hostinger تلقائياً عند كل Push إلى GitHub.

---

## المتطلبات

1. **مفتاح SSH** - تم إنشاؤه مسبقاً وموجود في `.ssh/alwakeel_hostinger_new`
2. **حساب GitHub** - مع صلاحيات الكتابة على المستودع
3. **خادم Hostinger** - مع دعم SSH

---

## خطوات الإعداد

### 1. إضافة Secrets إلى GitHub

انتقل إلى مستودع GitHub:
```
https://github.com/alabasi2025/alwakeel_php/settings/secrets/actions
```

أضف الـ Secrets التالية:

#### SSH_PRIVATE_KEY
محتوى ملف المفتاح الخاص:
```bash
cat .ssh/alwakeel_hostinger_new
```
انسخ المحتوى الكامل (من `-----BEGIN` إلى `-----END`)

#### REMOTE_HOST
```
82.29.157.218
```

#### REMOTE_USER
```
u306850950
```

#### REMOTE_PORT
```
65002
```

#### REMOTE_TARGET
```
/home/u306850950/domains/mediumturquoise-porcupine-839487.hostingersite.com/laravel_app
```

---

### 2. التأكد من صلاحيات المفتاح العام

تأكد من أن المفتاح العام مضاف في لوحة تحكم Hostinger:

1. افتح لوحة تحكم Hostinger
2. انتقل إلى **Advanced** → **SSH Access**
3. أضف المفتاح العام من ملف `.ssh/alwakeel_hostinger_new.pub`

---

### 3. اختبار النشر التلقائي

بعد إضافة جميع الـ Secrets:

1. قم بعمل أي تعديل بسيط في المشروع
2. قم بـ Commit و Push:
```bash
git add .
git commit -m "اختبار النشر التلقائي"
git push origin main
```

3. انتقل إلى تبويب **Actions** في GitHub:
```
https://github.com/alabasi2025/alwakeel_php/actions
```

4. راقب عملية النشر - يجب أن تكتمل بنجاح خلال 2-3 دقائق

---

## كيف يعمل النظام؟

### المرحلة 1: التحضير
- استنساخ الكود من GitHub
- تثبيت PHP 8.1
- تثبيت Composer dependencies

### المرحلة 2: النشر
- نقل الملفات إلى الخادم عبر SSH
- استثناء الملفات غير الضرورية (node_modules, .git, storage)

### المرحلة 3: ما بعد النشر
- تحديث الـ cache (config, routes, views)
- تشغيل migrations
- ضبط صلاحيات المجلدات

---

## استكشاف الأخطاء

### خطأ: Permission denied
**الحل:** تأكد من أن المفتاح العام مضاف في Hostinger

### خطأ: Connection refused
**الحل:** تأكد من صحة REMOTE_HOST و REMOTE_PORT

### خطأ: Directory not found
**الحل:** تأكد من صحة REMOTE_TARGET

---

## النشر اليدوي

يمكنك تشغيل النشر يدوياً من GitHub:

1. انتقل إلى **Actions** → **Deploy to Hostinger**
2. اضغط على **Run workflow**
3. اختر branch **main**
4. اضغط **Run workflow**

---

## الأمان

- ✅ المفتاح الخاص محفوظ بشكل آمن في GitHub Secrets
- ✅ لا يتم تخزين كلمات المرور في الكود
- ✅ الاتصال مشفر عبر SSH
- ✅ الملفات الحساسة مستثناة من النشر

---

## الدعم

في حالة وجود أي مشاكل:
1. راجع سجلات GitHub Actions
2. تأكد من صحة جميع الـ Secrets
3. تحقق من صلاحيات SSH على الخادم

---

**تم الإعداد بواسطة:** Manus AI  
**التاريخ:** 17 نوفمبر 2025
