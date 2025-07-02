<?php
require_once 'middleware.php';
require_admin();
delete_expired_users($pdo); // <-- Add this line

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_user'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        $permissions = null;
        if ($role === 'staff' && isset($_POST['permissions'])) {
            $permissions = implode(',', $_POST['permissions']);
        }
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, permissions) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $password, $role, $permissions]);
        log_action($_SESSION['user_id'], 'user_created', "Created user $username with role $role", get_client_ip());
        $_SESSION['message'] = 'User created successfully';
        header('Location: users.php');
        exit();
    } elseif (isset($_POST['update_user'])) {
        $user_id = $_POST['edit_user_id'];
        $username = $_POST['edit_username'];
        $role = $_POST['edit_role'];
        $password = $_POST['edit_password'];
        $permissions = null;
        if ($role === 'staff' && isset($_POST['edit_permissions'])) {
            $permissions = implode(',', $_POST['edit_permissions']);
        }
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, role = ?, permissions = ? WHERE id = ?");
            $stmt->execute([$username, $hashed, $role, $permissions, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ?, permissions = ? WHERE id = ?");
            $stmt->execute([$username, $role, $permissions, $user_id]);
        }
        log_action($_SESSION['user_id'], 'user_updated', "Updated user $user_id", get_client_ip());
        $_SESSION['message'] = 'User updated successfully';
        header('Location: users.php');
        exit();
    } elseif (isset($_POST['update_role'])) {
        $user_id = $_POST['user_id'];
        $new_role = $_POST['role'];
        
        // Get current role
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if ($user) {
            $old_role = $user['role'];
            if ($old_role !== $new_role) {
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$new_role, $user_id]);
                
                // Log role change
                $stmt = $pdo->prepare("INSERT INTO role_change_logs (user_id, old_role, new_role, changed_by) VALUES (?, ?, ?, ?)");
                $stmt->execute([$user_id, $old_role, $new_role, $_SESSION['user_id']]);
                
                log_action($_SESSION['user_id'], 'role_changed', "Changed role for user $user_id from $old_role to $new_role", get_client_ip());
                $_SESSION['message'] = 'Role updated successfully';
            }
        }
        
        header('Location: users.php');
        exit();
    } elseif (isset($_POST['toggle_status'])) {
        $user_id = $_POST['user_id'];

        // Fetch current status before toggling
        $stmt = $pdo->prepare("SELECT is_active FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        $action = $user && $user['is_active'] ? 'deactivated' : 'activated';

        if ($user && $user['is_active']) {
            // Deactivate: set is_active=0, disabled=1, disabled_at=NOW()
            $stmt = $pdo->prepare("UPDATE users SET is_active = 0, disabled = 1, disabled_at = NOW() WHERE id = ?");
        } else {
            // Activate: set is_active=1, disabled=0, disabled_at=NULL
            $stmt = $pdo->prepare("UPDATE users SET is_active = 1, disabled = 0, disabled_at = NULL WHERE id = ?");
        }
        $stmt->execute([$user_id]);

        log_action($_SESSION['user_id'], 'user_status', "$action user $user_id", get_client_ip());
        $_SESSION['message'] = 'User status updated';
        header('Location: users.php');
        exit();
    } elseif (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        if (delete_user_by_id($pdo, $user_id)) {
            log_action($_SESSION['user_id'], 'user_deleted', "Deleted user $user_id", get_client_ip());
            $_SESSION['message'] = 'User deleted successfully';
        } else {
            $_SESSION['message'] = 'Failed to delete user';
        }
        header('Location: users.php');
        exit();
    }
}

// Get all users
$stmt = $pdo->query("SELECT id, username, role, is_active, created_at, last_login, disabled, disabled_at, permissions FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

// Helper for time until deletion
function get_time_until_deletion($user) {
    $now = new DateTime();
    if (isset($user['disabled']) && $user['disabled'] && !empty($user['disabled_at'])) {
        $deleteAt = new DateTime($user['disabled_at']);
        $deleteAt->modify('+12 hours'); // Match the auto-delete interval
    } else {
        return 'N/A';
    }
    $interval = $now->diff($deleteAt);
    if ($deleteAt < $now) {
        return '<span class="text-danger">Pending deletion</span>';
    }
    $parts = [];
    if ($interval->d > 0) $parts[] = $interval->d . 'd';
    if ($interval->h > 0) $parts[] = $interval->h . 'h';
    if ($interval->i > 0) $parts[] = $interval->i . 'm';
    if (empty($parts)) $parts[] = $interval->s . 's';
    return implode(' ', $parts) . ' left';
}

// Client IP function
function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ipList[0]);
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container mt-4">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <h2>User Management</h2>
        
        <!-- Add User Button -->
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
            Add User
        </button>

        <!-- Add User Modal -->
        <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST">
                <div class="modal-header">
                  <h5 class="modal-title" id="addUserModalLabel">Create New User</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                  </div>
                  <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                  </div>
                  <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role" required onchange="togglePermissions(this.value)">
                      <option value="admin">Admin</option>
                      <option value="staff">Staff</option>
                    </select>
                  </div>
                  <div class="mb-3" id="permissionsBox">
                    <label class="form-label">Permissions</label><br>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]" value="view" id="perm_view">
                      <label class="form-check-label" for="perm_view">View</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]" value="add" id="perm_add">
                      <label class="form-check-label" for="perm_add">Add</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]" value="update" id="perm_update">
                      <label class="form-check-label" for="perm_update">Update</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]" value="delete" id="perm_delete">
                      <label class="form-check-label" for="perm_delete">Delete</label>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" name="create_user" class="btn btn-primary">Create User</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        
        <!-- Edit/View User Modal -->
        <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST" id="editUserForm">
                <div class="modal-header">
                  <h5 class="modal-title" id="editUserModalLabel">View/Edit User</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="edit_user_id" id="edit_user_id">
                  <div class="mb-3">
                    <label for="edit_username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="edit_username" name="edit_username" required>
                  </div>
                  <div class="mb-3">
                    <label for="edit_password" class="form-label">Password (leave blank to keep current)</label>
                    <input type="password" class="form-control" id="edit_password" name="edit_password">
                  </div>
                  <div class="mb-3">
                    <label for="edit_role" class="form-label">Role</label>
                    <select class="form-select" id="edit_role" name="edit_role" required onchange="toggleEditPermissions(this.value)">
                      <option value="admin">Admin</option>
                      <option value="staff">Staff</option>
                    </select>
                  </div>
                  <div class="mb-3" id="edit_permissionsBox" style="display:none;">
                    <label class="form-label">Staff Permissions</label><br>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="checkbox" name="edit_permissions[]" value="view" id="edit_perm_view">
                      <label class="form-check-label" for="edit_perm_view">View</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="checkbox" name="edit_permissions[]" value="add" id="edit_perm_add">
                      <label class="form-check-label" for="edit_perm_add">Add</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="checkbox" name="edit_permissions[]" value="update" id="edit_perm_update">
                      <label class="form-check-label" for="edit_perm_update">Update</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="checkbox" name="edit_permissions[]" value="delete" id="edit_perm_delete">
                      <label class="form-check-label" for="edit_perm_delete">Delete</label>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" name="update_user" class="btn btn-primary">Save changes</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- View User Modal -->
        <div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="viewUserModalLabel">View User</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" id="view_username" readonly>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Role</label>
                    <input type="text" class="form-control" id="view_role" readonly>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Permissions</label>
                    <input type="text" class="form-control" id="view_permissions" readonly>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Status</label>
                    <input type="text" class="form-control" id="view_status" readonly>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Created At</label>
                    <input type="text" class="form-control" id="view_created_at" readonly>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
          </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                User List
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Time Until Deletion</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= ucfirst($user['role']) ?></td>
                            <td><?= $user['is_active'] ? 'Active' : 'Inactive' ?></td>
                            <td><?= $user['created_at'] ?></td>
                            <td><?= get_time_until_deletion($user) ?></td>
                            <td>
                                <button class="btn btn-sm btn-secondary" onclick='showViewUserModal(<?= json_encode($user) ?>)'>View</button>
                                <button class="btn btn-sm btn-info" 
                                    data-bs-toggle="modal" data-bs-target="#editUserModal" 
                                    data-user-id="<?= $user['id'] ?>"
                                    data-username="<?= htmlspecialchars($user['username']) ?>"
                                    data-role="<?= $user['role'] ?>"
                                    data-permissions="<?= htmlspecialchars($user['permissions']) ?>"
                                    data-status="<?= $user['is_active'] ? 'Active' : 'Inactive' ?>"
                                    data-created="<?= $user['created_at'] ?>"
                                    >Edit
                                </button>
                                
                                <form method="POST" style="display: inline;" onsubmit="return confirmDeactivation(<?= $user['is_active'] ? 'true' : 'false' ?>);">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" name="toggle_status" class="btn btn-sm btn-<?= $user['is_active'] ? 'warning' : 'success' ?>">
                                        <?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>

                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" name="delete_user" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmDeactivation(isActive) {
    if (isActive) {
        return confirm("Are you sure you want to deactivate this account?\nAfter 12 hours of being deactivated, the account will be permanently deleted.");
    }
    return true;
}

// Populate edit user modal
var editUserModal = document.getElementById('editUserModal');
editUserModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var userId = button.getAttribute('data-user-id');
    var username = button.getAttribute('data-username');
    var role = button.getAttribute('data-role');
    var permissions = button.getAttribute('data-permissions') || '';
    var permsArr = permissions.split(',');

    // Set fields
    editUserModal.querySelector('#edit_user_id').value = userId;
    editUserModal.querySelector('#edit_username').value = username;
    editUserModal.querySelector('#edit_role').value = role;

    // Always show the permissions box
    document.getElementById('edit_permissionsBox').style.display = 'block';

    // Enable/disable checkboxes based on role
    var editCheckboxes = [
        document.getElementById('edit_perm_view'),
        document.getElementById('edit_perm_add'),
        document.getElementById('edit_perm_update'),
        document.getElementById('edit_perm_delete')
    ];
    if (role === 'admin') {
        editCheckboxes.forEach(function(cb) { cb.checked = true; cb.disabled = true; });
    } else {
        editCheckboxes.forEach(function(cb) { cb.checked = false; cb.disabled = false; });
        permsArr.forEach(function(p) {
            if (p) {
                var cb = document.getElementById('edit_perm_' + p);
                if (cb) cb.checked = true;
            }
        });
    }
});

// Update checkboxes if role is changed in edit modal
function toggleEditPermissions(role) {
    document.getElementById('edit_permissionsBox').style.display = 'block';
    var editCheckboxes = [
        document.getElementById('edit_perm_view'),
        document.getElementById('edit_perm_add'),
        document.getElementById('edit_perm_update'),
        document.getElementById('edit_perm_delete')
    ];
    if (role === 'admin') {
        editCheckboxes.forEach(function(cb) { cb.checked = true; cb.disabled = true; });
    } else {
        editCheckboxes.forEach(function(cb) { cb.checked = false; cb.disabled = false; });
    }
}

// View User Modal
function showViewUserModal(user) {
    document.getElementById('view_username').value = user.username;
    document.getElementById('view_role').value = user.role.charAt(0).toUpperCase() + user.role.slice(1);
    document.getElementById('view_permissions').value = user.permissions ? user.permissions : 'N/A';
    document.getElementById('view_status').value = user.is_active ? 'Active' : 'Inactive';
    document.getElementById('view_created_at').value = user.created_at;
    var modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
    modal.show();
}

function togglePermissions(role) {
    var checkboxes = document.querySelectorAll('.perm-checkbox');
    if (role === 'admin') {
        checkboxes.forEach(function(cb) { cb.checked = true; cb.disabled = true; });
    } else {
        checkboxes.forEach(function(cb) { cb.checked = false; cb.disabled = false; });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    togglePermissions(document.getElementById('role').value);
    document.getElementById('role').addEventListener('change', function() {
        togglePermissions(this.value);
    });
});
</script>
</body>
</html>