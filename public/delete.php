<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

$type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : 'member';
if (!in_array($type, ['member', 'trainer'])) {
    $type = 'member';
}

$pageTitle = $type === 'member' ? 'Delete Member' : 'Delete Trainer';
$pdo = getDBConnection();

// Get ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

// Get existing data
if ($type === 'member') {
    $item = getMemberById($pdo, $id);
    $table = 'members';
    $name = $item ? ($item['first_name'] . ' ' . $item['last_name']) : '';
} else {
    $item = getTrainerById($pdo, $id);
    $table = 'trainers';
    $name = $item ? ($item['first_name'] . ' ' . $item['last_name']) : '';
}

if (!$item) {
    header('Location: index.php');
    exit;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM {$table} WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: index.php?deleted=1');
        exit;
    } catch (PDOException $e) {
        $error = "Error deleting " . $type . ": " . $e->getMessage();
    }
}

require_once '../includes/header.php';
?>

<div class="page-header">
    <h2>Delete <?php echo ucfirst($type); ?></h2>
    <a href="index.php" class="btn btn-secondary">Back to List</a>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-error">
        <p><?php echo escape($error); ?></p>
    </div>
<?php endif; ?>

<div class="delete-confirmation">
    <div class="alert alert-warning">
        <h3>Are you sure you want to delete this <?php echo escape($type); ?>?</h3>
        <p>This action cannot be undone.</p>
    </div>
    
    <div class="book-details-card">
        <h3><?php echo escape($name); ?></h3>
        <?php if ($type === 'member'): ?>
            <p><strong>Email:</strong> <?php echo escape($item['email']); ?></p>
            <p><strong>Membership Type:</strong> <?php echo escape($item['membership_type']); ?></p>
            <p><strong>Status:</strong> <?php echo escape($item['status']); ?></p>
        <?php else: ?>
            <p><strong>Email:</strong> <?php echo escape($item['email']); ?></p>
            <p><strong>Specialization:</strong> <?php echo escape($item['specialization'] ?? 'N/A'); ?></p>
            <p><strong>Status:</strong> <?php echo escape($item['status']); ?></p>
        <?php endif; ?>
    </div>
    
    <form method="POST" action="delete.php?type=<?php echo escape($type); ?>&id=<?php echo escape($id); ?>" class="delete-form">
        <div class="form-actions">
            <button type="submit" name="confirm_delete" class="btn btn-delete">Yes, Delete <?php echo ucfirst($type); ?></button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
