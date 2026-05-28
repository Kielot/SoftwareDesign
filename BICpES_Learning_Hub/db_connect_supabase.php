<?php
/**
 * db_connect_supabase.php
 * BICpES Learning Hub — Database Connection for Supabase (PostgreSQL)
 *
 * This file connects to Supabase PostgreSQL database.
 * For local development, use db_connect.php (MySQL/XAMPP)
 *
 * To get your Supabase credentials:
 * 1. Go to https://supabase.com
 * 2. Create a new project
 * 3. In Project Settings → Database, copy:
 *    - Host (DB_HOST)
 *    - User (postgres)
 *    - Password (DB_PASS)
 *    - Database (postgres)
 *
 * Set these in your environment variables or .env file
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

// ─── SUPABASE CONFIGURATION ───────────────────────────────────────────────
$db_type = $_ENV['DB_TYPE'] ?? getenv('DB_TYPE') ?? 'postgres';
$db_host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost';
$db_port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? 5432;
$db_user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?? 'postgres';
$db_pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?? '';
$db_name = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'postgres';

define('DB_TYPE',    $db_type);
define('DB_HOST',    $db_host);
define('DB_PORT',    $db_port);
define('DB_USER',    $db_user);
define('DB_PASS',    $db_pass);
define('DB_NAME',    $db_name);
define('DB_CHARSET', 'utf8');

/**
 * Establishes connection to Supabase PostgreSQL database using PDO.
 * Returns PDO connection object.
 */
function get_db(): PDO
{
    static $conn = null;
    if ($conn !== null) return $conn;

    try {
        $dsn = 'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $conn = new PDO(
            $dsn,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $e) {
        error_log('[BICpES DB] Connection failed: ' . $e->getMessage());
        throw new RuntimeException('Database connection failed. Please try again later.');
    }
    return $conn;
}

/**
 * Helper: Execute a query with bound parameters.
 * Usage: query("SELECT * FROM users WHERE id = ?", [$id])
 */
function query(string $sql, array $params = [])
{
    $db = get_db();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Helper: Fetch all rows from a query.
 */
function fetch_all(string $sql, array $params = []): array
{
    return query($sql, $params)->fetchAll();
}

/**
 * Helper: Fetch a single row.
 */
function fetch_one(string $sql, array $params = [])
{
    return query($sql, $params)->fetch();
}

/**
 * Helper: Execute INSERT/UPDATE/DELETE and get affected rows.
 */
function execute(string $sql, array $params = []): int
{
    return query($sql, $params)->rowCount();
}
