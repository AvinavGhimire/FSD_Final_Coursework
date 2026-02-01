<?php
/**
 * Add Member / Add Trainer — UI rendered via Twig (template engine).
 * Ajax: live email validation (check if exists) via main.js + check_email.php.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/twig.php';

$type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : 'member';
if (!in_array($type, ['member', 'trainer'])) {
    $type = 'member';
}

$pageTitle = $type === 'member' ? 'Add New Member' : 'Add New Trainer';
$pdo = getDBConnection();
$errors = [];
$success = false;

$form = [
    'first_name' => '', 'last_name' => '', 'email' => '', 'phone' => '',
    'membership_type' => '', 'membership_start_date' => '', 'membership_expiry_date' => '',
    'date_of_birth' => '', 'address' => '', 'emergency_contact_name' => '', 'emergency_contact_phone' => '',
    'specialization' => '', 'experience_years' => '', 'certification' => '', 'hire_date' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($type === 'member') {
        $form['first_name'] = sanitizeInput($_POST['first_name'] ?? '');
        $form['last_name'] = sanitizeInput($_POST['last_name'] ?? '');
        $form['email'] = sanitizeInput($_POST['email'] ?? '');
        $form['phone'] = sanitizeInput($_POST['phone'] ?? '');
        $form['membership_type'] = sanitizeInput($_POST['membership_type'] ?? '');
        $form['membership_start_date'] = sanitizeInput($_POST['membership_start_date'] ?? '');
        $form['membership_expiry_date'] = sanitizeInput($_POST['membership_expiry_date'] ?? '');
        $form['date_of_birth'] = sanitizeInput($_POST['date_of_birth'] ?? '');
        $form['address'] = sanitizeInput($_POST['address'] ?? '');
        $form['emergency_contact_name'] = sanitizeInput($_POST['emergency_contact_name'] ?? '');
        $form['emergency_contact_phone'] = sanitizeInput($_POST['emergency_contact_phone'] ?? '');

        if (empty($form['first_name'])) $errors[] = 'First name is required.';
        if (empty($form['last_name'])) $errors[] = 'Last name is required.';
        if (empty($form['email'])) {
            $errors[] = 'Email is required.';
        } elseif (!validateEmail($form['email'])) {
            $errors[] = 'Invalid email format.';
        }
        if (empty($form['phone'])) {
            $errors[] = 'Phone is required.';
        } elseif (!validatePhone($form['phone'])) {
            $errors[] = 'Invalid phone format.';
        }
        if (empty($form['membership_type'])) $errors[] = 'Membership type is required.';
        if (empty($form['membership_start_date'])) $errors[] = 'Membership start date is required.';
        if (empty($form['membership_expiry_date'])) $errors[] = 'Membership expiry date is required.';

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare('INSERT INTO members (first_name, last_name, email, phone, membership_type,
                    membership_start_date, membership_expiry_date, date_of_birth, address,
                    emergency_contact_name, emergency_contact_phone)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([
                    $form['first_name'], $form['last_name'], $form['email'], $form['phone'],
                    $form['membership_type'], $form['membership_start_date'], $form['membership_expiry_date'],
                    $form['date_of_birth'] ?: null, $form['address'] ?: null,
                    $form['emergency_contact_name'] ?: null, $form['emergency_contact_phone'] ?: null,
                ]);
                $success = true;
                $form = [
                    'first_name' => '', 'last_name' => '', 'email' => '', 'phone' => '',
                    'membership_type' => '', 'membership_start_date' => '', 'membership_expiry_date' => '',
                    'date_of_birth' => '', 'address' => '', 'emergency_contact_name' => '', 'emergency_contact_phone' => '',
                    'specialization' => '', 'experience_years' => '', 'certification' => '', 'hire_date' => '',
                ];
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $errors[] = 'Email already exists. Please use a different email.';
                } else {
                    $errors[] = 'Error adding member: ' . $e->getMessage();
                }
            }
        }
    } else {
        $form['first_name'] = sanitizeInput($_POST['first_name'] ?? '');
        $form['last_name'] = sanitizeInput($_POST['last_name'] ?? '');
        $form['email'] = sanitizeInput($_POST['email'] ?? '');
        $form['phone'] = sanitizeInput($_POST['phone'] ?? '');
        $form['specialization'] = sanitizeInput($_POST['specialization'] ?? '');
        $form['experience_years'] = sanitizeInput($_POST['experience_years'] ?? '');
        $form['certification'] = sanitizeInput($_POST['certification'] ?? '');
        $form['hire_date'] = sanitizeInput($_POST['hire_date'] ?? '');

        if (empty($form['first_name'])) $errors[] = 'First name is required.';
        if (empty($form['last_name'])) $errors[] = 'Last name is required.';
        if (empty($form['email'])) {
            $errors[] = 'Email is required.';
        } elseif (!validateEmail($form['email'])) {
            $errors[] = 'Invalid email format.';
        }
        if (empty($form['phone'])) {
            $errors[] = 'Phone is required.';
        } elseif (!validatePhone($form['phone'])) {
            $errors[] = 'Invalid phone format.';
        }
        if (empty($form['hire_date'])) $errors[] = 'Hire date is required.';

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare('INSERT INTO trainers (first_name, last_name, email, phone, specialization,
                    experience_years, certification, hire_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([
                    $form['first_name'], $form['last_name'], $form['email'], $form['phone'],
                    $form['specialization'] ?: null, $form['experience_years'] ?: null,
                    $form['certification'] ?: null, $form['hire_date'],
                ]);
                $success = true;
                $form = [
                    'first_name' => '', 'last_name' => '', 'email' => '', 'phone' => '',
                    'membership_type' => '', 'membership_start_date' => '', 'membership_expiry_date' => '',
                    'date_of_birth' => '', 'address' => '', 'emergency_contact_name' => '', 'emergency_contact_phone' => '',
                    'specialization' => '', 'experience_years' => '', 'certification' => '', 'hire_date' => '',
                ];
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $errors[] = 'Email already exists. Please use a different email.';
                } else {
                    $errors[] = 'Error adding trainer: ' . $e->getMessage();
                }
            }
        }
    }
}

$data = array_merge($form, [
    'pageTitle' => $pageTitle,
    'type' => $type,
    'errors' => $errors,
    'success' => $success,
]);
render('add.twig', $data);
