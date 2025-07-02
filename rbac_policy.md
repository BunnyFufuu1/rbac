# Role-Based Access Control (RBAC) Policy

## Roles

1. **Admin**
   - Full access to all system features
   - Can manage users and roles
   - Can view system logs
   - Can change system settings
   - Required to use Two-Factor Authentication

2. **Staff**
   - Can view and manage inventory
   - Cannot access user management
   - Cannot view system logs
   - Cannot change system settings
a
## Access Control Rules

1. **Role Assignment**
   - Only Admins can assign or change roles
   - Each user must have exactly one role
   - All role changes are logged with timestamp and admin who made the change

2. **Authentication**
   - Password must be hashed using `password_hash()`
   - After 3 failed login attempts, account is locked for 1 minute
   - Admin accounts require Two-Factor Authentication
   - Inactive accounts (no login for 3 days) are automatically disabled

3. **Privilege Escalation Prevention**
   - Staff cannot grant themselves Admin privileges
   - Only explicit Admin action can change a user's role

## Audit Requirements

1. **Logging**
   - All role changes are logged
   - All failed login attempts are logged with IP address
   - All unauthorized access attempts are logged
   - User activity is logged (logins, logouts, critical actions)

2. **Periodic Review**
   - System generates reports of user activity and roles
   - Admins can review logs and user activity