<?php
/**
 * Ajax API: check if email already exists (live validation).
 * Used for Add/Edit Member and Add/Edit Trainer forms.
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

$pdo = getDBConnection();
$email = isset($_GET['email']) ? sanitizeInput($_GET['email']) : '';
$type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : 'member';
$excludeId = isset($_GET['exclude_id']) ? (int) $_GET['exclude_id'] : null;

if (!in_array($type, ['member', 'trainer'])) {
    $type = 'member';
}

$exists = false;
if ($email !== '') {
    $exists = emailExists($pdo, $email, $type, $excludeId > 0 ? $excludeId : null);
}

echo json_encode(['exists' => $exists]);
