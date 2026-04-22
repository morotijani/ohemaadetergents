<?php
$apiBase = '/ohemaadetergents/api';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Account Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    
    <script>
        // Apply theme early to prevent flash
        const savedTheme = localStorage.getItem('admin_theme');
        if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
        }
    </script>

    <style>
        :root {
            --bg-color: #ffffff;
            --text-color: #202124;
            --card-bg: #ffffff;
            --card-border: #dadce0;
            --hover-bg: #f1f3f4;
            --active-bg: #e8f0fe;
            --active-text: #1a73e8;
            --secondary-text: #5f6368;
            --search-bg: #f1f3f4;
            --search-text: #202124;
            --btn-primary-bg: #1a73e8;
            --btn-primary-text: #ffffff;
            --btn-primary-hover: #1b66c9;
            --modal-bg: #ffffff;
            --input-border: #dadce0;
        }

        [data-theme="dark"] {
            --bg-color: #202124;
            --text-color: #e8eaed;
            --card-bg: #303134;
            --card-border: #5f6368;
            --hover-bg: rgba(255,255,255,0.04);
            --active-bg: rgba(138, 180, 248, 0.12);
            --active-text: #8ab4f8;
            --secondary-text: #9aa0a6;
            --search-bg: rgba(255,255,255,0.08);
            --search-text: #e8eaed;
            --btn-primary-bg: #8ab4f8;
            --btn-primary-text: #202124;
            --btn-primary-hover: #9bbcf9;
            --modal-bg: #282a2d;
            --input-border: #5f6368;
        }

        body { 
            font-family: 'Roboto', sans-serif; 
            background-color: var(--bg-color); 
            color: var(--text-color); 
            transition: background-color 0.2s, color 0.2s;
            margin: 0;
            padding: 0;
        }
        h1, h2, h3, h4, h5, h6 { 
            font-family: 'Google Sans', sans-serif; 
            color: var(--text-color);
        }
        
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }

        /* Layout */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            height: 64px;
            background-color: var(--bg-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            transition: background-color 0.2s;
        }

        .logo-area {
            display: flex;
            align-items: center;
            font-family: 'Google Sans', sans-serif;
            font-size: 22px;
            color: var(--text-color);
            text-decoration: none;
            width: 250px; /* Match sidebar width */
        }

        .search-container {
            flex-grow: 1;
            max-width: 720px;
            margin: 0 24px;
            position: relative;
        }

        .search-input {
            width: 100%;
            height: 48px;
            border-radius: 24px;
            background-color: var(--search-bg);
            border: 1px solid transparent;
            padding: 0 24px 0 56px;
            font-family: 'Google Sans', sans-serif;
            font-size: 16px;
            color: var(--search-text);
            transition: background-color 0.2s, box-shadow 0.2s;
        }
        .search-input:focus {
            background-color: var(--card-bg);
            box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15);
            outline: none;
        }
        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-text);
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .icon-btn {
            background: transparent;
            border: none;
            color: var(--secondary-text);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .icon-btn:hover {
            background-color: var(--hover-bg);
        }

        .layout-container {
            display: flex;
            min-height: calc(100vh - 64px);
        }

        .sidebar { 
            width: 256px; 
            background: var(--bg-color); 
            padding-top: 12px; 
            transition: background-color 0.2s;
            flex-shrink: 0;
        }
        
        .sidebar a { 
            padding: 12px 24px 12px 24px; 
            display: flex; 
            align-items: center;
            color: var(--text-color); 
            text-decoration: none; 
            font-family: 'Google Sans', sans-serif; 
            font-weight: 500;
            border-radius: 0 24px 24px 0;
            margin-right: 12px;
            transition: background-color 0.2s, color 0.2s;
        }
        .sidebar a:hover {
            background-color: var(--hover-bg);
        }
        .sidebar a.active { 
            background-color: var(--active-bg); 
            color: var(--active-text); 
        }
        .sidebar a .material-symbols-outlined {
            margin-right: 18px;
            color: var(--secondary-text);
        }
        .sidebar a.active .material-symbols-outlined {
            color: var(--active-text);
            font-variation-settings: 'FILL' 1;
        }

        .main-content { 
            flex-grow: 1; 
            padding: 24px 40px; 
            max-width: 1040px;
            margin: 0 auto;
        }
        .no-sidebar .main-content { margin-left: 0; max-width: 100%; }

        /* Google Cards */
        .google-card {
            background: var(--card-bg); 
            border-radius: 16px;
            border: 1px solid var(--card-border);
            padding: 24px;
            transition: background-color 0.2s, border-color 0.2s;
            margin-bottom: 24px;
        }
        .google-card-header {
            font-family: 'Google Sans', sans-serif;
            font-size: 22px;
            font-weight: 400;
            margin-bottom: 24px;
        }

        /* Buttons */
        .btn-google {
            background-color: var(--btn-primary-bg); 
            color: var(--btn-primary-text); 
            font-family: 'Google Sans', sans-serif;
            font-weight: 500; 
            border-radius: 4px; 
            padding: 8px 24px; 
            border: none;
            transition: background-color 0.2s;
        }
        .btn-google:hover { background-color: var(--btn-primary-hover); color: var(--btn-primary-text); }
        
        .btn-google-outline {
            background-color: transparent; 
            color: var(--active-text); 
            font-family: 'Google Sans', sans-serif;
            font-weight: 500; 
            border-radius: 4px; 
            padding: 7px 23px; 
            border: 1px solid var(--card-border);
            transition: background-color 0.2s;
        }
        .btn-google-outline:hover { background-color: var(--hover-bg); }

        /* Forms & Modals */
        .modal-content {
            background-color: var(--modal-bg);
            color: var(--text-color);
            border-radius: 16px;
            border: 1px solid var(--card-border);
        }
        .modal-header, .modal-footer {
            border-color: var(--card-border);
        }
        .form-control, .form-select {
            background-color: transparent;
            border-color: var(--input-border);
            color: var(--text-color);
        }
        .form-control:focus, .form-select:focus {
            background-color: transparent;
            color: var(--text-color);
            border-color: var(--active-text);
            box-shadow: inset 0 0 0 1px var(--active-text);
        }
        .form-floating > label { color: var(--secondary-text); }

        /* Table Resets for Dark Mode */
        .table {
            color: var(--text-color);
        }
        .table>:not(caption)>*>* {
            background-color: transparent;
            border-bottom-color: var(--card-border);
            color: var(--text-color);
        }
        .text-muted { color: var(--secondary-text) !important; }
        .text-dark { color: var(--text-color) !important; }

        /* List Items */
        .google-list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            border-bottom: 1px solid var(--card-border);
            transition: background-color 0.2s;
            cursor: pointer;
        }
        .google-list-item:hover {
            background-color: var(--hover-bg);
        }
        .google-list-item:last-child {
            border-bottom: none;
        }

    </style>
    <script>
        const apiBase = '<?php echo $apiBase; ?>';
        
        function getAuthHeaders() {
            const token = localStorage.getItem('admin_token');
            return token ? { 'Authorization': 'Bearer ' + token } : {};
        }

        function checkAuth() {
            if (!localStorage.getItem('admin_token')) {
                window.location.href = '/ohemaadetergents/admin/login';
            }
        }
        
        function logout() {
            localStorage.removeItem('admin_token');
            localStorage.removeItem('admin_user');
            window.location.href = '/ohemaadetergents/admin/login';
        }

        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-theme');
            const target = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', target);
            localStorage.setItem('admin_theme', target);
            
            const icon = document.getElementById('themeIcon');
            if (icon) {
                icon.innerText = target === 'dark' ? 'light_mode' : 'dark_mode';
            }
        }
    </script>
</head>
<body class="<?php echo isset($hideSidebar) && $hideSidebar ? 'no-sidebar' : ''; ?>">

<?php if (!isset($hideSidebar) || !$hideSidebar): ?>
    <div class="topbar">
        <a href="/ohemaadetergents/admin/index" class="logo-area">
            <span style="font-weight: 500;">Google Account</span>
        </a>
        
        <div class="search-container">
            <span class="material-symbols-outlined search-icon">search</span>
            <input type="text" class="search-input" id="globalSearch" placeholder="Search Google Account" onkeyup="if(typeof handleSearch === 'function') handleSearch(this.value)">
        </div>

        <div class="topbar-actions">
            <button class="icon-btn" onclick="toggleTheme()" title="Toggle Theme">
                <span class="material-symbols-outlined" id="themeIcon">
                    <script>document.write(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light_mode' : 'dark_mode');</script>
                </span>
            </button>
            <button class="icon-btn" onclick="logout()" title="Sign Out">
                <span class="material-symbols-outlined">logout</span>
            </button>
            <div id="userAvatar" class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 32px; height: 32px; background-color: var(--btn-primary-bg); font-weight: 500; margin-left: 8px;">
                A
            </div>
        </div>
    </div>

    <div class="layout-container">
        <div class="sidebar">
            <a href="/ohemaadetergents/admin/index" class="<?php echo $currentPage == 'index.php' || $currentPage == 'index' ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">home</span> Home
            </a>
            <a href="/ohemaadetergents/admin/products/index" class="<?php echo strpos($_SERVER['PHP_SELF'], '/products/') !== false ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">inventory_2</span> Products
            </a>
            <a href="/ohemaadetergents/admin/orders/index" class="<?php echo strpos($_SERVER['PHP_SELF'], '/orders/') !== false ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">local_shipping</span> Orders
            </a>
            <a href="/ohemaadetergents/admin/customers/index" class="<?php echo strpos($_SERVER['PHP_SELF'], '/customers/') !== false ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">group</span> Customers
            </a>
            <a href="/ohemaadetergents/admin/admins/index" class="<?php echo strpos($_SERVER['PHP_SELF'], '/admins/') !== false ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">admin_panel_settings</span> Admins
            </a>
        </div>
        <div class="main-content">
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const user = JSON.parse(localStorage.getItem('admin_user') || '{}');
                    if(user.name) {
                        document.getElementById('userAvatar').innerText = user.name.charAt(0).toUpperCase();
                    }
                });
            </script>
<?php endif; ?>
