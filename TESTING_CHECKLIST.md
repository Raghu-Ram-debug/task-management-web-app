# Testing Checklist - Task Manager Application

## Pre-Deployment Setup
- [ ] XAMPP installed and running (Apache + MySQL)
- [ ] Database `task_manager` created via phpMyAdmin
- [ ] SQL schema imported from `database/task_manager.sql`
- [ ] `config/database.php` configured with correct MySQL credentials
- [ ] Project placed in XAMPP htdocs folder

## Core Functionality Tests

### 1. User Registration
- [ ] Navigate to `http://localhost/task-manager/register.php`
- [ ] Submit empty form - should show validation errors
- [ ] Submit with invalid email format - should show error
- [ ] Submit with password < 6 chars - should show error
- [ ] Submit with mismatched passwords - should show error
- [ ] Submit valid data - should redirect to login with success message
- [ ] Try registering with same email again - should show "Email already registered"

### 2. User Login
- [ ] Navigate to `http://localhost/task-manager/login.php`
- [ ] Submit empty form - should show validation errors
- [ ] Submit wrong credentials - should show "Invalid email or password"
- [ ] Submit correct credentials - should redirect to dashboard
- [ ] Verify session persists on page refresh
- [ ] Try accessing login/register while logged in - should redirect to dashboard

### 3. Dashboard
- [ ] Verify stats cards show correct counts (0 initially)
- [ ] Check "Recent Tasks" and "Upcoming" sections show empty state
- [ ] Click "Add Task" button - should navigate to add_task.php
- [ ] Verify user name displayed in top bar
- [ ] Verify navigation links work (Dashboard, My Tasks, Add Task, Profile, Logout)

### 4. Create Task
- [ ] Navigate to Add Task page
- [ ] Submit empty form - should show "Task title is required"
- [ ] Submit with title only - should create task successfully
- [ ] Submit with all fields (title, description, priority, status, due date) - should create task
- [ ] Verify redirect to tasks list with success message
- [ ] Verify task appears in list with correct data

### 5. View Tasks
- [ ] Navigate to My Tasks page
- [ ] Verify table shows all tasks with correct columns
- [ ] Verify priority badges show correct colors (High=red, Medium=yellow, Low=green)
- [ ] Verify status badges show correct colors (Completed=green, In Progress=blue, Pending=yellow)
- [ ] Verify overdue tasks highlighted in red
- [ ] Test pagination if more than 10 tasks
- [ ] Test mobile view (resize browser) - should show card layout

### 6. Edit Task
- [ ] Click Edit button on a task
- [ ] Verify form pre-populated with task data
- [ ] Modify title, description, priority, status, due date
- [ ] Submit - should redirect to tasks list with success message
- [ ] Verify changes reflected in task list
- [ ] Try editing another user's task (if multiple users) - should deny access

### 7. Task Status Changes
- [ ] Click "Complete" button on pending task
- [ ] Verify task status changes to Completed
- [ ] Verify success message shown
- [ ] Verify completed task no longer shows "Complete" button
- [ ] Verify overdue indicator disappears for completed tasks

### 8. Delete Task
- [ ] Click Delete button on a task
- [ ] Verify confirmation dialog appears
- [ ] Click Cancel - task should remain
- [ ] Click OK - task should be deleted
- [ ] Verify success message shown
- [ ] Verify task removed from list
- [ ] Try deleting another user's task - should deny access

### 9. Task Filtering & Search
- [ ] Test Status filter (Pending, In Progress, Completed)
- [ ] Test Priority filter (High, Medium, Low)
- [ ] Test Search by title/description
- [ ] Test Sort options (Newest, Oldest, Due Date, Priority)
- [ ] Test combined filters
- [ ] Click "Clear Filters" - should reset all filters
- [ ] Verify URL updates with filter parameters

### 10. Profile Management
- [ ] Navigate to Profile page
- [ ] Verify current name, email, join date displayed
- [ ] Update name only - should save successfully
- [ ] Update email only - should save successfully
- [ ] Try using existing email - should show "Email already in use"
- [ ] Change password with correct current password - should succeed
- [ ] Change password with wrong current password - should fail
- [ ] Change password with mismatched new passwords - should fail
- [ ] Change password with new password < 6 chars - should fail

### 11. Logout
- [ ] Click Logout in sidebar or top bar
- [ ] Verify redirect to login page
- [ ] Try accessing dashboard directly - should redirect to login
- [ ] Try accessing tasks page directly - should redirect to login

### 12. Security Tests
- [ ] Register User A, create tasks
- [ ] Logout, register User B
- [ ] Try accessing User A's task edit/delete URLs - should deny access
- [ ] Verify SQL injection attempts are handled (test with ' OR '1'='1 in search)
- [ ] Verify XSS attempts are escaped (test with `<script>alert(1)</script>` in task title)
- [ ] Check passwords are hashed in database (not plain text)

## Responsive Design Tests
- [ ] Desktop (1920x1080) - full sidebar, table layout
- [ ] Laptop (1366x768) - full sidebar, table layout
- [ ] Tablet (768x1024) - collapsible sidebar, card layout
- [ ] Mobile (375x667) - hidden sidebar (hamburger menu), card layout
- [ ] Test all forms usable on mobile
- [ ] Test navigation works on mobile

## Browser Compatibility
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

## Error Handling
- [ ] Test database connection failure (stop MySQL) - should show friendly error
- [ ] Test invalid task ID in URL - should redirect with error
- [ ] Test session expiry - should redirect to login

## Performance
- [ ] Page load times acceptable (< 2 seconds)
- [ ] No console errors in browser dev tools
- [ ] Images/SVG icons load correctly

## Accessibility
- [ ] All form inputs have labels
- [ ] Buttons have accessible names
- [ ] Color contrast meets WCAG AA
- [ ] Keyboard navigation works
- [ ] Screen reader friendly (ARIA labels on icon buttons)

---

## Test Results Template

| Test Case | Status | Notes |
|-----------|--------|-------|
| User Registration | [ ] Pass / [ ] Fail | |
| User Login | [ ] Pass / [ ] Fail | |
| Dashboard | [ ] Pass / [ ] Fail | |
| Create Task | [ ] Pass / [ ] Fail | |
| View Tasks | [ ] Pass / [ ] Fail | |
| Edit Task | [ ] Pass / [ ] Fail | |
| Delete Task | [ ] Pass / [ ] Fail | |
| Task Status Change | [ ] Pass / [ ] Fail | |
| Filtering & Search | [ ] Pass / [ ] Fail | |
| Profile Management | [ ] Pass / [ ] Fail | |
| Logout | [ ] Pass / [ ] Fail | |
| Security (User Isolation) | [ ] Pass / [ ] Fail | |
| Security (SQL Injection) | [ ] Pass / [ ] Fail | |
| Security (XSS) | [ ] Pass / [ ] Fail | |
| Responsive Design | [ ] Pass / [ ] Fail | |
| Browser Compatibility | [ ] Pass / [ ] Fail | |
| Error Handling | [ ] Pass / [ ] Fail | |
| Accessibility | [ ] Pass / [ ] Fail | |

---

## Known Issues / Limitations

1. **File Uploads**: Not implemented - tasks don't support attachments
2. **Email Notifications**: Not implemented - no due date reminders
3. **Recurring Tasks**: Not implemented
4. **Task Categories/Tags**: Not implemented
5. **API**: No REST API for mobile apps
6. **Tests**: No automated test suite included

---

## Sign-off

**Tested By**: _______________

**Date**: _______________

**All Critical Tests Pass**: [ ] Yes / [ ] No