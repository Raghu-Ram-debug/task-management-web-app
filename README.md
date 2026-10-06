# Task Manager

A simple, functional task management web application built with PHP, MySQL, HTML, CSS, and JavaScript. This project demonstrates core web development concepts including user authentication, CRUD operations, database design, and responsive UI.

## Project Overview

Task Manager allows users to register, log in, and manage their personal tasks. Users can create tasks with titles, descriptions, priorities, due dates, and statuses. The application provides filtering, searching, and sorting capabilities to help users organize their work effectively.

## Features

### User Authentication
- **Registration**: Create account with name, email, and password
- **Login**: Secure authentication using PHP sessions
- **Logout**: Destroy session and redirect to login
- **Password Security**: Passwords hashed using PHP's `password_hash()`

### Task Management
- **Create Tasks**: Add tasks with title, description, priority (Low/Medium/High), status (Pending/In Progress/Completed), and due date
- **View Tasks**: List all tasks in a responsive table (desktop) or card layout (mobile)
- **Edit Tasks**: Modify any task property
- **Delete Tasks**: Remove tasks with confirmation dialog
- **Mark Complete**: Quick status update to Completed

### Organization & Filtering
- **Status Filter**: Filter by Pending, In Progress, Completed
- **Priority Filter**: Filter by High, Medium, Low
- **Search**: Search tasks by title or description
- **Sorting**: Sort by creation date, due date, or priority
- **Overdue Indicator**: Visual highlight for overdue tasks

### Dashboard
- Statistics cards showing total, pending, in-progress, completed, and overdue task counts
- Recent tasks list
- Upcoming tasks (next 7 days)

### Profile Management
- View account information
- Update name and email
- Change password with current password verification

### Security
- Prepared statements for all database queries
- Password hashing with `password_hash()` and verification with `password_verify()`
- Session-based authentication
- Input validation and sanitization
- Output escaping to prevent XSS
- User isolation (users can only access their own tasks)
- Protected routes (authenticated pages redirect to login)

## Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ / MariaDB 10.2+
- **Frontend**: HTML5, CSS3, Vanilla JavaScript (ES6+)
- **Server**: Apache (via XAMPP)

## Database Setup

### 1. Create Database

Run the SQL file in phpMyAdmin or MySQL command line:

```bash
mysql -u root -p < database/task_manager.sql
```

Or manually execute the SQL in `database/task_manager.sql`:

```sql
CREATE DATABASE IF NOT EXISTS `task_manager` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `task_manager`;

CREATE TABLE `users` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tasks` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT,
    `priority` ENUM('Low', 'Medium', 'High') NOT NULL DEFAULT 'Medium',
    `status` ENUM('Pending', 'In Progress', 'Completed') NOT NULL DEFAULT 'Pending',
    `due_date` DATE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_priority` (`priority`),
    INDEX `idx_due_date` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2. Configure Database Connection

Edit `config/database.php` with your MySQL credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'task_manager');
define('DB_USER', 'root');
define('DB_PASS', '');  // Your MySQL password
define('DB_CHARSET', 'utf8mb4');
```

## How to Run (XAMPP)

### Prerequisites
- XAMPP installed (Apache + MySQL)
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.2+

### Steps

1. **Start XAMPP**
   - Open XAMPP Control Panel
   - Start **Apache** and **MySQL**

2. **Create Database**
   - Open browser and go to `http://localhost/phpmyadmin`
   - Click **Import** tab
   - Select `database/task_manager.sql` from the project
   - Click **Go** to execute

3. **Place Project Files**
   - Copy the `task-manager` folder to your XAMPP `htdocs` directory:
     - Windows: `C:\xampp\htdocs\task-manager`
     - Mac: `/Applications/XAMPP/htdocs/task-manager`
     - Linux: `/opt/lampp/htdocs/task-manager`

4. **Configure Database** (if needed)
   - Edit `config/database.php` if your MySQL credentials differ from defaults

5. **Access Application**
   - Open browser and navigate to:
     - `http://localhost/task-manager/`
     - Or `http://localhost/task-manager/index.php`

6. **Test the Application**
   - Register a new account
   - Log in
   - Create, edit, and delete tasks
   - Test filtering and search
   - Update profile

## Folder Structure

```
task-manager/
│
├── index.php              # Entry point - redirects to login or dashboard
├── login.php              # User login page
├── register.php           # User registration page
├── logout.php             # Logout handler
│
├── dashboard.php          # Main dashboard with stats
├── tasks.php              # Task list with filters
├── add_task.php           # Create new task
├── edit_task.php          # Edit existing task
├── delete_task.php        # Delete task confirmation
├── profile.php            # User profile management
│
├── config/
│   └── database.php       # Database configuration & connection
│
├── includes/
│   ├── auth.php           # Authentication functions & helpers
│   ├── header.php         # HTML header & navigation
│   └── footer.php         # HTML footer & scripts
│
├── css/
│   └── style.css          # Complete stylesheet
│
├── js/
│   └── script.js          # Frontend JavaScript
│
├── database/
│   └── task_manager.sql   # Database schema
│
└── README.md              # This file
```

## Screenshots

*Add screenshots here after running the application*

- Login Page
- Registration Page
- Dashboard
- Tasks List (Desktop)
- Tasks List (Mobile)
- Add/Edit Task Form
- Profile Page

## Future Improvements

- [ ] Task categories/tags
- [ ] Task comments/notes
- [ ] Email notifications for due dates
- [ ] Recurring tasks
- [ ] Task attachments
- [ ] Dark mode toggle
- [ ] Keyboard shortcuts
- [ ] Export tasks to CSV/PDF
- [ ] Task sharing between users
- [ ] API for mobile app integration
- [ ] Unit tests with PHPUnit
- [ ] Docker configuration

## License

This project is created for educational purposes as a student portfolio project.

## Author

Built as a BCA (Bachelor of Computer Applications) student project to demonstrate proficiency in:
- HTML5, CSS3, JavaScript (ES6+)
- PHP (procedural, session management, PDO)
- MySQL (schema design, queries, relationships)
- Web security fundamentals
- Responsive design