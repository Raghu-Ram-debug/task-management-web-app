<?php
/**
 * Login Page
 * Task Manager Application
 */

require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
redirectIfLoggedIn();

$pageTitle = 'Login';
$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $result = loginUser($email, $password);
    
    if ($result['success']) {
        $_SESSION['flash_message'] = $result['message'];
        $_SESSION['flash_type'] = 'success';
        header('Location: dashboard.php');
        exit;
    } else {
        $errors[] = $result['message'];
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-header">
    <div class="logo">Task Manager</div>
    <h1>Welcome Back</h1>
    <p>Sign in to manage your tasks</p>
</div>

<?php if (!empty($errors)): ?>
    <div class="flash-message error">
        <?php foreach ($errors as $error): ?>
            <div><?php echo escape($error); ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form class="auth-form" method="POST" action="" data-validate>
    <div class="form-group">
        <label class="form-label required" for="email">Email</label>
        <input type="email" 
               class="form-input" 
               id="email" 
               name="email" 
               value="<?php echo escape($email); ?>" 
               required 
               autocomplete="email"
               placeholder="Enter your email">
    </div>
    
    <div class="form-group">
        <label class="form-label required" for="password">Password</label>
        <input type="password" 
               class="form-input" 
               id="password" 
               name="password" 
               required 
               autocomplete="current-password"
               placeholder="Enter your password">
    </div>
    
    <button type="submit" class="btn btn-primary" style="width: 100%;">Sign In</button>
</form>

<div class="auth-footer">
    <p>Don't have an account? <a href="register.php">Register</a></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>