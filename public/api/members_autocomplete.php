<?php
/**
 * Ajax API: member search by name (autocomplete + load results without page refresh).
 * Uses Fetch API from frontend.
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

$pdo = getDBConnection();
$q = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';

$members = searchMembersByName($pdo, $q, 25);
echo json_encode(['members' => $members]);
