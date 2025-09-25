<?php
// admin_manage_inventory.php

require_once "../../db/config.php";

// Handle filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'All';

$sql = "SELECT product_id, name, description, stock_quantity, price, category, image_path FROM products";

if ($filter != "All") {
    if ($filter == "In Stock") {
        $sql .= " WHERE stock_quantity > 30";
    } elseif ($filter == "Low Stock") {
        $sql .= " WHERE stock_quantity BETWEEN 1 AND 30";
    } elseif ($filter == "Out of Stock") {
        $sql .= " WHERE stock_quantity = 0";
    }
}

$sql .= " ORDER BY product_id DESC";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Janiking - Manage Inventory</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style_admin.css">
  <style>
    .action-buttons {
      display: flex;
      gap: 8px;
    }
    .action-btn {
      padding: 6px 12px;
      font-size: 14px;
      border-radius: 4px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }
    .action-btn.view-btn {
      background-color: #004990;
      color: #fff;
    }
    .action-btn.view-btn:hover {
      background-color: #003366;
    }
    .action-btn.edit-btn {
      background-color: #ffc107;
      color: #000;
    }
    .action-btn.edit-btn:hover {
      background-color: #e0a800;
    }
    .action-btn.delete-btn {
      background-color: #dc3545;
      color: #fff;
    }
    .action-btn.delete-btn:hover {
      background-color: #b02a37;
    }
    .status-badge {
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: bold;
    }
    .status-in-stock { background: #28a745; color: #fff; }
    .status-low-stock { background: #ffc107; color: #000; }
    .status-out-of-stock { background: #dc3545; color: #fff; }
  </style>
</head>
<body>
  <?php
    include('../includes/header.php');
    include('../includes/admin_navbar.php');
  ?>

  <div class="main-container">
    <main class="main-content">
      
      <!-- Header + New Button -->
      <div class="top-controls">
        <h1 class="page-title">Manage Inventory</h1>
        <a href="admin_inventory/create_product.php" class="btn btn-primary">
          <i class="fas fa-plus"></i> New Inventory
        </a>
      </div>

      <!-- Filter -->
      <form class="filter-form" method="get">
        <label for="filter">Filter by Stock:</label>
        <select name="filter" id="filter" class="form-control" style="width:auto;display:inline-block;">
          <option value="All" <?php if($filter=="All") echo "selected"; ?>>All</option>
          <option value="In Stock" <?php if($filter=="In Stock") echo "selected"; ?>>In Stock</option>
          <option value="Low Stock" <?php if($filter=="Low Stock") echo "selected"; ?>>Low Stock</option>
          <option value="Out of Stock" <?php if($filter=="Out of Stock") echo "selected"; ?>>Out of Stock</option>
        </select>
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply</button>
      </form>

      <!-- Inventory Table -->
      <div class="table-card">
        <table>
          <thead>
            <tr>
              <th>Item ID</th>
              <th>Product Name</th>
              <th>Description</th>
              <th>Stock</th>
              <th>Unit Price</th>
              <th>Category</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php
          try {
              $stmt = $conn->prepare($sql);
              $stmt->execute();
              $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

              if (count($result) > 0) {
                  foreach ($result as $row) {
                      $stock_quantity = $row['stock_quantity'];
                      $status = ($stock_quantity > 30) ? 'In Stock' : (($stock_quantity > 0) ? 'Low Stock' : 'Out of Stock');
                      $statusClass = ($stock_quantity > 30) ? 'status-in-stock' : (($stock_quantity > 0) ? 'status-low-stock' : 'status-out-of-stock');

                      echo "<tr>";
                      echo "<td>#INV-" . htmlspecialchars($row['product_id']) . "</td>";
                      echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                      echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                      echo "<td>" . htmlspecialchars($stock_quantity) . "</td>";
                      echo "<td>$" . number_format($row['price'], 2) . "</td>";
                      echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                      echo "<td><span class='status-badge {$statusClass}'>" . htmlspecialchars($status) . "</span></td>";
                      echo "<td>
                              <div class='action-buttons'>
                                <a href='admin_inventory/view_product.php?id=" . $row['product_id'] . "' class='action-btn view-btn'><i class='fas fa-eye'></i> View</a>
                                <a href='admin_inventory/edit_product.php?id=" . $row['product_id'] . "' class='action-btn edit-btn'><i class='fas fa-edit'></i> Edit</a>
                                <a href='admin_inventory/delete_product.php?id=" . $row['product_id'] . "' onclick=\"return confirm('Are you sure?');\" class='action-btn delete-btn'><i class='fas fa-trash'></i> Remove</a>
                              </div>
                            </td>";
                      echo "</tr>";
                  }
              } else {
                  echo '<tr><td colspan="8" style="text-align:center;"><em>No products found.</em></td></tr>';
              }
          } catch (PDOException $e) {
              echo '<tr><td colspan="8" style="text-align:center;">Error fetching data: ' . $e->getMessage() . '</td></tr>';
          }
          ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>
</html>
