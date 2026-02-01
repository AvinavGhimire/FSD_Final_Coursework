<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

$pageTitle = 'Workout Plans';
$pdo = getDBConnection();
$workoutPlans = getAllWorkoutPlans($pdo);
$members = getAllMembers($pdo);
$trainers = getActiveTrainers($pdo);
$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $member_id = (int)($_POST['member_id'] ?? 0);
        $trainer_id = !empty($_POST['trainer_id']) ? (int)$_POST['trainer_id'] : null;
        $plan_name = sanitizeInput($_POST['plan_name'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $duration_weeks = !empty($_POST['duration_weeks']) ? (int)$_POST['duration_weeks'] : null;
        $start_date = sanitizeInput($_POST['start_date'] ?? '');
        $end_date = !empty($_POST['end_date']) ? sanitizeInput($_POST['end_date']) : null;
        
        if (empty($member_id)) {
            $errors[] = "Member is required.";
        }
        if (empty($plan_name)) {
            $errors[] = "Plan name is required.";
        }
        if (empty($start_date)) {
            $errors[] = "Start date is required.";
        }
        
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO workout_plans (member_id, trainer_id, plan_name, description, duration_weeks, start_date, end_date) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$member_id, $trainer_id, $plan_name, $description ?: null, $duration_weeks, $start_date, $end_date]);
                $success = true;
                $workoutPlans = getAllWorkoutPlans($pdo);
            } catch (PDOException $e) {
                $errors[] = "Error adding workout plan: " . $e->getMessage();
            }
        }
    }
}
?>

<div class="page-header">
    <h2>Workout Plans</h2>
    <button onclick="document.getElementById('add-plan-form').style.display='block'" class="btn btn-primary">Add New Workout Plan</button>
</div>

<?php if ($success): ?>
    <div class="alert alert-success">
        <p>Workout plan added successfully!</p>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo escape($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Add Workout Plan Form (initially hidden) -->
<div id="add-plan-form" style="display:none; margin-bottom: 30px;">
    <div class="book-card">
        <h3>Add New Workout Plan</h3>
        <form method="POST" action="workout_plans.php" class="book-form">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label for="member_id">Member <span class="required">*</span></label>
                <select id="member_id" name="member_id" required>
                    <option value="">Select a member</option>
                    <?php foreach ($members as $member): ?>
                        <option value="<?php echo escape($member['id']); ?>">
                            <?php echo escape($member['first_name'] . ' ' . $member['last_name'] . ' (' . $member['membership_type'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="trainer_id">Trainer</label>
                <select id="trainer_id" name="trainer_id">
                    <option value="">No trainer assigned</option>
                    <?php foreach ($trainers as $trainer): ?>
                        <option value="<?php echo escape($trainer['id']); ?>">
                            <?php echo escape($trainer['first_name'] . ' ' . $trainer['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="plan_name">Plan Name <span class="required">*</span></label>
                <input type="text" id="plan_name" name="plan_name" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"></textarea>
            </div>
            
            <div class="form-group">
                <label for="duration_weeks">Duration (Weeks)</label>
                <input type="number" id="duration_weeks" name="duration_weeks" min="1">
            </div>
            
            <div class="form-group">
                <label for="start_date">Start Date <span class="required">*</span></label>
                <input type="date" id="start_date" name="start_date" required>
            </div>
            
            <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="date" id="end_date" name="end_date">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Workout Plan</button>
                <button type="button" onclick="document.getElementById('add-plan-form').style.display='none'" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<?php if (empty($workoutPlans)): ?>
    <div class="alert alert-info">
        <p>No workout plans found. Add your first workout plan above!</p>
    </div>
<?php else: ?>
    <div class="books-grid">
        <?php foreach ($workoutPlans as $plan): ?>
            <div class="book-card">
                <div class="book-header">
                    <h3><?php echo escape($plan['plan_name']); ?></h3>
                    <span class="book-id">#<?php echo escape($plan['id']); ?></span>
                </div>
                <div class="book-details">
                    <p><strong>Member:</strong> <?php echo escape($plan['member_first_name'] . ' ' . $plan['member_last_name']); ?></p>
                    <?php if ($plan['trainer_first_name']): ?>
                        <p><strong>Trainer:</strong> <?php echo escape($plan['trainer_first_name'] . ' ' . $plan['trainer_last_name']); ?></p>
                    <?php else: ?>
                        <p><strong>Trainer:</strong> <em>Not assigned</em></p>
                    <?php endif; ?>
                    <?php if ($plan['duration_weeks']): ?>
                        <p><strong>Duration:</strong> <?php echo escape($plan['duration_weeks']); ?> weeks</p>
                    <?php endif; ?>
                    <p><strong>Start Date:</strong> <?php echo formatDate($plan['start_date']); ?></p>
                    <?php if ($plan['end_date']): ?>
                        <p><strong>End Date:</strong> <?php echo formatDate($plan['end_date']); ?></p>
                    <?php endif; ?>
                    <p><strong>Status:</strong> 
                        <span class="badge <?php echo strtolower($plan['status']); ?>">
                            <?php echo escape($plan['status']); ?>
                        </span>
                    </p>
                    <?php if (!empty($plan['description'])): ?>
                        <p><strong>Description:</strong> <?php echo escape(substr($plan['description'], 0, 100)); ?>...</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
