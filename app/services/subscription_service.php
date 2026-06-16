<?php
/**
 * TradeNexa.com - Subscription & Payments Coordinator
 * Manages ledger transactions for memberships, upgrades, and debits
 */

require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/session.php';
require_once dirname(__DIR__) . '/core/saas.php';

/**
 * Charges a user's wallet balance using ledger-exclusive transactions to upgrade plans
 */
function subscription_upgrade_plan($user_id, $target_plan) {
    global $conn;
    $user_id = intval($user_id);
    $target_plan = strtolower(trim($target_plan));
    
    // Check valid plans
    $plans = saas_get_pricing_plans();
    if (!isset($plans[$target_plan])) {
        return ['success' => false, 'message' => "Requested membership plan '$target_plan' is invalid."];
    }
    
    $plan_details = $plans[$target_plan];
    $cost = floatval($plan_details['price']);

    if (!$conn) {
        return ['success' => false, 'message' => 'Database connection offline.'];
    }

    // Begin simulated database lock/transaction
    mysqli_query($conn, "START TRANSACTION");

    // Fetch user current balance
    $res = mysqli_query($conn, "SELECT `id`, `plan`, `wallet_balance` FROM `users` WHERE `id` = $user_id FOR UPDATE");
    $user = mysqli_fetch_assoc($res);

    if (!$user) {
        mysqli_query($conn, "ROLLBACK");
        return ['success' => false, 'message' => 'User profile not found.'];
    }

    if ($user['plan'] === $target_plan) {
        mysqli_query($conn, "ROLLBACK");
        return ['success' => false, 'message' => 'Your account is already subscribed to this tier.'];
    }

    $current_balance = floatval($user['wallet_balance']);

    if ($current_balance < $cost) {
        mysqli_query($conn, "ROLLBACK");
        return ['success' => false, 'message' => "Insufficient wallet balance. You require \$$cost but only have \$$current_balance. Deposit simulated credits first!"];
    }

    $new_balance = $current_balance - $cost;

    // 1. Write Debit Ledger Entry (NON-NEGOTIABLE CORE ARCHITECTURE REQUIREMENT)
    $stmt1 = mysqli_prepare($conn, "INSERT INTO `wallet_ledger` (`user_id`, `type`, `amount`, `reason`, `balance_after`) VALUES (?, 'debit', ?, ?, ?)");
    if (!$stmt1) {
        mysqli_query($conn, "ROLLBACK");
        return ['success' => false, 'message' => 'Ledger insertion error. Try again.'];
    }
    
    $reason = "Subscription Purchase - " . $plan_details['name'];
    mysqli_stmt_bind_param($stmt1, "idss", $user_id, $cost, $reason, $new_balance);
    mysqli_stmt_execute($stmt1);
    mysqli_stmt_close($stmt1);

    // 2. Perform safe user update (Set new balance and status tier)
    $stmt2 = mysqli_prepare($conn, "UPDATE `users` SET `plan` = ?, `wallet_balance` = ? WHERE `id` = ?");
    if (!$stmt2) {
        mysqli_query($conn, "ROLLBACK");
        return ['success' => false, 'message' => 'Member records lock error.'];
    }

    mysqli_stmt_bind_param($stmt2, "sdi", $target_plan, $new_balance, $user_id);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);

    // Commit Transaction
    mysqli_query($conn, "COMMIT");

    // Refresh active session flags if self
    if (isset($_SESSION['auth_user_id']) && intval($_SESSION['auth_user_id']) === $user_id) {
        $_SESSION['auth_user_plan'] = $target_plan;
    }

    return [
        'success' => true,
        'message' => "Congratulations! Your TradeNexa membership has been upgraded to " . $plan_details['name'] . ". \$$cost has been securely debited from your simulation ledger.",
        'new_balance' => $new_balance,
        'new_plan' => $target_plan
    ];
}

/**
 * Handles adding positive credit entries to user wallet ledgers
 */
function subscription_deposit_mock_funds($user_id, $amount) {
    global $conn;
    $user_id = intval($user_id);
    $amount = floatval($amount);
    
    if ($amount <= 0) {
        return ['success' => false, 'message' => 'Deposit amount must be positive.'];
    }

    if (!$conn) {
        return ['success' => false, 'message' => 'Database offline.'];
    }

    mysqli_query($conn, "START TRANSACTION");

    // Fetch user current balance
    $res = mysqli_query($conn, "SELECT `wallet_balance` FROM `users` WHERE `id` = $user_id FOR UPDATE");
    $user = mysqli_fetch_assoc($res);

    if (!$user) {
        mysqli_query($conn, "ROLLBACK");
        return ['success' => false, 'message' => 'User profile not found.'];
    }

    $current_balance = floatval($user['wallet_balance']);
    $new_balance = $current_balance + $amount;

    // Insert Credit Ledger Entry
    $stmt = mysqli_prepare($conn, "INSERT INTO `wallet_ledger` (`user_id`, `type`, `amount`, `reason`, `balance_after`) VALUES (?, 'credit', ?, 'Simulated Demo Deposit Credit', ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "idd", $user_id, $amount, $new_balance);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        // Update user direct balance column
        mysqli_query($conn, "UPDATE `users` SET `wallet_balance` = $new_balance WHERE `id` = $user_id");
        mysqli_query($conn, "COMMIT");
        
        return ['success' => true, 'message' => "Successfully credited \$$amount simulated funds to your wallet balance.", 'new_balance' => $new_balance];
    }

    mysqli_query($conn, "ROLLBACK");
    return ['success' => false, 'message' => 'Failed to process ledger deposit.'];
}
