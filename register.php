<?php
/**
 * Register Page
 * Task Manager Application
 */

require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
redirectIfLoggedIn();

$pageTitle = 'Register';
$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    $result = registerUser($name, $email, $password, $confirmPassword);
    
    if ($result['success']) {
        $_SESSION['flash_message'] = $result['message'];
        $_SESSION['flash_type'] = 'success';
        header('Location: login.php');
        exit;
    } else {
        $errors[] = $result['message'];
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-header">
    <div class="logo">Task Manager</div>
    <h1>Create Account</h1>
    <p>Start managing your tasks today</p>
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
        <label class="form-label required" for="name">Full Name</label>
        <input type="text" 
               class="form-input" 
               id="name" 
               name="name" 
               value="<?php echo escape($name); ?>" 
               required 
               maxlength="100"
               autocomplete="name"
               placeholder="Enter your full name">
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
               minlength="6"
               autocomplete="new-password"
               placeholder="Create a password (min 6 characters)">
    </div>
    
    <div class="form-group">
        <label class="form-label required" for="confirm_password">Confirm Password</label>
        <input type="password" 
               class="form-input" 
               id="confirm_password" 
               name="confirm_password" 
               required 
               autocomplete="new-password"
               placeholder="Confirm your password">
    </div>
    
    <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
</form>

<div class="auth-footer">
    <p>Already have an account? <a href="login.php">Sign in</a></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>