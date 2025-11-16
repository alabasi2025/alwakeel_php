<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>واجهة الوكيل - Agent Interface</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .header p {
            font-size: 1.2em;
            opacity: 0.9;
        }
        
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .tab-button {
            flex: 1;
            min-width: 150px;
            padding: 15px 25px;
            background: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .tab-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .tab-button.active {
            background: #4CAF50;
            color: white;
        }
        
        .tab-content {
            display: none;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
            font-family: 'Courier New', monospace;
        }
        
        .btn {
            padding: 12px 30px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .btn-success {
            background: #4CAF50;
        }
        
        .btn-success:hover {
            background: #45a049;
        }
        
        .btn-refresh {
            background: #2196F3;
            margin-right: 10px;
        }
        
        .btn-refresh:hover {
            background: #0b7dda;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #ddd;
        }
        
        .data-table th {
            background: #667eea;
            color: white;
            font-weight: bold;
        }
        
        .data-table tr:hover {
            background: #f5f5f5;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }
        
        .status-pending {
            background: #FFC107;
            color: #000;
        }
        
        .status-processing {
            background: #2196F3;
            color: white;
        }
        
        .status-completed {
            background: #4CAF50;
            color: white;
        }
        
        .status-failed {
            background: #F44336;
            color: white;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            display: none;
        }
        
        .alert.show {
            display: block;
            animation: slideDown 0.3s;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .sql-result {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            max-height: 400px;
            overflow: auto;
        }
        
        .sql-result pre {
            margin: 0;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .empty-state svg {
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🤖 واجهة الوكيل</h1>
            <p>نظام إدارة الأوامر والنتائج مع شل SQL مدمج</p>
        </div>
        
        <div class="tabs">
            <button class="tab-button active" onclick="showTab('commands')">📋 الأوامر</button>
            <button class="tab-button" onclick="showTab('results')">📊 النتائج</button>
            <button class="tab-button" onclick="showTab('sql')">💻 شل SQL</button>
        </div>
        
        <!-- تبويب الأوامر -->
        <div id="commands-tab" class="tab-content active">
            <h2>إدارة الأوامر</h2>
            
            <div id="commands-alert" class="alert"></div>
            
            <div class="form-group">
                <label>إضافة أمر جديد:</label>
                <textarea id="command-text" placeholder="اكتب الأمر هنا..."></textarea>
            </div>
            
            <button class="btn btn-success" onclick="addCommand()">➕ إرسال الأمر</button>
            <button class="btn btn-refresh" onclick="loadCommands()">🔄 تحديث</button>
            
            <div id="commands-list"></div>
        </div>
        
        <!-- تبويب النتائج -->
        <div id="results-tab" class="tab-content">
            <h2>إدارة النتائج</h2>
            
            <div id="results-alert" class="alert"></div>
            
            <div class="form-group">
                <label>معرف الأمر:</label>
                <input type="number" id="result-command-id" placeholder="أدخل معرف الأمر">
            </div>
            
            <div class="form-group">
                <label>نص النتيجة:</label>
                <textarea id="result-text" placeholder="اكتب النتيجة هنا..."></textarea>
            </div>
            
            <button class="btn btn-success" onclick="addResult()">➕ إضافة نتيجة</button>
            <button class="btn btn-refresh" onclick="loadResults()">🔄 تحديث</button>
            
            <div id="results-list"></div>
        </div>
        
        <!-- تبويب SQL -->
        <div id="sql-tab" class="tab-content">
            <h2>شل SQL</h2>
            
            <div id="sql-alert" class="alert"></div>
            
            <div class="form-group">
                <label>استعلام SQL:</label>
                <textarea id="sql-query" placeholder="SELECT * FROM commands LIMIT 10"></textarea>
            </div>
            
            <button class="btn btn-success" onclick="executeSql()">▶️ تنفيذ</button>
            
            <div id="sql-result" class="sql-result" style="display: none;"></div>
        </div>
    </div>
    
    <script>
        // عرض التبويب
        function showTab(tabName) {
            // إخفاء جميع التبويبات
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // عرض التبويب المحدد
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
            
            // تحميل البيانات
            if (tabName === 'commands') loadCommands();
            if (tabName === 'results') loadResults();
        }
        
        // عرض رسالة
        function showAlert(tabName, message, type) {
            const alert = document.getElementById(tabName + '-alert');
            alert.className = 'alert alert-' + type + ' show';
            alert.textContent = message;
            
            setTimeout(() => {
                alert.classList.remove('show');
            }, 5000);
        }
        
        // تحميل الأوامر
        async function loadCommands() {
            try {
                const response = await fetch('api.php?action=get_commands');
                const data = await response.json();
                
                if (data.success) {
                    const list = document.getElementById('commands-list');
                    
                    if (data.data.length === 0) {
                        list.innerHTML = '<div class="empty-state"><p>لا توجد أوامر مسجلة بعد</p></div>';
                        return;
                    }
                    
                    let html = '<table class="data-table"><thead><tr><th>المعرف</th><th>الأمر</th><th>الحالة</th><th>تاريخ الإنشاء</th></tr></thead><tbody>';
                    
                    data.data.forEach(cmd => {
                        html += `<tr>
                            <td>${cmd.id}</td>
                            <td>${cmd.command_text}</td>
                            <td><span class="status-badge status-${cmd.status}">${getStatusText(cmd.status)}</span></td>
                            <td>${new Date(cmd.created_at).toLocaleString('ar-SA')}</td>
                        </tr>`;
                    });
                    
                    html += '</tbody></table>';
                    list.innerHTML = html;
                }
            } catch (error) {
                showAlert('commands', 'خطأ في تحميل الأوامر: ' + error.message, 'error');
            }
        }
        
        // إضافة أمر
        async function addCommand() {
            const text = document.getElementById('command-text').value.trim();
            
            if (!text) {
                showAlert('commands', 'الرجاء إدخال نص الأمر', 'error');
                return;
            }
            
            try {
                const response = await fetch('api.php?action=add_command', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ command_text: text })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('commands', 'تم إضافة الأمر بنجاح', 'success');
                    document.getElementById('command-text').value = '';
                    loadCommands();
                } else {
                    showAlert('commands', data.error, 'error');
                }
            } catch (error) {
                showAlert('commands', 'خطأ: ' + error.message, 'error');
            }
        }
        
        // تحميل النتائج
        async function loadResults() {
            try {
                const response = await fetch('api.php?action=get_results');
                const data = await response.json();
                
                if (data.success) {
                    const list = document.getElementById('results-list');
                    
                    if (data.data.length === 0) {
                        list.innerHTML = '<div class="empty-state"><p>لا توجد نتائج مسجلة بعد</p></div>';
                        return;
                    }
                    
                    let html = '<table class="data-table"><thead><tr><th>المعرف</th><th>معرف الأمر</th><th>النتيجة</th><th>تاريخ الإنشاء</th></tr></thead><tbody>';
                    
                    data.data.forEach(result => {
                        html += `<tr>
                            <td>${result.id}</td>
                            <td>${result.command_id}</td>
                            <td>${result.result_text}</td>
                            <td>${new Date(result.created_at).toLocaleString('ar-SA')}</td>
                        </tr>`;
                    });
                    
                    html += '</tbody></table>';
                    list.innerHTML = html;
                }
            } catch (error) {
                showAlert('results', 'خطأ في تحميل النتائج: ' + error.message, 'error');
            }
        }
        
        // إضافة نتيجة
        async function addResult() {
            const commandId = document.getElementById('result-command-id').value;
            const text = document.getElementById('result-text').value.trim();
            
            if (!commandId || !text) {
                showAlert('results', 'الرجاء إدخال معرف الأمر ونص النتيجة', 'error');
                return;
            }
            
            try {
                const response = await fetch('api.php?action=add_result', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ command_id: parseInt(commandId), result_text: text })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('results', 'تم إضافة النتيجة بنجاح', 'success');
                    document.getElementById('result-command-id').value = '';
                    document.getElementById('result-text').value = '';
                    loadResults();
                } else {
                    showAlert('results', data.error, 'error');
                }
            } catch (error) {
                showAlert('results', 'خطأ: ' + error.message, 'error');
            }
        }
        
        // تنفيذ SQL
        async function executeSql() {
            const query = document.getElementById('sql-query').value.trim();
            
            if (!query) {
                showAlert('sql', 'الرجاء إدخال استعلام SQL', 'error');
                return;
            }
            
            try {
                const response = await fetch('api.php?action=execute_sql', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ query: query })
                });
                
                const data = await response.json();
                
                const resultDiv = document.getElementById('sql-result');
                resultDiv.style.display = 'block';
                
                if (data.success) {
                    showAlert('sql', 'تم تنفيذ الاستعلام بنجاح', 'success');
                    resultDiv.innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                } else {
                    showAlert('sql', data.error, 'error');
                    resultDiv.innerHTML = '<pre style="color: red;">' + data.error + '</pre>';
                }
            } catch (error) {
                showAlert('sql', 'خطأ: ' + error.message, 'error');
            }
        }
        
        // ترجمة الحالات
        function getStatusText(status) {
            const statuses = {
                'pending': 'قيد الانتظار',
                'processing': 'قيد المعالجة',
                'completed': 'مكتمل',
                'failed': 'فشل'
            };
            return statuses[status] || status;
        }
        
        // تحميل الأوامر عند فتح الصفحة
        loadCommands();
    </script>
</body>
</html>
