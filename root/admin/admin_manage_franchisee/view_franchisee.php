<?php
require_once "../../../db/config.php";

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$id = intval($_GET['id']);

try {
    $sql = "SELECT * FROM franchisees WHERE franchisee_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $franchisee = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$franchisee) {
        die("Franchisee not found.");
    }
} catch (PDOException $e) {
    die("Error fetching franchisee: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Franchisee</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include('../../includes/header.php'); ?>
    <?php include('../../includes/admin_navbar.php'); ?>

    <div class="main-container">
        <main class="main-content">
            <h1 class="page-title">Franchisee Details</h1>

            <ul class="list-group">
                <li class="list-group-item"><strong>ID:</strong> #FR-<?php echo $franchisee['franchisee_id']; ?></li>
                <li class="list-group-item"><strong>Business Name:</strong> <?php echo htmlspecialchars($franchisee['business_name']); ?></li>
                <li class="list-group-item"><strong>Address:</strong> <?php echo htmlspecialchars($franchisee['address']); ?></li>
                <li class="list-group-item"><strong>ABN:</strong> <?php echo htmlspecialchars($franchisee['abn']); ?></li>
                <li class="list-group-item"><strong>Start Date:</strong> <?php echo htmlspecialchars($franchisee['start_date']); ?></li>
                <li class="list-group-item"><strong>Status:</strong> <?php echo htmlspecialchars($franchisee['status']); ?></li>
                <li class="list-group-item"><strong>Point of Contact:</strong> <?php echo htmlspecialchars($franchisee['point_of_contact']); ?></li>
                <li class="list-group-item"><strong>Phone:</strong> <?php echo htmlspecialchars($franchisee['phone']); ?></li>
                <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($franchisee['email']); ?></li>
            </ul>

            <a href="../admin_manage_franchisee.php" class="btn btn-secondary mt-3">Back</a>
        </main>
    </div>
</body>
</html>
