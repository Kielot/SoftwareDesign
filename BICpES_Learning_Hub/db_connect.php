<?php
/**
 * db_connect.php (Universal Version)
 * BICpES Learning Hub — Database Connection
 * 
 * Supports both MySQL (local development) and PostgreSQL (Supabase/production)
 * Automatically detects environment and uses appropriate database
 */

// ─── LOAD ENVIRONMENT VARIABLES ────────────────────────────────────────────
if (file_exists(__DIR__ . '/../.env')) {
    $env_lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// ─── DETECT ENVIRONMENT & LOAD CONFIGURATION ──────────────────────────────
$is_production = !in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', 'localhost:8000', 'localhost:3000']);

if ($is_production || getenv('APP_ENV') === 'production') {
    // ─ PRODUCTION: Supabase PostgreSQL ─
    $db_type = $_ENV['DB_TYPE'] ?? getenv('DB_TYPE') ?? 'postgres';
    $db_host = $_ENV['DB_HOST'] ?? getenv('DB_HOST');
    $db_port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? 5432;
    $db_user = $_ENV['DB_USER'] ?? getenv('DB_USER');
    $db_pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS');
    $db_name = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'postgres';
} else {
    // ─ LOCAL: MySQL (XAMPP Default) ─
    $db_type = 'mysql';
    $db_host = 'localhost';
    $db_port = 3306;
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'bicpes_hub';
}

define('DB_TYPE',    $db_type);
define('DB_HOST',    $db_host);
define('DB_PORT',    $db_port);
define('DB_USER',    $db_user);
define('DB_PASS',    $db_pass);
define('DB_NAME',    $db_name);
define('DB_CHARSET', $db_type === 'postgres' ? 'UTF8' : 'utf8mb4');

/**
 * Universal database connection using PDO.
 * Supports MySQL and PostgreSQL seamlessly.
 */
function get_db(): PDO
{
    static $conn = null;
    if ($conn !== null) return $conn;

    try {
        if (DB_TYPE === 'postgres') {
            // ─ PostgreSQL Connection (Supabase) ─
            $dsn = sprintf(
                'pgsql:host=%s;port=%d;dbname=%s;charset=%s',
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_CHARSET
            );
        } else {
            // ─ MySQL Connection (Local Development) ─
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_CHARSET
            );
        }

        $conn = new PDO(
            $dsn,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 10,
            ]
        );

        // Set session timezone for consistency
        if (DB_TYPE === 'postgres') {
            $conn->exec("SET timezone TO 'UTC'");
        } else {
            $conn->exec("SET SESSION sql_mode='STRICT_TRANS_TABLES'");
        }

    } catch (PDOException $e) {
        error_log('[BICpES DB] Connection failed: ' . $e->getMessage());
        throw new RuntimeException('Database connection failed. Please try again later.');
    }

    return $conn;
}