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
    <title>View Product</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #004990;
            margin-bottom: 20px;
        }
        .product-img {
            max-width: 100%;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #004990;
            border-color: #004990;
        }
        .btn-primary:hover {
            background-color: #003366;
            border-color: #003366;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Product Details</h2>

        <?php if (!empty($product['image_path'])): ?>
            <img src="../../assets/images/products/<?php echo htmlspecialchars(basename($product['image_path'])); ?>" 
                 alt="Product Image" class="product-img">
        <?php endif; ?>

        <p><strong>Name:</strong> <?php echo htmlspecialchars($product['name']); ?></p>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($product['description']); ?></p>
        <p><strong>Price:</strong> $<?php echo number_format($product['price'], 2); ?></p>
        <p><strong>Stock Quantity:</strong> <?php echo htmlspecialchars($product['stock_quantity']); ?></p>
        <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category']); ?></p>

        <a href="../admin_manage_inventory.php" class="btn btn-secondary">Back to Inventory</a>
    </div>
</body>
</html>
