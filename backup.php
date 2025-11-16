<?php header('Content-Type: text/html; charset=utf-8'); ?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>النسخ الاحتياطي - واجهة الوكيل</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; direction: rtl; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        h1 { color: #667eea; text-align: center; }
        .section { background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0; border: 2px dashed #667eea; }
        button { background: #667eea; color: white; padding: 12px 30px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: bold; margin: 10px 5px; }
        button:hover { opacity: 0.9; }
        .success { background: #28a745; }
        .status { padding: 15px; border-radius: 8px; margin: 15px 0; }
        .status-success { background: #d4edda; color: #155724; }
        .status-error { background: #f8d7da; color: #721c24; }
        .status-info { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <div class="container">
        <h1>💾 النسخ الاحتياطي والاستعادة</h1>
        
        <div class="section">
            <h2>📤 تصدير البيانات</h2>
            <p>تصدير جميع الأوامر والنتائج إلى ملف JSON</p>
            <button onclick="exportBackup()">📥 تصدير النسخة الاحتياطية</button>
        </div>

        <div class="section">
            <h2>📥 استيراد البيانات</h2>
            <p>استيراد بيانات من ملف JSON احتياطي</p>
            <input type="file" id="backupFile" accept=".json" style="margin: 10px 0;">
            <br>
            <button class="success" onclick="importBackup()">📤 استيراد النسخة الاحتياطية</button>
        </div>

        <div id="status"></div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="alwakeel.php" style="background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; display: inline-block;">
                🏠 العودة للواجهة الرئيسية
            </a>
        </div>
    </div>

    <script>
        function showStatus(msg, type) {
            const statusDiv = document.getElementById('status');
            statusDiv.innerHTML = \`<div class="status status-\${type}">\${msg}</div>\`;
        }

        async function exportBackup() {
            try {
                showStatus('جاري تصدير البيانات...', 'info');
                
                const commandsRes = await fetch('api.php?action=get_commands');
                const commandsData = await commandsRes.json();
                
                const resultsRes = await fetch('api.php?action=get_results');
                const resultsData = await resultsRes.json();
                
                const backup = {
                    export_date: new Date().toISOString(),
                    version: '1.0',
                    commands: commandsData.data || [],
                    results: resultsData.data || []
                };
                
                const blob = new Blob([JSON.stringify(backup, null, 2)], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = \`alwakeel_backup_\${new Date().toISOString().split('T')[0]}.json\`;
                a.click();
                URL.revokeObjectURL(url);
                
                showStatus('✅ تم تصدير النسخة الاحتياطية بنجاح!', 'success');
            } catch (error) {
                showStatus('❌ خطأ في التصدير: ' + error.message, 'error');
            }
        }

        async function importBackup() {
            const fileInput = document.getElementById('backupFile');
            const file = fileInput.files[0];
            
            if (!file) {
                showStatus('⚠️ الرجاء اختيار ملف أولاً', 'error');
                return;
            }
            
            try {
                showStatus('جاري استيراد البيانات...', 'info');
                
                const text = await file.text();
                const backup = JSON.parse(text);
                
                if (!backup.commands || !backup.results) {
                    throw new Error('ملف النسخة الاحتياطية غير صالح');
                }
                
                let importedCommands = 0;
                let importedResults = 0;
                
                for (const cmd of backup.commands) {
                    const res = await fetch('api.php?action=add_command', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ command_text: cmd.command_text })
                    });
                    if (res.ok) importedCommands++;
                }
                
                for (const result of backup.results) {
                    const res = await fetch('api.php?action=add_result', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            command_id: result.command_id,
                            result_text: result.result_text
                        })
                    });
                    if (res.ok) importedResults++;
                }
                
                showStatus(\`✅ تم الاستيراد بنجاح!<br>الأوامر: \${importedCommands}<br>النتائج: \${importedResults}\`, 'success');
                
            } catch (error) {
                showStatus('❌ خطأ في الاستيراد: ' + error.message, 'error');
            }
        }
    </script>
</body>
</html>
