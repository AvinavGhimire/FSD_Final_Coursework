<?php
/**
 * Edit Member / Edit Trainer — UI rendered via Twig (template engine).
 * Ajax: live email validation (check if exists) via main.js + check_email.php.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/twig.php';

$type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : 'member';
if (!in_array($type, ['member', 'trainer'])) {
    $type = 'member';
}

$pageTitle = $type === 'member' ? 'Edit Member' : 'Edit Trainer';
$pdo = getDBConnection();
$errors = [];
$success = false;
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

if ($type === 'member') {
    $item = getMemberById($pdo, $id);
} else {
    $item = getTrainerById($pdo, $id);
}

if (!$item) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($type === 'member') {
        $first_name = sanitizeInput($_POST['first_name'] ?? '');
        $last_name = sanitizeInput($_POST['last_name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $membership_type = sanitizeInput($_POST['membership_type'] ?? '');
        $membership_start_date = sanitizeInput($_POST['membership_start_date'] ?? '');
        $membership_expiry_date = sanitizeInput($_POST['membership_expiry_date'] ?? '');
        $date_of_birth = sanitizeInput($_POST['date_of_birth'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        $emergency_contact_name = sanitizeInput($_POST['emergency_contact_name'] ?? '');
        $emergency_contact_phone = sanitizeInput($_POST['emergency_contact_phone'] ?? '');
        $status = sanitizeInput($_POST['status'] ?? 'Active');

        if (empty($first_name)) $errors[] = 'First name is required.';
        if (empty($last_name)) $errors[] = 'Last name is required.';
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!validateEmail($email)) {
            $errors[] = 'Invalid email format.';
        }
        if (empty($phone)) {
            $errors[] = 'Phone is required.';
        } elseif (!validatePhone($phone)) {
            $errors[] = 'Invalid phone format.';
        }
        if (empty($membership_type)) $errors[] = 'Membership type is required.';
        if (empty($membership_start_date)) $errors[] = 'Membership start date is required.';
        if (empty($membership_expiry_date)) $errors[] = 'Membership expiry date is required.';

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare('UPDATE members SET first_name = ?, last_name = ?, email = ?, phone = ?,
                    membership_type = ?, membership_start_date = ?, membership_expiry_date = ?,
                    date_of_birth = ?, address = ?, emergency_contact_name = ?,
                    emergency_contact_phone = ?, status = ? WHERE id = ?');
                $stmt->execute([
                    $first_name, $last_name, $email, $phone, $membership_type,
                    $membership_start_date, $membership_expiry_date,
                    $date_of_birth ?: null, $address ?: null,
                    $emergency_contact_name ?: null, $emergency_contact_phone ?: null,
                    $status, $id,
                ]);
                $success = true;
                $item = getMemberById($pdo, $id);
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $errors[] = 'Email already exists. Please use a different email.';
                } else {
                    $errors[] = 'Error updating member: ' . $e->getMessage();
                }
            }
        }
    } else {
        $first_name = sanitizeInput($_POST['first_name'] ?? '');
        $last_name = sanitizeInput($_POST['last_name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $specialization = sanitizeInput($_POST['specialization'] ?? '');
        $experience_years = sanitizeInput($_POST['experience_years'] ?? '');
        $certification = sanitizeInput($_POST['certification'] ?? '');
        $hire_date = sanitizeInput($_POST['hire_date'] ?? '');
        $status = sanitizeInput($_POST['status'] ?? 'Active');

        if (empty($first_name)) $errors[] = 'First name is required.';
        if (empty($last_name)) $errors[] = 'Last name is required.';
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!validateEmail($email)) {
            $errors[] = 'Invalid email format.';
        }
        if (empty($phone)) {
            $errors[] = 'Phone is required.';
        } elseif (!validatePhone($phone)) {
            $errors[] = 'Invalid phone format.';
        }
        if (empty($hire_date)) $errors[] = 'Hire date is required.';

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare('UPDATE trainers SET first_name = ?, last_name = ?, email = ?, phone = ?,
                    specialization = ?, experience_years = ?, certification = ?, hire_date = ?, status = ?
                    WHERE id = ?');
                $stmt->execute([
                    $first_name, $last_name, $email, $phone, $specialization ?: null,
                    $experience_years ?: null, $certification ?: null, $hire_date, $status, $id,
                ]);
                $success = true;
                $item = getTrainerById($pdo, $id);
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $errors[] = 'Email already exists. Please use a different email.';
                } else {
                    $errors[] = 'Error updating trainer: ' . $e->getMessage();
                }
            }
        }
    }
}

render('edit.twig', [
    'pageTitle' => $pageTitle,
    'type' => $type,
    'id' => $id,
    'item' => $item,
    'errors' => $errors,
    'success' => $success,
]);
