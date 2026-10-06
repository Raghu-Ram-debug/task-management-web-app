<?php
/**
 * Delete Task Page
 * Task Manager Application
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

// Require authentication
requireAuth();

$user = getCurrentUser();

$taskId = (int)($_GET['id'] ?? 0);

if (!$taskId) {
    $_SESSION['flash_message'] = 'Invalid task ID';
    $_SESSION['flash_type'] = 'error';
    header('Location: tasks.php');
    exit;
}

$pdo = getDBConnection();

// Verify task belongs to user
$stmt = $pdo->prepare("SELECT id, title FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$taskId, $user['id']]);
$task = $stmt->fetch();

if (!$task) {
    $_SESSION['flash_message'] = 'Task not found or access denied';
    $_SESSION['flash_type'] = 'error';
    header('Location: tasks.php');
    exit;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['confirm'])) {
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    
    if ($stmt->execute([$taskId, $user['id']])) {
        $_SESSION['flash_message'] = 'Task deleted successfully';
        $_SESSION['flash_type'] = 'success';
    } else {
        $_SESSION['flash_message'] = 'Failed to delete task';
        $_SESSION['flash_type'] = 'error';
    }
    
    header('Location: tasks.php');
    exit;
}

$pageTitle = 'Delete Task';

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Delete Task</h1>
    <a href="tasks.php" class="btn btn-secondary">Cancel</a>
</div>

<div class="card" style="max-width: 500px;">
    <div class="card-body" style="text-align: center; padding: 3rem 2rem;">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--danger-color)" stroke-width="1.5" style="margin-bottom: 1.5rem;">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>
        
        <h2 style="margin-bottom: 1rem;">Delete Task?</h2>
        
        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">
            Are you sure you want to delete <strong>"<?php echo escape($task['title']); ?>"</strong>?
            This action cannot be undone.
        </p>
        
        <form method="POST" action="">
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <a href="tasks.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" name="confirm" value="1" class="btn btn-danger">Delete Task</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>