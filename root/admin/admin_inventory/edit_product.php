<?php
// edit_product.php
require_once "../../../db/config.php";

// Check if product id is provided
if (!isset($_GET["id"]) || empty(trim($_GET["id"]))) {
    header("location: ../admin_manage_inventory.php");
    exit();
}

$id = intval($_GET["id"]);
$name = $description = $price = $stock_quantity = $category = $image_path = "";
$name_err = $price_err = $stock_err = $category_err = "";

// Fetch product details
try {
    $sql = "SELECT * FROM products WHERE product_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $name = $row["name"];
        $description = $row["description"];
        $price = $row["price"];
        $stock_quantity = $row["stock_quantity"];
        $category = $row["category"];
        $image_path = $row["image_path"];
    } else {
        echo "Product not found.";
        exit();
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate product name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter a product name.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Validate price
    if (empty(trim($_POST["price"]))) {
        $price_err = "Please enter a price.";
    } elseif (!is_numeric($_POST["price"])) {
        $price_err = "Price must be numeric.";
    } else {
        $price = (float) trim($_POST["price"]);
    }

    // Validate stock
    if ($_POST["stock_quantity"] === "" || !is_numeric($_POST["stock_quantity"])) {
        $stock_err = "Stock must be a valid number.";
    } else {
        $stock_quantity = (int) $_POST["stock_quantity"];
    }

    // Validate category
    $valid_categories = ['equipment', 'safety gear', 'uniforms', 'consumables'];
    $category = trim($_POST["category"]);
    if (!in_array($category, $valid_categories)) {
        $category_err = "Please select a valid category.";
    }

    $description = trim($_POST["description"]);

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "../../assets/images/products/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileTmp = $_FILES['image']['tmp_name'];
        $fileName = time() . "_" . basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmp, $targetFile)) {
            $image_path = "assets/images/products/" . $fileName; // save relative path
        }
    }

    if (empty($name_err) && empty($price_err) && empty($stock_err) && empty($category_err)) {
        try {
            $sql = "UPDATE products 
                    SET name = :name, description = :description, price = :price, 
                        stock_quantity = :stock_quantity, category = :category, image_path = :image_path 
                    WHERE product_id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':price', $price, PDO::PARAM_STR);
            $stmt->bindParam(':stock_quantity', $stock_quantity, PDO::PARAM_INT);
            $stmt->bindParam(':category', $category, PDO::PARAM_STR);
            $stmt->bindParam(':image_path', $image_path, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                header("location: ../admin_manage_inventory.php");
                exit();
            } else {
                echo "Something went wrong while updating.";
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>Edit Product</h2>
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label>Product Name</label>
            <input type="text" name="name" 
                class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" 
                value="<?php echo htmlspecialchars($name); ?>">
            <span class="invalid-feedback"><?php echo $name_err; ?></span>
        </div>
        <div class="form-group">
            <label>Description (optional)</label>
            <textarea name="description" class="form-control"><?php echo htmlspecialchars($description); ?></textarea>
        </div>
        <div class="form-group">
            <label>Unit Price</label>
            <input type="text" name="price" 
                class="form-control <?php echo (!empty($price_err)) ? 'is-invalid' : ''; ?>" 
                value="<?php echo htmlspecialchars($price); ?>">
            <span class="invalid-feedback"><?php echo $price_err; ?></span>
        </div>
        <div class="form-group">
            <label>Stock Quantity</label>
            <input type="text" name="stock_quantity" 
                class="form-control <?php echo (!empty($stock_err)) ? 'is-invalid' : ''; ?>" 
                value="<?php echo htmlspecialchars($stock_quantity); ?>">
            <span class="invalid-feedback"><?php echo $stock_err; ?></span>
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category" class="form-control <?php echo (!empty($category_err)) ? 'is-invalid' : ''; ?>">
                <option value="" <?php echo empty($category) ? 'selected' : ''; ?>>Select a category</option>
                <option value="equipment" <?php echo ($category == 'equipment') ? 'selected' : ''; ?>>Equipment</option>
                <option value="safety gear" <?php echo ($category == 'safety gear') ? 'selected' : ''; ?>>Safety Gear</option>
                <option value="uniforms" <?php echo ($category == 'uniforms') ? 'selected' : ''; ?>>Uniforms</option>
                <option value="consumables" <?php echo ($category == 'consumables') ? 'selected' : ''; ?>>Consumables</option>
            </select>
            <span class="invalid-feedback"><?php echo $category_err; ?></span>
        </div>

        <div class="form-group">
            <label>Product Image</label><br>
            <?php if (!empty($image_path)): ?>
                <img src="../../<?php echo htmlspecialchars($image_path); ?>" alt="Product Image" style="max-width:200px; margin-bottom:10px; border:1px solid #ccc; padding:5px;">
            <?php endif; ?>
            <input type="file" name="image" class="form-control-file">
            <small class="form-text text-muted">Leave blank to keep current image.</small>
        </div>

        <button type="submit" class="btn btn-success">Update</button>
        <a href="../admin_manage_inventory.php" class="btn btn-secondary">Cancel</a>
    </form>
</body>
</html>
