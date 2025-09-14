<?php
// create_product.php
require_once "../../../db/config.php";

$name = $description = $price = $stock_quantity = $category = $image = "";
$name_err = $price_err = $stock_err = $category_err = $image_err = "";

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
        $price = trim($_POST["price"]);
    }

    // Validate stock
    if (empty(trim($_POST["stock_quantity"]))) {
        $stock_err = "Please enter stock quantity.";
    } elseif (!ctype_digit($_POST["stock_quantity"])) {
        $stock_err = "Stock must be a number.";
    } else {
        $stock_quantity = (int) $_POST["stock_quantity"];
    }

    // Validate category
    $valid_categories = ['equipment', 'safety gear', 'uniforms', 'consumables'];
    $category = trim($_POST["category"]);
    if (!in_array($category, $valid_categories)) {
        $category_err = "Please select a valid category.";
    }

    // Description (optional)
    $description = trim($_POST["description"]);

    // Image upload
    if (!empty($_FILES["product_image"]["name"])) {
        $target_dir = "../../../assets/images/producst/";
        $image_name = time() . "_" . basename($_FILES["product_image"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check file type
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($imageFileType, $allowed_types)) {
            $image_err = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        } else {
            if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                $image = $image_name;
            } else {
                $image_err = "Error uploading image.";
            }
        }
    }

    // If no errors, insert into DB
    if (empty($name_err) && empty($price_err) && empty($stock_err) && empty($category_err) && empty($image_err)) {
        try {
            $sql = "INSERT INTO products (name, description, price, stock_quantity, category, image_path) 
                    VALUES (:name, :description, :price, :stock_quantity, :category, :image_path)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':price', $price, PDO::PARAM_STR);
            $stmt->bindParam(':stock_quantity', $stock_quantity, PDO::PARAM_INT);
            $stmt->bindParam(':category', $category, PDO::PARAM_STR);
            $stmt->bindParam(':image_path', $image, PDO::PARAM_STR);

            if ($stmt->execute()) {
                header("location: ../admin_manage_inventory.php");
                exit();
            } else {
                echo "Error: Could not save product.";
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
    <title>Create Product</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>Add New Product</h2>
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label>Product Name</label>
            <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name); ?>">
            <span class="invalid-feedback"><?php echo $name_err; ?></span>
        </div>
        <div class="form-group">
            <label>Description (optional)</label>
            <textarea name="description" class="form-control"><?php echo htmlspecialchars($description); ?></textarea>
        </div>
        <div class="form-group">
            <label>Unit Price</label>
            <input type="text" name="price" class="form-control <?php echo (!empty($price_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($price); ?>">
            <span class="invalid-feedback"><?php echo $price_err; ?></span>
        </div>
        <div class="form-group">
            <label>Stock Quantity</label>
            <input type="text" name="stock_quantity" class="form-control <?php echo (!empty($stock_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($stock_quantity); ?>">
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
            <label>Product Image</label>
            <input type="file" name="product_image" class="form-control-file <?php echo (!empty($image_err)) ? 'is-invalid' : ''; ?>">
            <span class="invalid-feedback d-block"><?php echo $image_err; ?></span>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="../admin_manage_inventory.php" class="btn btn-secondary">Cancel</a>
    </form>
</body>
</html>
