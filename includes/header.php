<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? escape($pageTitle) : 'Fitness Club Management System'; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <h1 class="logo">💪 Fitness Club Management</h1>
                <ul class="nav-menu">
                    <li><a href="index.php">Members</a></li>
                    <li><a href="add.php?type=member">Add Member</a></li>
                    <li><a href="add.php?type=trainer">Add Trainer</a></li>
                    <li><a href="workout_plans.php">Workout Plans</a></li>
                    <li><a href="search.php">Search</a></li>
                </ul>
            </div>
        </nav>
    </header>
    <main class="container">
