<?php

/**
 * Transaction and Payment History Helper Functions
 */

/**
 * Get transaction history for a user
 */
function get_user_transactions($userId, $userType, $limit = 50, $offset = 0) {
    if ($userType === 'client') {
        $stmt = db_prepare("
            SELECT t.*, c.contract_amount, pb.title as brief_title,
                   u.first_name, u.last_name, u.email
            FROM transactions t
            LEFT JOIN contracts c ON t.contract_id = c.id
            LEFT JOIN project_briefs pb ON c.project_brief_id = pb.id
            LEFT JOIN users u ON t.payee_user_id = u.id
            WHERE t.payer_user_id = ?
            ORDER BY t.created_at DESC
            LIMIT ? OFFSET ?
        ");
    } else {
        // Creator
        $stmt = db_prepare("
            SELECT t.*, c.contract_amount, pb.title as brief_title,
                   u.first_name, u.last_name, u.email
            FROM transactions t
            LEFT JOIN contracts c ON t.contract_id = c.id
            LEFT JOIN project_briefs pb ON c.project_brief_id = pb.id
            LEFT JOIN users u ON t.payer_user_id = u.id
            WHERE t.payee_user_id = ?
            ORDER BY t.created_at DESC
            LIMIT ? OFFSET ?
        ");
    }

    $stmt->bind_param('iii', $userId, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $transactions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $transactions;
}

/**
 * Get earnings summary for creator
 */
function get_creator_earnings_summary($userId) {
    $stmt = db_prepare("
        SELECT
            SUM(CASE WHEN transaction_type = 'payout' AND status = 'completed' THEN amount ELSE 0 END) as total_withdrawn,
            SUM(CASE WHEN transaction_type = 'payout' AND status = 'pending' THEN amount ELSE 0 END) as available_balance,
            SUM(CASE WHEN transaction_type = 'payment' AND status = 'completed' THEN amount ELSE 0 END) as total_earned,
            COUNT(DISTINCT contract_id) as total_contracts
        FROM transactions
        WHERE payee_user_id = ?
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $summary = $result->fetch_assoc();
    $stmt->close();

    return [
        'total_withdrawn' => (int)($summary['total_withdrawn'] ?? 0),
        'available_balance' => (int)($summary['available_balance'] ?? 0),
        'total_earned' => (int)($summary['total_earned'] ?? 0),
        'total_contracts' => (int)($summary['total_contracts'] ?? 0),
        'pending_withdrawal' => (int)($summary['available_balance'] ?? 0)
    ];
}

/**
 * Get spending summary for client
 */
function get_client_spending_summary($userId) {
    $stmt = db_prepare("
        SELECT
            SUM(CASE WHEN transaction_type = 'payment' AND status = 'completed' THEN amount ELSE 0 END) as total_spent,
            SUM(CASE WHEN transaction_type = 'refund' AND status = 'completed' THEN amount ELSE 0 END) as total_refunded,
            COUNT(DISTINCT contract_id) as total_contracts,
            SUM(CASE WHEN transaction_type = 'payment' AND status = 'pending' THEN amount ELSE 0 END) as pending_payments
        FROM transactions
        WHERE payer_user_id = ?
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $summary = $result->fetch_assoc();
    $stmt->close();

    return [
        'total_spent' => (int)($summary['total_spent'] ?? 0),
        'total_refunded' => (int)($summary['total_refunded'] ?? 0),
        'total_contracts' => (int)($summary['total_contracts'] ?? 0),
        'pending_payments' => (int)($summary['pending_payments'] ?? 0),
        'net_spent' => ((int)($summary['total_spent'] ?? 0)) - ((int)($summary['total_refunded'] ?? 0))
    ];
}

/**
 * Get transaction by ID with verification
 */
function get_transaction_by_id($transactionId, $userId = null, $userType = null) {
    if ($userId && $userType) {
        if ($userType === 'client') {
            $stmt = db_prepare("
                SELECT t.*, c.contract_amount, pb.title as brief_title
                FROM transactions t
                LEFT JOIN contracts c ON t.contract_id = c.id
                LEFT JOIN project_briefs pb ON c.project_brief_id = pb.id
                WHERE t.id = ? AND t.payer_user_id = ?
            ");
        } else if ($userType === 'creator') {
            $stmt = db_prepare("
                SELECT t.*, c.contract_amount, pb.title as brief_title
                FROM transactions t
                LEFT JOIN contracts c ON t.contract_id = c.id
                LEFT JOIN project_briefs pb ON c.project_brief_id = pb.id
                WHERE t.id = ? AND t.payee_user_id = ?
            ");
        } else {
            // Admin can see all
            $stmt = db_prepare("
                SELECT t.*, c.contract_amount, pb.title as brief_title
                FROM transactions t
                LEFT JOIN contracts c ON t.contract_id = c.id
                LEFT JOIN project_briefs pb ON c.project_brief_id = pb.id
                WHERE t.id = ?
            ");
            $stmt->bind_param('i', $transactionId);
        }

        if ($userType !== 'admin') {
            $stmt->bind_param('ii', $transactionId, $userId);
        }
    } else {
        $stmt = db_prepare("SELECT * FROM transactions WHERE id = ?");
        $stmt->bind_param('i', $transactionId);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $transaction = $result->fetch_assoc();
    $stmt->close();

    return $transaction;
}

/**
 * Generate receipt/invoice for a transaction
 */
function generate_transaction_receipt($transactionId, $userId, $userType) {
    $transaction = get_transaction_by_id($transactionId, $userId, $userType);

    if (!$transaction) {
        return null;
    }

    $user = find_user_by_id($userId);

    return [
        'transaction' => $transaction,
        'user' => $user,
        'receipt_number' => 'RCPT-' . str_pad($transactionId, 8, '0', STR_PAD_LEFT),
        'generated_at' => date('Y-m-d H:i:s'),
        'platform_name' => APP_NAME,
        'platform_url' => APP_URL
    ];
}

/**
 * Get recent transactions for dashboard
 */
function get_recent_transactions($userId, $userType, $limit = 5) {
    return get_user_transactions($userId, $userType, $limit, 0);
}

/**
 * Get transaction statistics for admin
 */
function get_transaction_statistics($startDate = null, $endDate = null) {
    $whereClause = '';
    if ($startDate && $endDate) {
        $whereClause = " WHERE created_at BETWEEN ? AND ?";
    }

    $stmt = db_prepare("
        SELECT
            SUM(CASE WHEN transaction_type = 'payment' AND status = 'completed' THEN amount ELSE 0 END) as total_payments,
            SUM(CASE WHEN transaction_type = 'payout' AND status = 'completed' THEN amount ELSE 0 END) as total_payouts,
            SUM(CASE WHEN transaction_type = 'fee' AND status = 'completed' THEN amount ELSE 0 END) as total_fees,
            SUM(CASE WHEN transaction_type = 'refund' AND status = 'completed' THEN amount ELSE 0 END) as total_refunds,
            COUNT(CASE WHEN transaction_type = 'payment' AND status = 'completed' THEN 1 END) as payment_count,
            COUNT(CASE WHEN transaction_type = 'payout' AND status = 'completed' THEN 1 END) as payout_count
        FROM transactions
        $whereClause
    ");

    if ($startDate && $endDate) {
        $stmt->bind_param('ss', $startDate, $endDate);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $stats = $result->fetch_assoc();
    $stmt->close();

    return [
        'total_payments' => (int)($stats['total_payments'] ?? 0),
        'total_payouts' => (int)($stats['total_payouts'] ?? 0),
        'total_fees' => (int)($stats['total_fees'] ?? 0),
        'total_refunds' => (int)($stats['total_refunds'] ?? 0),
        'payment_count' => (int)($stats['payment_count'] ?? 0),
        'payout_count' => (int)($stats['payout_count'] ?? 0),
        'platform_revenue' => ((int)($stats['total_payments'] ?? 0)) - ((int)($stats['total_payouts'] ?? 0)) - ((int)($stats['total_refunds'] ?? 0))
    ];
}

/**
 * Export transactions to CSV
 */
function export_transactions_csv($userId, $userType, $filename = 'transactions.csv') {
    $transactions = get_user_transactions($userId, $userType, 1000, 0);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // CSV Headers
    fputcsv($output, [
        'Transaction ID',
        'Date',
        'Type',
        'Description',
        'Amount',
        'Status',
        'Payment Method',
        'Reference'
    ]);

    // Data rows
    foreach ($transactions as $transaction) {
        fputcsv($output, [
            $transaction['id'],
            format_datetime($transaction['created_at']),
            ucfirst($transaction['transaction_type']),
            $transaction['brief_title'] ?? 'N/A',
            format_money($transaction['amount']),
            ucfirst($transaction['status']),
            ucfirst($transaction['payment_provider']),
            $transaction['provider_transaction_id'] ?? 'N/A'
        ]);
    }

    fclose($output);
    exit;
}
