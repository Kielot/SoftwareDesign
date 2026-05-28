<?php
/**
 * auth.php
 * BICpES Learning Hub — Authentication Logic
 */

// ─── SESSION BOOTSTRAP ────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false,        // false = works on HTTP localhost (XAMPP)
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

require_once __DIR__ . '/db_connect.php';

// ─── CONSTANTS ────────────────────────────────────────────────────────────────
define('PASSWORD_COST',       12);
define('MAX_LOGIN_ATTEMPTS',   5);
define('LOCKOUT_MINUTES',     15);

// ─── SESSION HELPERS ──────────────────────────────────────────────────────────

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_role(): ?string
{
    return $_SESSION['role'] ?? null;
}

function is_admin(): bool
{
    return is_logged_in() && current_role() === 'admin';
}

function redirect(string $url = 'index.php'): never
{
    header('Location: ' . $url);
    exit;
}

function require_admin(): void
{
    if (!is_admin()) {
        redirect('index.php');
    }
}

/**
 * Guards a page to logged-in users only.
 * Redirects to index.php with a flag that triggers the login modal.
 */
function require_login(): void
{
    if (!is_logged_in()) {
        redirect('index.php?require_login=1');
    }
}

// ─── REGISTRATION ─────────────────────────────────────────────────────────────

function register_student(
    string $student_number,
    string $last_name,
    string $first_name,
    string $date_of_birth,
    string $password
): array {
    $student_number = trim($student_number);
    $last_name      = trim($last_name);
    $first_name     = trim($first_name);
    $date_of_birth  = trim($date_of_birth);

    if (!preg_match('/^\d{5,10}$/', $student_number)) {
        return ['success' => false, 'message' => 'Student number must be 5–10 digits.'];
    }
    if (empty($last_name) || empty($first_name)) {
        return ['success' => false, 'message' => 'First and last name are required.'];
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_of_birth) || !strtotime($date_of_birth)) {
        return ['success' => false, 'message' => 'Invalid date of birth.'];
    }
    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
    }

    $db   = get_db();
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);

    $stmt = $db->prepare('SELECT id FROM users WHERE student_number = ? LIMIT 1');
    $stmt->bind_param('s', $student_number);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        return ['success' => false, 'message' => 'Student number is already registered.'];
    }
    $stmt->close();

    $stmt = $db->prepare(
        'INSERT INTO users (student_number, last_name, first_name, date_of_birth, password_hash, role)
         VALUES (?, ?, ?, ?, ?, "student")'
    );
    $stmt->bind_param('sssss', $student_number, $last_name, $first_name, $date_of_birth, $hash);

    if (!$stmt->execute()) {
        error_log('[BICpES Auth] Registration failed: ' . $stmt->error);
        $stmt->close();
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
    $stmt->close();

    return ['success' => true, 'message' => 'Account created successfully. You can now log in.'];
}

// ─── LOGIN ────────────────────────────────────────────────────────────────────

/**
 * Authenticates a user.
 *
 * STUDENT: login with their numeric student_number.
 * ADMIN:   login with the keyword "ADMIN" (case-insensitive) as the
 *          student_number field — the query matches on NULL student_number
 *          rows whose role = 'admin'.
 */
function login_user(string $identifier, string $password): array
{
    $identifier = trim($identifier);

    // Brute-force guard
    $attempts_key = 'login_attempts_' . md5($identifier);
    $lockout_key  = 'login_lockout_'  . md5($identifier);

    if (!empty($_SESSION[$lockout_key]) && time() < $_SESSION[$lockout_key]) {
        $remaining = ceil(($_SESSION[$lockout_key] - time()) / 60);
        return [
            'success' => false,
            'message' => "Too many failed attempts. Try again in {$remaining} minute(s).",
        ];
    }

    $db = get_db();

    // ── Determine query strategy ──────────────────────────────────────────────
    // If the identifier looks like "ADMIN" (letters only), try admin accounts
    // that have NULL student_number. Otherwise query by student_number.
    if (strtoupper($identifier) === 'ADMIN') {
        // Fetch by role = admin, student_number IS NULL
        $stmt = $db->prepare(
            "SELECT id, last_name, first_name, password_hash, role, student_number
             FROM users
             WHERE role = 'admin'
             LIMIT 1"
        );
        $stmt->execute();
    } else {
        $stmt = $db->prepare(
            "SELECT id, last_name, first_name, password_hash, role, student_number
             FROM users
             WHERE student_number = ?
             LIMIT 1"
        );
        $stmt->bind_param('s', $identifier);
        $stmt->execute();
    }

    $result = $stmt->get_result();
    $user   = $result->fetch_assoc();
    $stmt->close();

    // Verify password
    if (!$user || !password_verify($password, $user['password_hash'])) {
        $_SESSION[$attempts_key] = ($_SESSION[$attempts_key] ?? 0) + 1;

        if ($_SESSION[$attempts_key] >= MAX_LOGIN_ATTEMPTS) {
            $_SESSION[$lockout_key]  = time() + (LOCKOUT_MINUTES * 60);
            $_SESSION[$attempts_key] = 0;
        }

        return ['success' => false, 'message' => 'Invalid credentials.'];
    }

    // Re-hash if cost changed
    if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost' => PASSWORD_COST])) {
        $new_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
        $upd      = $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $upd->bind_param('si', $new_hash, $user['id']);
        $upd->execute();
        $upd->close();
    }

    // Create session
    session_regenerate_id(true);

    $display_student_number = $user['student_number'] ?? 'N/A';

    $_SESSION['user_id']        = $user['id'];
    $_SESSION['first_name']     = $user['first_name'];
    $_SESSION['last_name']      = $user['last_name'];
    $_SESSION['full_name']      = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['role']           = $user['role'];
    $_SESSION['student_number'] = $display_student_number;

    unset($_SESSION[$attempts_key], $_SESSION[$lockout_key]);

    return ['success' => true, 'message' => 'Login successful.'];
}

// ─── LOGOUT ───────────────────────────────────────────────────────────────────

function logout_user(): never
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }

    session_destroy();
    redirect('index.php');
}

// ─── UPDATE PROFILE ───────────────────────────────────────────────────────────

function update_profile(
    int    $user_id,
    string $last_name,
    string $first_name,
    string $date_of_birth
): array {
    if (!is_logged_in() || $_SESSION['user_id'] !== $user_id) {
        return ['success' => false, 'message' => 'Unauthorized.'];
    }

    $last_name     = trim($last_name);
    $first_name    = trim($first_name);
    $date_of_birth = trim($date_of_birth);

    if (empty($last_name) || empty($first_name)) {
        return ['success' => false, 'message' => 'Name fields cannot be empty.'];
    }

    $db   = get_db();
    $stmt = $db->prepare(
        'UPDATE users SET last_name = ?, first_name = ?, date_of_birth = ? WHERE id = ?'
    );
    $stmt->bind_param('sssi', $last_name, $first_name, $date_of_birth, $user_id);

    if (!$stmt->execute()) {
        $stmt->close();
        return ['success' => false, 'message' => 'Update failed.'];
    }
    $stmt->close();

    $_SESSION['first_name'] = $first_name;
    $_SESSION['last_name']  = $last_name;
    $_SESSION['full_name']  = "$first_name $last_name";

    return ['success' => true, 'message' => 'Profile updated successfully.'];
}

// ─── CHANGE PASSWORD ──────────────────────────────────────────────────────────

function change_password(
    int    $user_id,
    string $current_password,
    string $new_password
): array {
    if (!is_logged_in() || $_SESSION['user_id'] !== $user_id) {
        return ['success' => false, 'message' => 'Unauthorized.'];
    }
    if (strlen($new_password) < 8) {
        return ['success' => false, 'message' => 'New password must be at least 8 characters.'];
    }

    $db   = get_db();
    $stmt = $db->prepare('SELECT password_hash FROM users WHERE id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $row  = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || !password_verify($current_password, $row['password_hash'])) {
        return ['success' => false, 'message' => 'Current password is incorrect.'];
    }

    $new_hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
    $stmt     = $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
    $stmt->bind_param('si', $new_hash, $user_id);
    $stmt->execute();
    $stmt->close();

    return ['success' => true, 'message' => 'Password updated successfully.'];
}

// ─── AJAX ENDPOINT DISPATCHER ─────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename($_SERVER['SCRIPT_FILENAME']) === 'auth.php') {
    header('Content-Type: application/json');

    $input  = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $input['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'register':
            echo json_encode(register_student(
                $input['student_number'] ?? '',
                $input['last_name']      ?? '',
                $input['first_name']     ?? '',
                $input['date_of_birth']  ?? '',
                $input['password']       ?? ''
            ));
            break;

        case 'login':
            $result = login_user(
                $input['student_number'] ?? '',
                $input['password']       ?? ''
            );
            if ($result['success']) {
                $result['user'] = [
                    'full_name'      => $_SESSION['full_name'],
                    'role'           => $_SESSION['role'],
                    'student_number' => $_SESSION['student_number'],
                ];
            }
            echo json_encode($result);
            break;

        case 'check_session':
            if (is_logged_in()) {
                echo json_encode([
                    'logged_in'      => true,
                    'full_name'      => $_SESSION['full_name'],
                    'role'           => $_SESSION['role'],
                    'student_number' => $_SESSION['student_number'],
                    'first_name'     => $_SESSION['first_name'],
                    'last_name'      => $_SESSION['last_name'],
                ]);
            } else {
                echo json_encode(['logged_in' => false]);
            }
            break;

        case 'logout':
            logout_user(); // redirects — no JSON response

        case 'update_profile':
            if (!is_logged_in()) {
                echo json_encode(['success' => false, 'message' => 'Not logged in.']);
                break;
            }
            echo json_encode(update_profile(
                (int) $_SESSION['user_id'],
                $input['last_name']     ?? '',
                $input['first_name']    ?? '',
                $input['date_of_birth'] ?? ''
            ));
            break;

        case 'change_password':
            if (!is_logged_in()) {
                echo json_encode(['success' => false, 'message' => 'Not logged in.']);
                break;
            }
            echo json_encode(change_password(
                (int) $_SESSION['user_id'],
                $input['current_password'] ?? '',
                $input['new_password']     ?? ''
            ));
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }
    exit;
}