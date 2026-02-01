<?php
/**
 * Search Members — UI rendered via Twig (template engine).
 * Ajax: autocomplete search bar + load results without page refresh (Fetch API) via main.js.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/twig.php';

$pageTitle = 'Search Members';
$pdo = getDBConnection();
$members = [];
$membershipType = '';
$expiryDate = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $membershipType = isset($_GET['membership_type']) ? sanitizeInput($_GET['membership_type']) : '';
    $expiryDate = isset($_GET['expiry_date']) ? sanitizeInput($_GET['expiry_date']) : '';
    if ($membershipType !== '' || $expiryDate !== '') {
        $members = searchMembers($pdo, $membershipType ?: null, $expiryDate ?: null);
    }
}

render('search.twig', [
    'pageTitle' => $pageTitle,
    'members' => $members,
    'membership_type' => $membershipType,
    'expiry_date' => $expiryDate,
]);
