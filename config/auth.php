<?php
// Hubi in session-ku bilaawdo haddii uusan hore u jirin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once('db.php');

// Basic security helpers
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Haddii CSRF uu dhib kugu hayo inta aad tijaabada ku jirto, 
        // hubi in session-kaagu uu si sax ah u shaqaynayo.
        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
            http_response_code(403);
            die('Invalid CSRF token. Fadlan dib u cusboonaysii (Refresh) bogga.');
        }
    }
}

function set_flash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function esc($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function require_login() {
    if (empty($_SESSION['user_id'])) {
        // Waxaan ku daray BASE_URL haddii aad u baahato, haddii kalena waa login.php
        header('Location: login.php');
        exit;
    }
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function require_admin() {
    require_login();
    if (!isAdmin()) {
        header('Location: unauthorized.php');
        exit;
    }
}