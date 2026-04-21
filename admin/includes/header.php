<?php
$apiBase = '/ohemaadetergents/api';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ohemaa Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #f8f9fa; color: #202124; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Google Sans', sans-serif; }
        .google-card {
            background: #fff; border-radius: 8px;
            box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15);
            padding: 24px; border: none;
        }
        .btn-google {
            background-color: #1a73e8; color: white; font-family: 'Google Sans', sans-serif;
            font-weight: 500; border-radius: 4px; padding: 8px 24px; border: none;
        }
        .btn-google:hover { background-color: #1b66c9; color: white; }
        .btn-google-outline {
            background-color: transparent; color: #1a73e8; font-family: 'Google Sans', sans-serif;
            font-weight: 500; border-radius: 4px; padding: 7px 23px; border: 1px solid #dadce0;
        }
        .btn-google-outline:hover { background-color: #f8f9fa; border-color: #d2e3fc; }
        .form-floating > label { color: #5f6368; }
        .form-control:focus { border-color: #1a73e8; box-shadow: inset 0 0 0 1px #1a73e8; }
        
        /* Layout */
        .navbar-google { background-color: #fff; box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3); padding: 12px 24px; z-index: 1000; position: sticky; top: 0; }
        .sidebar { width: 250px; height: calc(100vh - 60px); position: fixed; background: #fff; box-shadow: 1px 0 2px rgba(60,64,67,0.1); padding-top: 20px; }
        .sidebar a { padding: 12px 24px; display: block; color: #3c4043; text-decoration: none; font-family: 'Google Sans', sans-serif; font-weight: 500; }
        .sidebar a:hover, .sidebar a.active { background-color: #e8f0fe; color: #1a73e8; border-radius: 0 24px 24px 0; margin-right: 12px; }
        .main-content { margin-left: 250px; padding: 24px; }
        .no-sidebar .main-content { margin-left: 0; }
    </style>
    <script>
        const apiBase = '<?php echo $apiBase; ?>';
        
        function getAuthHeaders() {
            const token = localStorage.getItem('admin_token');
            return token ? { 'Authorization': 'Bearer ' + token } : {};
        }

        function checkAuth() {
            if (!localStorage.getItem('admin_token')) {
                window.location.href = '/ohemaadetergents/admin/login.php';
            }
        }
        
        function logout() {
            localStorage.removeItem('admin_token');
            localStorage.removeItem('admin_user');
            window.location.href = '/ohemaadetergents/admin/login.php';
        }
    </script>
</head>
<body class="<?php echo isset($hideSidebar) && $hideSidebar ? 'no-sidebar' : ''; ?>">
<?php if (!isset($hideSidebar) || !$hideSidebar): ?>
    <nav class="navbar-google d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <h4 class="mb-0 text-primary" style="font-family: 'Google Sans', sans-serif; font-weight: 500;">Ohemaa Admin</h4>
        </div>
        <div>
            <span id="userName" class="me-3 fw-medium"></span>
            <button onclick="logout()" class="btn-google-outline btn-sm">Sign Out</button>
        </div>
    </nav>
    <div class="sidebar">
        <a href="/ohemaadetergents/admin/index.php">Dashboard</a>
        <a href="/ohemaadetergents/admin/products/index.php">Products</a>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const user = JSON.parse(localStorage.getItem('admin_user') || '{}');
            if(user.name) document.getElementById('userName').innerText = user.name;
        });
    </script>
<?php endif; ?>
<div class="main-content">
