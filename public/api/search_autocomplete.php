<?php
/**
 * Ajax API endpoint for membership validation
 * Returns JSON results for membership validation
 */

require_once '../../config/db.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

$pdo = getDBConnection();
$memberId = isset($_GET['member_id']) ? (int)$_GET['member_id'] : 0;

if ($memberId > 0) {
    $result = validateMembership($pdo, $memberId);
    echo json_encode($result);
} else {
    echo json_encode(['valid' => false, 'message' => 'Invalid member ID']);
}
?>
