<?php

/**
 * Contract and Milestone Management Helper Functions
 */

/**
 * Create a milestone for a contract
 */
function create_milestone($contractId, $title, $description, $amount, $dueDate = null) {
    $stmt = db_prepare("
        INSERT INTO contract_milestones (contract_id, title, description, amount, due_date, status)
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");

    $stmt->bind_param('issis', $contractId, $title, $description, $amount, $dueDate);

    if ($stmt->execute()) {
        $milestoneId = db_last_insert_id();
        $stmt->close();
        return ['success' => true, 'milestone_id' => $milestoneId];
    }

    $stmt->close();
    return ['success' => false, 'error' => 'Failed to create milestone'];
}

/**
 * Update milestone status
 */
function update_milestone_status($milestoneId, $status) {
    $validStatuses = ['pending', 'in_progress', 'submitted', 'approved', 'paid'];

    if (!in_array($status, $validStatuses)) {
        return ['success' => false, 'error' => 'Invalid status'];
    }

    $updateField = '';
    switch ($status) {
        case 'submitted':
            $updateField = ', submitted_at = NOW()';
            break;
        case 'approved':
            $updateField = ', approved_at = NOW()';
            break;
        case 'paid':
            $updateField = ', paid_at = NOW()';
            break;
    }

    $stmt = db_prepare("UPDATE contract_milestones SET status = ? {$updateField} WHERE id = ?");
    $stmt->bind_param('si', $status, $milestoneId);

    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true];
    }

    $stmt->close();
    return ['success' => false, 'error' => 'Failed to update milestone status'];
}

/**
 * Get all milestones for a contract
 */
function get_contract_milestones($contractId) {
    $stmt = db_prepare("
        SELECT * FROM contract_milestones
        WHERE contract_id = ?
        ORDER BY created_at ASC
    ");
    $stmt->bind_param('i', $contractId);
    $stmt->execute();
    $result = $stmt->get_result();
    $milestones = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $milestones;
}

/**
 * Get milestone by ID with verification
 */
function get_milestone_by_id($milestoneId, $userId = null, $userRole = null) {
    if ($userId && $userRole) {
        // Verify user has access to this milestone
        if ($userRole === 'client') {
            $stmt = db_prepare("
                SELECT m.*, c.client_profile_id
                FROM contract_milestones m
                JOIN contracts c ON m.contract_id = c.id
                JOIN client_profiles cp ON c.client_profile_id = cp.id
                WHERE m.id = ? AND cp.user_id = ?
            ");
        } else {
            $stmt = db_prepare("
                SELECT m.*, c.creator_profile_id
                FROM contract_milestones m
                JOIN contracts c ON m.contract_id = c.id
                JOIN creator_profiles crp ON c.creator_profile_id = crp.id
                WHERE m.id = ? AND crp.user_id = ?
            ");
        }
        $stmt->bind_param('ii', $milestoneId, $userId);
    } else {
        $stmt = db_prepare("SELECT * FROM contract_milestones WHERE id = ?");
        $stmt->bind_param('i', $milestoneId);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $milestone = $result->fetch_assoc();
    $stmt->close();

    return $milestone;
}

/**
 * Calculate milestone progress for a contract
 */
function get_milestone_progress($contractId) {
    $stmt = db_prepare("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid,
            SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) as approved_amount,
            SUM(amount) as total_amount
        FROM contract_milestones
        WHERE contract_id = ?
    ");
    $stmt->bind_param('i', $contractId);
    $stmt->execute();
    $result = $stmt->get_result();
    $progress = $result->fetch_assoc();
    $stmt->close();

    return [
        'total' => (int)$progress['total'],
        'completed' => (int)$progress['completed'],
        'paid' => (int)$progress['paid'],
        'approved_amount' => (int)$progress['approved_amount'],
        'total_amount' => (int)$progress['total_amount'],
        'completion_percentage' => $progress['total'] > 0
            ? round(($progress['completed'] / $progress['total']) * 100)
            : 0
    ];
}

/**
 * Check if all milestones are completed
 */
function all_milestones_completed($contractId) {
    $stmt = db_prepare("
        SELECT COUNT(*) as total,
               SUM(CASE WHEN status IN ('approved', 'paid') THEN 1 ELSE 0 END) as completed
        FROM contract_milestones
        WHERE contract_id = ?
    ");
    $stmt->bind_param('i', $contractId);
    $stmt->execute();
    $result = $stmt->get_result();
    $counts = $result->fetch_assoc();
    $stmt->close();

    return $counts['total'] > 0 && $counts['total'] == $counts['completed'];
}

/**
 * Delete a milestone (only if not started)
 */
function delete_milestone($milestoneId) {
    // Check if milestone can be deleted
    $stmt = db_prepare("SELECT status FROM contract_milestones WHERE id = ?");
    $stmt->bind_param('i', $milestoneId);
    $stmt->execute();
    $result = $stmt->get_result();
    $milestone = $result->fetch_assoc();
    $stmt->close();

    if (!$milestone) {
        return ['success' => false, 'error' => 'Milestone not found'];
    }

    if ($milestone['status'] !== 'pending') {
        return ['success' => false, 'error' => 'Cannot delete milestone that has been started'];
    }

    $stmt = db_prepare("DELETE FROM contract_milestones WHERE id = ?");
    $stmt->bind_param('i', $milestoneId);

    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true];
    }

    $stmt->close();
    return ['success' => false, 'error' => 'Failed to delete milestone'];
}

/**
 * Get contract details with user verification
 */
function get_contract_with_verification($contractId, $userId, $userRole) {
    if ($userRole === 'client') {
        $stmt = db_prepare("
            SELECT c.*, pb.title as brief_title
            FROM contracts c
            JOIN project_briefs pb ON c.project_brief_id = pb.id
            JOIN client_profiles cp ON c.client_profile_id = cp.id
            WHERE c.id = ? AND cp.user_id = ?
        ");
    } else {
        $stmt = db_prepare("
            SELECT c.*, pb.title as brief_title
            FROM contracts c
            JOIN project_briefs pb ON c.project_brief_id = pb.id
            JOIN creator_profiles crp ON c.creator_profile_id = crp.id
            WHERE c.id = ? AND crp.user_id = ?
        ");
    }

    $stmt->bind_param('ii', $contractId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $contract = $result->fetch_assoc();
    $stmt->close();

    return $contract;
}
