<?php
/**
 * Add Task Page
 * Task Manager Application
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

// Require authentication
requireAuth();

$user = getCurrentUser();
$pageTitle = 'Add Task';
$errors = [];
$success = false;

$title = '';
$description = '';
$priority = 'Medium';
$status = 'Pending';
$dueDate = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'Medium';
    $status = $_POST['status'] ?? 'Pending';
    $dueDate = $_POST['due_date'] ?? '';
    
    // Validate
    if (empty($title)) {
        $errors[] = 'Task title is required';
    } elseif (strlen($title) > 200) {
        $errors[] = 'Title must be less than 200 characters';
    }
    
    if (!in_array($priority, ['Low', 'Medium', 'High'])) {
        $errors[] = 'Invalid priority';
    }
    
    if (!in_array($status, ['Pending', 'In Progress', 'Completed'])) {
        $errors[] = 'Invalid status';
    }
    
    if ($dueDate && !strtotime($dueDate)) {
        $errors[] = 'Invalid due date format';
    }
    
    if (empty($errors)) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            INSERT INTO tasks (user_id, title, description, priority, status, due_date) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $dueDateValue = $dueDate ? $dueDate : null;
        
        if ($stmt->execute([$user['id'], $title, $description, $priority, $status, $dueDateValue])) {
            $success = true;
            $_SESSION['flash_message'] = 'Task created successfully';
            $_SESSION['flash_type'] = 'success';
            header('Location: tasks.php');
            exit;
        } else {
            $errors[] = 'Failed to create task. Please try again.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Add New Task</h1>
    <a href="tasks.php" class="btn btn-secondary">Back to Tasks</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="flash-message error">
        <?php foreach ($errors as $error): ?>
            <div><?php echo escape($error); ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="" data-validate>
            <div class="form-group">
                <label class="form-label required" for="title">Task Title</label>
                <input type="text" 
                       class="form-input" 
                       id="title" 
                       name="title" 
                       value="<?php echo escape($title); ?>" 
                       required 
                       maxlength="200"
                       placeholder="Enter task title">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-textarea" 
                          id="description" 
                          name="description" 
                          placeholder="Enter task description (optional)"><?php echo escape($description); ?></textarea>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label class="form-label required" for="priority">Priority</label>
                    <select class="form-select" id="priority" name="priority" required>
                        <option value="Low" <?php echo $priority === 'Low' ? 'selected' : ''; ?>>Low</option>
                        <option value="Medium" <?php echo $priority === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="High" <?php echo $priority === 'High' ? 'selected' : ''; ?>>High</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label required" for="status">Status</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="Pending" <?php echo $status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="In Progress" <?php echo $status === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Completed" <?php echo $status === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="due_date">Due Date</label>
                <input type="date" 
                       class="form-input" 
                       id="due_date" 
                       name="due_date" 
                       value="<?php echo escape($dueDate); ?>"
                       placeholder="Select due date (optional)">
                <p class="form-help">Optional. Leave blank if no deadline.</p>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                <a href="tasks.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Task</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>