<?php
/**
 * Utility Functions for Fitness Club Management System
 */

/**
 * Escape output to prevent XSS attacks
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate and sanitize input
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate phone number (basic validation)
 */
function validatePhone($phone) {
    // Remove common formatting characters
    $phone = preg_replace('/[-\s()]/', '', $phone);
    // Check if it contains at least 10 digits
    return preg_match('/^\d{10,}$/', $phone);
}

/**
 * Format date for display
 */
function formatDate($date) {
    if (empty($date) || $date === '0000-00-00') {
        return 'N/A';
    }
    return date('M d, Y', strtotime($date));
}

/**
 * Check if membership is expired
 */
function isMembershipExpired($expiryDate) {
    if (empty($expiryDate) || $expiryDate === '0000-00-00') {
        return true;
    }
    return strtotime($expiryDate) < strtotime('today');
}

/**
 * Get all members from database
 */
function getAllMembers($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM members ORDER BY id DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get member by ID
 */
function getMemberById($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Search members by name (first, last, or email) - for autocomplete / Ajax search
 */
function searchMembersByName($pdo, $query, $limit = 20) {
    if (empty(trim($query))) {
        return [];
    }
    try {
        $q = '%' . trim($query) . '%';
        $stmt = $pdo->prepare(
            "SELECT * FROM members 
             WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? 
             OR CONCAT(first_name, ' ', last_name) LIKE ? OR CONCAT(last_name, ' ', first_name) LIKE ?
             ORDER BY last_name ASC, first_name ASC 
             LIMIT " . (int) $limit
        );
        $stmt->execute([$q, $q, $q, $q, $q]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Check if email exists in members or trainers (for live validation).
 * $excludeId excludes that ID when checking (for edit forms).
 */
function emailExists($pdo, $email, $type, $excludeId = null) {
    $email = trim($email);
    if ($email === '') {
        return false;
    }
    $table = $type === 'trainer' ? 'trainers' : 'members';
    try {
        if ($excludeId !== null && $excludeId > 0) {
            $stmt = $pdo->prepare("SELECT 1 FROM {$table} WHERE email = ? AND id != ?");
            $stmt->execute([$email, (int) $excludeId]);
        } else {
            $stmt = $pdo->prepare("SELECT 1 FROM {$table} WHERE email = ?");
            $stmt->execute([$email]);
        }
        return (bool) $stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Search members by membership type and expiry date
 */
function searchMembers($pdo, $membershipType = null, $expiryDate = null) {
    try {
        $conditions = [];
        $params = [];
        
        if (!empty($membershipType)) {
            $conditions[] = "membership_type = ?";
            $params[] = $membershipType;
        }
        
        if (!empty($expiryDate)) {
            $conditions[] = "membership_expiry_date = ?";
            $params[] = $expiryDate;
        }
        
        $sql = "SELECT * FROM members";
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY membership_expiry_date ASC, last_name ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Validate membership (check if active and not expired)
 */
function validateMembership($pdo, $memberId) {
    try {
        $stmt = $pdo->prepare("SELECT id, membership_expiry_date, status FROM members WHERE id = ?");
        $stmt->execute([$memberId]);
        $member = $stmt->fetch();
        
        if (!$member) {
            return ['valid' => false, 'message' => 'Member not found'];
        }
        
        if ($member['status'] !== 'Active') {
            return ['valid' => false, 'message' => 'Membership is not active'];
        }
        
        if (isMembershipExpired($member['membership_expiry_date'])) {
            return ['valid' => false, 'message' => 'Membership has expired'];
        }
        
        $daysUntilExpiry = floor((strtotime($member['membership_expiry_date']) - strtotime('today')) / 86400);
        
        return [
            'valid' => true,
            'message' => 'Membership is valid',
            'expiry_date' => $member['membership_expiry_date'],
            'days_until_expiry' => $daysUntilExpiry
        ];
    } catch (PDOException $e) {
        return ['valid' => false, 'message' => 'Error validating membership'];
    }
}

/**
 * Get all trainers from database
 */
function getAllTrainers($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM trainers ORDER BY id DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get trainer by ID
 */
function getTrainerById($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM trainers WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Get active trainers only
 */
function getActiveTrainers($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM trainers WHERE status = 'Active' ORDER BY last_name ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get all workout plans
 */
function getAllWorkoutPlans($pdo) {
    try {
        $stmt = $pdo->query("SELECT wp.*, 
                            m.first_name as member_first_name, 
                            m.last_name as member_last_name,
                            t.first_name as trainer_first_name,
                            t.last_name as trainer_last_name
                            FROM workout_plans wp
                            LEFT JOIN members m ON wp.member_id = m.id
                            LEFT JOIN trainers t ON wp.trainer_id = t.id
                            ORDER BY wp.id DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get workout plan by ID
 */
function getWorkoutPlanById($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT wp.*, 
                            m.first_name as member_first_name, 
                            m.last_name as member_last_name,
                            t.first_name as trainer_first_name,
                            t.last_name as trainer_last_name
                            FROM workout_plans wp
                            LEFT JOIN members m ON wp.member_id = m.id
                            LEFT JOIN trainers t ON wp.trainer_id = t.id
                            WHERE wp.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Get workout plans for a specific member
 */
function getWorkoutPlansByMember($pdo, $memberId) {
    try {
        $stmt = $pdo->prepare("SELECT wp.*, 
                            t.first_name as trainer_first_name,
                            t.last_name as trainer_last_name
                            FROM workout_plans wp
                            LEFT JOIN trainers t ON wp.trainer_id = t.id
                            WHERE wp.member_id = ?
                            ORDER BY wp.start_date DESC");
        $stmt->execute([$memberId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}
?>
