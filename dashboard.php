<?php
/**
 * Dashboard Page
 * Task Manager Application
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

// Require authentication
requireAuth();

$user = getCurrentUser();
$pageTitle = 'Dashboard';

// Get task statistics
$pdo = getDBConnection();
$userId = $user['id'];

// Total tasks
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tasks WHERE user_id = ?");
$stmt->execute([$userId]);
$totalTasks = $stmt->fetch()['count'];

// Pending tasks
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tasks WHERE user_id = ? AND status = 'Pending'");
$stmt->execute([$userId]);
$pendingTasks = $stmt->fetch()['count'];

// In Progress tasks
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tasks WHERE user_id = ? AND status = 'In Progress'");
$stmt->execute([$userId]);
$inProgressTasks = $stmt->fetch()['count'];

// Completed tasks
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tasks WHERE user_id = ? AND status = 'Completed'");
$stmt->execute([$userId]);
$completedTasks = $stmt->fetch()['count'];

// Overdue tasks
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tasks WHERE user_id = ? AND status != 'Completed' AND due_date IS NOT NULL AND due_date < CURDATE()");
$stmt->execute([$userId]);
$overdueTasks = $stmt->fetch()['count'];

// Recent tasks (last 5)
$stmt = $pdo->prepare("
    SELECT * FROM tasks 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute([$userId]);
$recentTasks = $stmt->fetchAll();

// Upcoming due tasks (next 7 days, not completed)
$stmt = $pdo->prepare("
    SELECT * FROM tasks 
    WHERE user_id = ? 
    AND status != 'Completed' 
    AND due_date IS NOT NULL 
    AND due_date >= CURDATE() 
    AND due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ORDER BY due_date ASC
    LIMIT 5
");
$stmt->execute([$userId]);
$upcomingTasks = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Dashboard</h1>
    <a href="add_task.php" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Add Task
    </a>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon total">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 11l3 3L22 4"></path>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo $totalTasks; ?></div>
            <div class="stat-label">Total Tasks</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon pending">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo $pendingTasks; ?></div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon in-progress">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M12 6v6l4 2"></path>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo $inProgressTasks; ?></div>
            <div class="stat-label">In Progress</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon completed">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo $completedTasks; ?></div>
            <div class="stat-label">Completed</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon overdue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="6" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo $overdueTasks; ?></div>
            <div class="stat-label">Overdue</div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem;">
    <!-- Recent Tasks -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Tasks</h3>
            <a href="tasks.php" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="card-body">
            <?php if (empty($recentTasks)): ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M9 11l3 3L22 4"></path>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                    </svg>
                    <h3>No tasks yet</h3>
                    <p>Create your first task to get started</p>
                    <a href="add_task.php" class="btn btn-primary">Add Task</a>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Due Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentTasks as $task): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo escape($task['title']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo getPriorityClass($task['priority']); ?>">
                                            <?php echo escape($task['priority']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo getStatusClass($task['status']); ?>">
                                            <?php echo escape($task['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $dueDate = formatDate($task['due_date']);
                                        $isOverdue = isOverdue($task['due_date'], $task['status']);
                                        echo $isOverdue ? '<span style="color: var(--danger-color); font-weight: 500;">' . escape($dueDate) . ' (Overdue)</span>' : escape($dueDate);
                                        ?>
                                    </td>
                                    <td>
                                        <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="action-btn" data-tooltip="Edit">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Upcoming Due Tasks -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Upcoming (Next 7 Days)</h3>
            <a href="tasks.php?filter=upcoming" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="card-body">
            <?php if (empty($upcomingTasks)): ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <h3>No upcoming tasks</h3>
                    <p>You're all caught up for the next week</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Priority</th>
                                <th>Due Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingTasks as $task): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo escape($task['title']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo getPriorityClass($task['priority']); ?>">
                                            <?php echo escape($task['priority']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo formatDate($task['due_date']); ?>
                                    </td>
                                    <td>
                                        <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="action-btn" data-tooltip="Edit">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>