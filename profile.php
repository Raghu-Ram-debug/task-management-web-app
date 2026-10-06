<?php
/**
 * Profile Page
 * Task Manager Application
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

// Require authentication
requireAuth();

$user = getCurrentUser();
$pageTitle = 'Profile';
$errors = [];
$success = false;

$name = $user['name'];
$email = $user['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate name
    if (empty($name)) {
        $errors[] = 'Name is required';
    } elseif (strlen($name) > 100) {
        $errors[] = 'Name must be less than 100 characters';
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    } elseif (strlen($email) > 150) {
        $errors[] = 'Email must be less than 150 characters';
    }
    
    // Check if email is being changed and if it's already taken
    if ($email !== $user['email']) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user['id']]);
        if ($stmt->fetch()) {
            $errors[] = 'Email is already in use';
        }
    }
    
    // Handle password change
    $passwordChanged = false;
    if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
        if (empty($currentPassword)) {
            $errors[] = 'Current password is required to change password';
        } elseif (empty($newPassword)) {
            $errors[] = 'New password is required';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'New passwords do not match';
        } else {
            // Verify current password
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $userData = $stmt->fetch();
            
            if (!password_verify($currentPassword, $userData['password'])) {
                $errors[] = 'Current password is incorrect';
            } else {
                $passwordChanged = true;
            }
        }
    }
    
    if (empty($errors)) {
        $pdo = getDBConnection();
        
        if ($passwordChanged) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
            $result = $stmt->execute([$name, $email, $hashedPassword, $user['id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $result = $stmt->execute([$name, $email, $user['id']]);
        }
        
        if ($result) {
            // Update session
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            
            $success = true;
            $_SESSION['flash_message'] = 'Profile updated successfully';
            $_SESSION['flash_type'] = 'success';
            header('Location: profile.php');
            exit;
        } else {
            $errors[] = 'Failed to update profile. Please try again.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Profile</h1>
</div>

<div class="profile-section">
    <!-- Profile Info -->
    <div class="card">
        <div class="card-body">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
                <div class="profile-info">
                    <h2><?php echo escape($user['name']); ?></h2>
                    <p><?php echo escape($user['email']); ?></p>
                    <p style="font-size: 0.875rem; margin-top: 0.5rem;">
                        Member since <?php echo formatDate($user['created_at']); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Update Profile Form -->
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Update Profile</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($errors)): ?>
                <div class="flash-message error">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo escape($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="flash-message success">
                    Profile updated successfully
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" data-validate>
                <div class="form-group">
                    <label class="form-label required" for="name">Full Name</label>
                    <input type="text" 
                           class="form-input" 
                           id="name" 
                           name="name" 
                           value="<?php echo escape($name); ?>" 
                           required 
                           maxlength="100"
                           autocomplete="name">
                </div>
                
                <div class="form-group">
                    <label class="form-label required" for="email">Email</label>
                    <input type="email" 
                           class="form-input" 
                           id="email" 
                           name="email" 
                           value="<?php echo escape($email); ?>" 
                           required 
                           maxlength="150"
                           autocomplete="email">
                </div>
                
                <hr style="margin: 1.5rem 0; border-color: var(--border-color);">
                
                <h4 style="margin-bottom: 1rem;">Change Password</h4>
                <p class="form-help" style="margin-bottom: 1rem;">Leave blank to keep current password</p>
                
                <div class="form-group">
                    <label class="form-label" for="current_password">Current Password</label>
                    <input type="password" 
                           class="form-input" 
                           id="current_password" 
                           name="current_password" 
                           autocomplete="current-password"
                           placeholder="Enter current password">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="new_password">New Password</label>
                    <input type="password" 
                           class="form-input" 
                           id="new_password" 
                           name="new_password" 
                           minlength="6"
                           autocomplete="new-password"
                           placeholder="Enter new password (min 6 characters)">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm New Password</label>
                    <input type="password" 
                           class="form-input" 
                           id="confirm_password" 
                           name="confirm_password" 
                           autocomplete="new-password"
                           placeholder="Confirm new password">
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>