<?php
/**
 * Index Page - Redirect to dashboard or login
 * Task Manager Application
 */

require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;