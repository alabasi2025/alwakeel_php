<?php
/**
 * القائمة الجانبية المشتركة - Shared Sidebar
 * يتم تضمينها في جميع الصفحات للتنقل السهل
 */

// تحديد الصفحة النشطة
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
/* أنماط القائمة الجانبية */
.sidebar {
    position: fixed;
    right: 0;
    top: 0;
    width: 280px;
    height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 30px 20px;
    box-shadow: -5px 0 20px rgba(0,0,0,0.1);
    overflow-y: auto;
    z-index: 1000;
    transition: transform 0.3s ease;
}

.sidebar-header {
    text-align: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 2px solid rgba(255,255,255,0.2);
}

.sidebar-logo {
    font-size: 48px;
    margin-bottom: 10px;
}

.sidebar-title {
    color: white;
    font-size: 22px;
    font-weight: bold;
    margin: 0;
}

.sidebar-subtitle {
    color: rgba(255,255,255,0.8);
    font-size: 13px;
    margin-top: 5px;
}

.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-menu li {
    margin-bottom: 10px;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    color: white;
    text-decoration: none;
    border-radius: 12px;
    transition: all 0.3s ease;
    font-size: 16px;
    background: rgba(255,255,255,0.1);
}

.sidebar-menu a:hover {
    background: rgba(255,255,255,0.2);
    transform: translateX(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.sidebar-menu a.active {
    background: white;
    color: #667eea;
    font-weight: bold;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
}

.sidebar-icon {
    font-size: 24px;
    margin-left: 15px;
    min-width: 30px;
    text-align: center;
}

.sidebar-footer {
    position: absolute;
    bottom: 20px;
    right: 20px;
    left: 20px;
    text-align: center;
    padding-top: 20px;
    border-top: 2px solid rgba(255,255,255,0.2);
}

.sidebar-footer-text {
    color: rgba(255,255,255,0.7);
    font-size: 12px;
}

/* زر إخفاء/إظهار القائمة للشاشات الصغيرة */
.sidebar-toggle {
    position: fixed;
    top: 20px;
    right: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 18px;
    border-radius: 50%;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    z-index: 1001;
    display: none;
}

/* تعديل المحتوى الرئيسي */
.main-content {
    margin-right: 300px;
    padding: 20px;
    transition: margin 0.3s ease;
}

/* للشاشات الصغيرة */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(100%);
    }
    
    .sidebar.active {
        transform: translateX(0);
    }
    
    .sidebar-toggle {
        display: block;
    }
    
    .main-content {
        margin-right: 0;
    }
}

/* شريط التمرير المخصص */
.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
}

.sidebar::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 10px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.5);
}
</style>

<!-- زر إظهار/إخفاء القائمة -->
<button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>

<!-- القائمة الجانبية -->
<div class="sidebar" id="sidebar">
    <!-- رأس القائمة -->
    <div class="sidebar-header">
        <div class="sidebar-logo">🤖</div>
        <h2 class="sidebar-title">الوكيل الذكي</h2>
        <p class="sidebar-subtitle">Agent Interface</p>
    </div>
    
    <!-- قائمة التنقل -->
    <ul class="sidebar-menu">
        <li>
            <a href="alwakeel.php" class="<?php echo ($current_page == 'alwakeel.php') ? 'active' : ''; ?>">
                <span class="sidebar-icon">🏠</span>
                <span>الواجهة الرئيسية</span>
            </a>
        </li>
        <li>
            <a href="chat.php" class="<?php echo ($current_page == 'chat.php') ? 'active' : ''; ?>">
                <span class="sidebar-icon">💬</span>
                <span>الدردشة الذكية</span>
            </a>
        </li>
        <li>
            <a href="integrations.php" class="<?php echo ($current_page == 'integrations.php') ? 'active' : ''; ?>">
                <span class="sidebar-icon">⚙️</span>
                <span>إدارة الربط</span>
            </a>
        </li>
        <li>
            <a href="sync_engine.php" class="<?php echo ($current_page == 'sync_engine.php') ? 'active' : ''; ?>">
                <span class="sidebar-icon">🔄</span>
                <span>محرك المزامنة</span>
            </a>
        </li>
        <li>
            <a href="backup.php" class="<?php echo ($current_page == 'backup.php') ? 'active' : ''; ?>">
                <span class="sidebar-icon">💾</span>
                <span>النسخ الاحتياطي</span>
            </a>
        </li>
        <li>
            <a href="migrate.php" class="<?php echo ($current_page == 'migrate.php') ? 'active' : ''; ?>">
                <span class="sidebar-icon">🔄</span>
                <span>ترحيل القاعدة</span>
            </a>
        </li>
        <li>
            <a href="https://github.com/alabasi2025/alwakeel_php" target="_blank">
                <span class="sidebar-icon">🐙</span>
                <span>GitHub</span>
            </a>
        </li>
    </ul>
    
    <!-- تذييل القائمة -->
    <div class="sidebar-footer">
        <p class="sidebar-footer-text">
            صُنع بـ ❤️ في فلسطين 🇵🇸<br>
            © 2025 Alwakeel Project
        </p>
    </div>
</div>

<script>
// وظيفة إظهار/إخفاء القائمة
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('active');
}

// إغلاق القائمة عند النقر خارجها (للشاشات الصغيرة)
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.querySelector('.sidebar-toggle');
    
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    }
});
</script>
