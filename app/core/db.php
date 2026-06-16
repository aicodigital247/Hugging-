<?php
/**
 * TradeNexa.com - Database Connection Handler
 * MySQLi API ONLY (Strictly No PDO as requested)
 */

if (!defined('DB_HOST')) {
    define('DB_HOST', '127.0.0.1');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'tradenexa_db');
}

$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Let router handle DB failure beautifully without hard crash if not yet installed
if (!$conn) {
    // If we're not inside installer, tell user to run installer.php
    if (strpos($_SERVER['REQUEST_URI'], '/install') === false) {
        header("Location: /install/index.php");
        exit;
    }
} else {
    mysqli_set_charset($conn, 'utf8mb4');
}

/**
 * Escapes database bound string values to avoid raw string injections
 */
function db_escape($value) {
    global $conn;
    if ($conn) {
        return mysqli_real_escape_string($conn, $value);
    }
    return addslashes($value);
}

/**
 * Executes a mysqli prepared statement query block securely
 */
function db_query($sql, $params = [], $types = "") {
    global $conn;
    if (!$conn) {
        return false;
    }
    
    if (empty($params)) {
        return mysqli_query($conn, $sql);
    }
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return false;
    }
    
    if (empty($types)) {
        $types = str_repeat("s", count($params)); // default all to strings
    }
    
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    
    // For SELECT queries returning rows
    if (stripos($sql, "SELECT") === 0 || stripos($sql, "SHOW") === 0) {
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }
    
    // For INSERT, UPDATE, DELETE returning boolean status
    $affected = mysqli_stmt_affected_rows($stmt);
    $insert_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    
    return [
        'affected_rows' => $affected,
        'insert_id' => $insert_id,
        'status' => true
    ];
}
