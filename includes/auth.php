<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// MEMBER FUNCTIONS
// ============================================

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUserName() {
    return $_SESSION['user_name'] ?? 'Guest';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /booking-system/public/login.php');
        exit();
    }
}

// ============================================
// ADMIN FUNCTIONS
// ============================================

function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function getCurrentAdminId() {
    return $_SESSION['admin_id'] ?? null;
}

function getCurrentAdminName() {
    return $_SESSION['admin_name'] ?? 'Guest';
}

function getCurrentAdminRole() {
    return $_SESSION['admin_role'] ?? 'viewer';
}

function isSuperAdmin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super_admin';
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /booking-system/admin/login.php');
        exit();
    }
}

function requireSuperAdmin() {
    if (!isSuperAdmin()) {
        header('Location: /booking-system/admin/admin.php');
        exit();
    }
}

// ============================================
// LOGOUT FUNCTIONS
// ============================================

function logout() {
    $_SESSION = array();
    session_destroy();
    header('Location: /booking-system/public/index.php');
    exit();
}

function adminLogout() {
    if (isset($_SESSION['admin_id'])) {
        // Only try to include if the file exists
        $admin_functions_path = __DIR__ . '/admin_functions.php';
        if (file_exists($admin_functions_path)) {
            require_once $admin_functions_path;
            logAdminActivity($_SESSION['admin_id'], 'logout', 'admin', $_SESSION['admin_id'], 'Logged out');
        }
    }
    $_SESSION = array();
    session_destroy();
    header('Location: /booking-system/public/index.php');
    exit();
}
?>