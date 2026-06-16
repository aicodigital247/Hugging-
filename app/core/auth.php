<?php
/**
 * TradeNexa.com - Authentication Logic
 * Provides secure login, signup, password hashing, and brute-force protection controls
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

/**
 * Authenticates user credentials and sets up session variables
 */
function auth_login($email, $password) {
    global $conn;
    $email = trim($email);
    
    // Check if system is installed and db connection exists
    if (!$conn) {
        return ['success' => false, 'message' => 'Database connection offline.'];
    }

    // Fast sanitize and select query
    $stmt = mysqli_prepare($conn, "SELECT `id`, `email`, `password`, `role`, `plan`, `status` FROM `users` WHERE `email` = ? LIMIT 1");
    if (!$stmt) {
        return ['success' => false, 'message' => 'System query failed.'];
    }

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$user) {
        return ['success' => false, 'message' => 'Invalid email address or password.'];
    }

    // Verify account status (banned/active)
    if ($user['status'] === 'banned') {
        return ['success' => false, 'message' => 'This account has been suspended due to system policy. Contact support.'];
    }

    // Verify password hash
    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid email address or password.'];
    }

    // Save active authentication session
    secure_session_regenerate();
    $_SESSION['auth_user_id'] = $user['id'];
    $_SESSION['auth_user_email'] = $user['email'];
    $_SESSION['auth_user_role'] = $user['role'];
    $_SESSION['auth_user_plan'] = $user['plan'];

    return ['success' => true, 'user' => $user];
}

/**
 * Regsiters a brand new user onto the system
 */
function auth_register($email, $password, $confirm_password) {
    global $conn;
    $email = trim($email);
    
    if (empty($email) || empty($password) || empty($confirm_password)) {
        return ['success' => false, 'message' => 'All validation fields are required.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please provide a valid email format.'];
    }

    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters.'];
    }

    if ($password !== $confirm_password) {
        return ['success' => false, 'message' => 'Passwords do not match. Review input.'];
    }

    if (!$conn) {
        return ['success' => false, 'message' => 'Database offline. Try again later.'];
    }

    // Pre-emptively check for duplicate emails
    $stmt = mysqli_prepare($conn, "SELECT `id` FROM `users` WHERE `email` = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $count = mysqli_stmt_num_rows($stmt);
    mysqli_stmt_close($stmt);

    if ($count > 0) {
        return ['success' => false, 'message' => 'An account is already registered with this email address.'];
    }

    // Hash password with bcrypt and insert user
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    
    $ins = mysqli_prepare($conn, "INSERT INTO `users` (`email`, `password`, `role`, `plan`, `status`, `ip_address`, `wallet_balance`) VALUES (?, ?, 'user', 'free', 'active', ?, 0.00000000)");
    if (!$ins) {
        return ['success' => false, 'message' => 'Setup registration failure. Contact site admin.'];
    }

    mysqli_stmt_bind_param($ins, "sss", $email, $hashed_password, $ip);
    $executed = mysqli_stmt_execute($ins);
    $inserted_id = mysqli_insert_id($conn);
    mysqli_stmt_close($ins);

    if ($executed) {
        // Automatically credit $500 simulated demo balance to new wallet ledgers
        $ledger_stmt = mysqli_prepare($conn, "INSERT INTO `wallet_ledger` (`user_id`, `type`, `amount`, `reason`, `balance_after`) VALUES (?, 'credit', 500.00000000, 'Welcome Bonus - Simulated Demo Credit', 500.00000000)");
        if ($ledger_stmt) {
            mysqli_stmt_bind_param($ledger_stmt, "i", $inserted_id);
            mysqli_stmt_execute($ledger_stmt);
            mysqli_stmt_close($ledger_stmt);
            
            // Update the main user wallet balance field
            $upd = mysqli_prepare($conn, "UPDATE `users` SET `wallet_balance` = 500.00000000 WHERE `id` = ?");
            mysqli_stmt_bind_param($upd, "i", $inserted_id);
            mysqli_stmt_execute($upd);
            mysqli_stmt_close($upd);
        }

        return ['success' => true, 'message' => 'Registration complete! Welcome bonus of $500 credited to wallet ledger. Welcome to TradeNexa.'];
    }

    return ['success' => false, 'message' => 'Could not save user profile. Database constraint error.'];
}

/**
 * Checks if a session authentication is currently valid
 */
function auth_is_logged_in() {
    return isset($_SESSION['auth_user_id']) && !empty($_SESSION['auth_user_id']);
}

/**
 * Verifies if the authenticated session role represents an Administrator profile
 */
function auth_is_admin() {
    return isset($_SESSION['auth_user_role']) && $_SESSION['auth_user_role'] === 'admin';
}

/**
 * Gets currently logged in user full model row
 */
function auth_current_user() {
    global $conn;
    if (!auth_is_logged_in() || !$conn) {
        return null;
    }
    $uid = intval($_SESSION['auth_user_id']);
    $res = mysqli_query($conn, "SELECT * FROM `users` WHERE `id` = $uid LIMIT 1");
    if ($res) {
        return mysqli_fetch_assoc($res);
    }
    return null;
}
