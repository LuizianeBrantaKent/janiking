<?php
// view_product.php
require_once "../../../db/config.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid product ID.");
}

$product_id = intval($_GET['id']);

try {
    $sql = "SELECT * FROM products WHERE product_id = :product_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("Product not found.");
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Product - Janiking</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../../assets/css/style_admin.css"> <!-- ✅ fixed path -->
  <style>
    .product-img {
        max-width: 50%;
        height: auto;
        border: 1px solid #ddd;
        border-radius: 8px;
        margin-bottom: 15px;
        cursor: zoom-in;
    }
    .modal-img {
        max-width: 100%;
        height: auto;
    }
  </style>
</head>
<body>
    <?php include('../../includes/header.php'); ?>
    <?php include('../../includes/admin_navbar.php'); ?>

    <div class="main-container">
      <main class="main-content">
        <h1 class="page-title">Product Details</h1>

        <?php if (!empty($product['image_path'])): ?>
          <div class="mb-3">
            <img src="../../assets/images/products/<?php echo htmlspecialchars(basename($product['image_path'])); ?>" 
                 alt="Product Image" class="product-img" data-toggle="modal" data-target="#imageModal">
          </div>
        <?php endif; ?>

        <ul class="list-group">
          <li class="list-group-item"><strong>Product ID:</strong> <?php echo "#INV-" . htmlspecialchars($product['product_id']); ?></li>
          <li class="list-group-item"><strong>Name:</strong> <?php echo htmlspecialchars($product['name']); ?></li>
          <li class="list-group-item"><strong>Description:</strong> <?php echo htmlspecialchars($product['description']); ?></li>
          <li class="list-group-item"><strong>Price:</strong> $<?php echo number_format($product['price'], 2); ?></li>
          <li class="list-group-item"><strong>Stock Quantity:</strong> <?php echo htmlspecialchars($product['stock_quantity']); ?></li>
          <li class="list-group-item"><strong>Category:</strong> <?php echo htmlspecialchars($product['category']); ?></li>
        </ul>

        <a href="../admin_manage_inventory.php" class="btn btn-secondary mt-3">Back</a>
      </main>
    </div>

    <!-- Modal for Zoomed Image -->
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-body text-center">
            <img src="../../assets/images/products/<?php echo htmlspecialchars(basename($product['image_path'])); ?>" 
                 alt="Zoomed Product Image" class="modal-img">
          </div>
        </div>
      </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
