<?php
require_once 'middleware.php';
require_login();

// Staff can access this page, but admins can too
if (has_role('staff')) {
    // Staff-specific logic if needed
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Permission checks for staff
    if (isset($_POST['add_item'])) {
        if (!has_permission('add')) {
            $_SESSION['message'] = 'You do not have permission to add items.';
            header('Location: inventory.php');
            exit();
        }
        $name = $_POST['name'];
        $quantity = $_POST['quantity'];
        $category = $_POST['category'];
        
        $stmt = $pdo->prepare("INSERT INTO inventory (name, quantity, category, created_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $quantity, $category, $_SESSION['user_id']]);
        
        log_action($_SESSION['user_id'], 'inventory_add', "Added item: $name", $_SERVER['REMOTE_ADDR']);
        $_SESSION['message'] = 'Item added successfully';
        header('Location: inventory.php');
        exit();
    } elseif (isset($_POST['update_item'])) {
        if (!has_permission('update')) {
            $_SESSION['message'] = 'You do not have permission to update items.';
            header('Location: inventory.php');
            exit();
        }
        $id = $_POST['edit_item_id'];
        $name = $_POST['edit_name'];
        $quantity = $_POST['edit_quantity'];
        $category = $_POST['edit_category'];
        
        $stmt = $pdo->prepare("UPDATE inventory SET name = ?, quantity = ?, category = ? WHERE id = ?");
        $stmt->execute([$name, $quantity, $category, $id]);
        
        log_action($_SESSION['user_id'], 'inventory_update', "Updated item $id", $_SERVER['REMOTE_ADDR']);
        $_SESSION['message'] = 'Item updated successfully';
        header('Location: inventory.php');
        exit();
    } elseif (isset($_POST['delete_item'])) {
        if (!has_permission('delete')) {
            $_SESSION['message'] = 'You do not have permission to delete items.';
            header('Location: inventory.php');
            exit();
        }
        $id = $_POST['id'];
        
        $stmt = $pdo->prepare("DELETE FROM inventory WHERE id = ?");
        $stmt->execute([$id]);
        
        log_action($_SESSION['user_id'], 'inventory_delete', "Deleted item $id", $_SERVER['REMOTE_ADDR']);
        $_SESSION['message'] = 'Item deleted successfully';
        header('Location: inventory.php');
        exit();
    }
}

// Get all inventory items
$stmt = $pdo->query("SELECT i.*, u.username as creator FROM inventory i JOIN users u ON i.created_by = u.id ORDER BY i.created_at DESC");
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container mt-4">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <h2>Inventory Management</h2>
        
        <!-- Add Item Button -->
        <?php if (has_permission('add')): ?>
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addItemModal">
            Add New Item
        </button>
        <?php endif; ?>

        <!-- Add Item Modal -->
        <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST">
                <div class="modal-header">
                  <h5 class="modal-title" id="addItemModalLabel">Add New Item</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label for="name" class="form-label">Item Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                  </div>
                  <div class="mb-3">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input type="number" class="form-control" id="quantity" name="quantity" required>
                  </div>
                  <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <input type="text" class="form-control" id="category" name="category" required>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" name="add_item" class="btn btn-primary">Add Item</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                Inventory List
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>Category</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= $item['id'] ?></td>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= htmlspecialchars($item['category']) ?></td>
                            <td><?= htmlspecialchars($item['creator']) ?></td>
                            <td><?= $item['created_at'] ?></td>
                            <td>
                                <?php if (has_permission('view')): ?>
                                <button class="btn btn-sm btn-secondary" onclick='showViewItemModal(<?= json_encode($item) ?>)'>View</button>
                                <?php endif; ?>
                                <?php if (has_permission('update')): ?>
                                <button class="btn btn-sm btn-info" onclick='showEditItemModal(<?= json_encode($item) ?>)'>Edit</button>
                                <?php endif; ?>
                                <?php if (has_permission('delete')): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <button type="submit" name="delete_item" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="POST" id="editItemForm">
            <div class="modal-header">
              <h5 class="modal-title" id="editItemModalLabel">Edit Item</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="edit_item_id" id="edit_item_id">
              <div class="mb-3">
                <label for="edit_name" class="form-label">Item Name</label>
                <input type="text" class="form-control" id="edit_name" name="edit_name" required>
              </div>
              <div class="mb-3">
                <label for="edit_quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="edit_quantity" name="edit_quantity" required>
              </div>
              <div class="mb-3">
                <label for="edit_category" class="form-label">Category</label>
                <input type="text" class="form-control" id="edit_category" name="edit_category" required>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" name="update_item" class="btn btn-primary">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- View Item Modal -->
    <div class="modal fade" id="viewItemModal" tabindex="-1" aria-labelledby="viewItemModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="viewItemModalLabel">View Item</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Item Name</label>
                <input type="text" class="form-control" id="view_name" readonly>
              </div>
              <div class="mb-3">
                <label class="form-label">Quantity</label>
                <input type="number" class="form-control" id="view_quantity" readonly>
              </div>
              <div class="mb-3">
                <label class="form-label">Category</label>
                <input type="text" class="form-control" id="view_category" readonly>
              </div>
              <div class="mb-3">
                <label class="form-label">Created By</label>
                <input type="text" class="form-control" id="view_creator" readonly>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function showEditItemModal(item) {
        document.getElementById('edit_item_id').value = item.id;
        document.getElementById('edit_name').value = item.name;
        document.getElementById('edit_quantity').value = item.quantity;
        document.getElementById('edit_category').value = item.category;
        var modal = new bootstrap.Modal(document.getElementById('editItemModal'));
        modal.show();
    }
    function showViewItemModal(item) {
        document.getElementById('view_name').value = item.name;
        document.getElementById('view_quantity').value = item.quantity;
        document.getElementById('view_category').value = item.category;
        document.getElementById('view_creator').value = item.creator;
        document.getElementById('view_created_at').value = item.created_at;
        var modal = new bootstrap.Modal(document.getElementById('viewItemModal'));
        modal.show();
    }
    </script>
</body>
</html>