<?php
/**
 * Tasks Page - View and manage all tasks
 * Task Manager Application
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

// Require authentication
requireAuth();

$user = getCurrentUser();
$pageTitle = 'My Tasks';
$userId = $user['id'];

$pdo = getDBConnection();

// Handle complete action (must be before any output)
if (isset($_GET['action']) && $_GET['action'] === 'complete' && isset($_GET['id'])) {
    $taskId = (int)$_GET['id'];
    $redirect = $_GET['redirect'] ?? 'tasks.php';
    
    $stmt = $pdo->prepare("UPDATE tasks SET status = 'Completed', updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$taskId, $userId])) {
        $_SESSION['flash_message'] = 'Task marked as completed';
        $_SESSION['flash_type'] = 'success';
    } else {
        $_SESSION['flash_message'] = 'Failed to update task';
        $_SESSION['flash_type'] = 'error';
    }
    header('Location: ' . $redirect);
    exit;
}

// Build query with filters
$whereConditions = ["user_id = ?"];
$params = [$userId];

// Status filter
$statusFilter = $_GET['status'] ?? '';
if ($statusFilter && in_array($statusFilter, ['Pending', 'In Progress', 'Completed'])) {
    $whereConditions[] = "status = ?";
    $params[] = $statusFilter;
}

// Priority filter
$priorityFilter = $_GET['priority'] ?? '';
if ($priorityFilter && in_array($priorityFilter, ['Low', 'Medium', 'High'])) {
    $whereConditions[] = "priority = ?";
    $params[] = $priorityFilter;
}

// Search
$search = trim($_GET['search'] ?? '');
if ($search) {
    $whereConditions[] = "(title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Overdue filter
$overdueFilter = $_GET['overdue'] ?? '';
if ($overdueFilter === '1') {
    $whereConditions[] = "status != 'Completed' AND due_date IS NOT NULL AND due_date < CURDATE()";
}

// Sort
$sort = $_GET['sort'] ?? 'created_desc';
$orderBy = 'created_at DESC';
switch ($sort) {
    case 'created_asc':
        $orderBy = 'created_at ASC';
        break;
    case 'due_asc':
        $orderBy = 'due_date ASC NULLS LAST';
        break;
    case 'due_desc':
        $orderBy = 'due_date DESC NULLS LAST';
        break;
    case 'priority_desc':
        $orderBy = "FIELD(priority, 'High', 'Medium', 'Low') DESC, created_at DESC";
        break;
    case 'priority_asc':
        $orderBy = "FIELD(priority, 'Low', 'Medium', 'High') ASC, created_at DESC";
        break;
    default:
        $orderBy = 'created_at DESC';
}

$whereClause = implode(' AND ', $whereConditions);

// Get total count for pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) as count FROM tasks WHERE $whereClause");
$countStmt->execute($params);
$totalTasks = $countStmt->fetch()['count'];

// Pagination
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;
$totalPages = ceil($totalTasks / $perPage);

// Get tasks
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE $whereClause ORDER BY $orderBy LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$tasks = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>My Tasks</h1>
    <a href="add_task.php" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Add Task
    </a>
</div>

<!-- Filters -->
<div class="filters-bar" id="taskFilterForm">
    <div class="search-box">
        <input type="text" 
               id="taskSearch" 
               name="search" 
               placeholder="Search tasks..." 
               value="<?php echo escape($search); ?>">
    </div>
    
    <div class="filter-group">
        <label for="statusFilter">Status</label>
        <select id="statusFilter" name="status">
            <option value="">All Status</option>
            <option value="Pending" <?php echo $statusFilter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="In Progress" <?php echo $statusFilter === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
            <option value="Completed" <?php echo $statusFilter === 'Completed' ? 'selected' : ''; ?>>Completed</option>
        </select>
    </div>
    
    <div class="filter-group">
        <label for="priorityFilter">Priority</label>
        <select id="priorityFilter" name="priority">
            <option value="">All Priorities</option>
            <option value="High" <?php echo $priorityFilter === 'High' ? 'selected' : ''; ?>>High</option>
            <option value="Medium" <?php echo $priorityFilter === 'Medium' ? 'selected' : ''; ?>>Medium</option>
            <option value="Low" <?php echo $priorityFilter === 'Low' ? 'selected' : ''; ?>>Low</option>
        </select>
    </div>
    
    <div class="filter-group">
        <label for="sortFilter">Sort By</label>
        <select id="sortFilter" name="sort">
            <option value="created_desc" <?php echo $sort === 'created_desc' ? 'selected' : ''; ?>>Newest First</option>
            <option value="created_asc" <?php echo $sort === 'created_asc' ? 'selected' : ''; ?>>Oldest First</option>
            <option value="due_asc" <?php echo $sort === 'due_asc' ? 'selected' : ''; ?>>Due Date (Ascending)</option>
            <option value="due_desc" <?php echo $sort === 'due_desc' ? 'selected' : ''; ?>>Due Date (Descending)</option>
            <option value="priority_desc" <?php echo $sort === 'priority_desc' ? 'selected' : ''; ?>>Priority (High to Low)</option>
            <option value="priority_asc" <?php echo $sort === 'priority_asc' ? 'selected' : ''; ?>>Priority (Low to High)</option>
        </select>
    </div>
    
    <a href="tasks.php" class="btn btn-secondary">Clear Filters</a>
</div>

<!-- Tasks Table (Desktop) -->
<div class="card table-container" style="display: block;">
    <?php if (empty($tasks)): ?>
        <div class="card-body">
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M9 11l3 3L22 4"></path>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                </svg>
                <h3>No tasks found</h3>
                <p><?php echo $search || $statusFilter || $priorityFilter ? 'Try adjusting your filters' : 'Create your first task to get started'; ?></p>
                <?php if ($search || $statusFilter || $priorityFilter): ?>
                    <a href="tasks.php" class="btn btn-secondary">Clear Filters</a>
                <?php else: ?>
                    <a href="add_task.php" class="btn btn-primary">Add Task</a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="card-body" style="padding: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td>
                                <strong><?php echo escape($task['title']); ?></strong>
                            </td>
                            <td>
                                <?php 
                                $desc = $task['description'] ?? '';
                                echo $desc ? escape(mb_strimwidth($desc, 0, 100, '...')) : '<span style="color: var(--text-muted);">No description</span>';
                                ?>
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
                                if ($isOverdue) {
                                    echo '<span style="color: var(--danger-color); font-weight: 500;">' . escape($dueDate) . ' <small>(Overdue)</small></span>';
                                } else {
                                    echo escape($dueDate);
                                }
                                ?>
                            </td>
                            <td><?php echo formatDate($task['created_at']); ?></td>
                            <td>
                                <div style="display: flex; gap: 0.25rem;">
                                    <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="action-btn" data-tooltip="Edit">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </a>
                                    
                                    <?php if ($task['status'] !== 'Completed'): ?>
                                        <a href="tasks.php?action=complete&id=<?php echo $task['id']; ?>&redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                                           class="action-btn" data-tooltip="Mark Complete">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="delete_task.php?id=<?php echo $task['id']; ?>" 
                                       class="action-btn delete" 
                                       data-confirm-delete
                                       data-confirm-message="Are you sure you want to delete this task?"
                                       data-tooltip="Delete">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="card-body" style="padding: 1rem 1.5rem; border-top: 1px solid var(--border-color);">
                <nav style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <?php
                    $baseParams = $_GET;
                    unset($baseParams['page']);
                    $baseQuery = http_build_query($baseParams);
                    $baseUrl = 'tasks.php' . ($baseQuery ? '?' . $baseQuery : '');
                    ?>
                    
                    <?php if ($page > 1): ?>
                        <a href="<?php echo $baseUrl . ($baseQuery ? '&' : '?') . 'page=' . ($page - 1); ?>" class="btn btn-sm btn-outline">Previous</a>
                    <?php endif; ?>
                    
                    <span style="padding: 0 1rem; color: var(--text-muted);">
                        Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                    </span>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo $baseUrl . ($baseQuery ? '&' : '?') . 'page=' . ($page + 1); ?>" class="btn btn-sm btn-outline">Next</a>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Task Cards (Mobile) -->
<div class="task-cards-container">
    <?php foreach ($tasks as $task): ?>
        <div class="task-card">
            <div class="task-card-header">
                <h4 class="task-card-title"><?php echo escape($task['title']); ?></h4>
                <div class="task-card-badges">
                    <span class="badge <?php echo getPriorityClass($task['priority']); ?>">
                        <?php echo escape($task['priority']); ?>
                    </span>
                    <span class="badge <?php echo getStatusClass($task['status']); ?>">
                        <?php echo escape($task['status']); ?>
                    </span>
                </div>
            </div>
            
            <?php if ($task['description']): ?>
                <p class="task-card-description"><?php echo escape($task['description']); ?></p>
            <?php endif; ?>
            
            <div class="task-card-meta">
                <?php if ($task['due_date']): ?>
                    <span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <?php 
                        $dueDate = formatDate($task['due_date']);
                        $isOverdue = isOverdue($task['due_date'], $task['status']);
                        echo $isOverdue ? '<strong style="color: var(--danger-color);">' . escape($dueDate) . ' (Overdue)</strong>' : escape($dueDate);
                        ?>
                    </span>
                <?php endif; ?>
                <span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    Created <?php echo formatDate($task['created_at']); ?>
                </span>
            </div>
            
            <div class="task-card-actions">
                <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="btn btn-sm btn-outline">Edit</a>
                
                <?php if ($task['status'] !== 'Completed'): ?>
                    <a href="tasks.php?action=complete&id=<?php echo $task['id']; ?>&redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-sm btn-success">Complete</a>
                <?php endif; ?>
                
                <a href="delete_task.php?id=<?php echo $task['id']; ?>" 
                   class="btn btn-sm btn-danger" 
                   data-confirm-delete
                   data-confirm-message="Are you sure you want to delete this task?">Delete</a>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if (empty($tasks)): ?>
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M9 11l3 3L22 4"></path>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
            </svg>
            <h3>No tasks found</h3>
            <p><?php echo $search || $statusFilter || $priorityFilter ? 'Try adjusting your filters' : 'Create your first task to get started'; ?></p>
            <?php if ($search || $statusFilter || $priorityFilter): ?>
                <a href="tasks.php" class="btn btn-secondary">Clear Filters</a>
            <?php else: ?>
                <a href="add_task.php" class="btn btn-primary">Add Task</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>