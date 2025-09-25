<?php
include('../../../db/config.php'); // $link connection

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report-type'])) {
    $reportType = $_POST['report-type'];
    $startDate = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $endDate   = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename={$reportType}_report_" . date('Y-m-d') . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    $output = fopen("php://output", "w");

    if ($reportType === 'inventory') {
        fputcsv($output, ['Product ID','Name','Description','Price','Stock Quantity','Status','Category'], "\t");
        $sql = "SELECT product_id, name, description, price, stock_quantity, status, category FROM products";
        $result = $link->query($sql);
        while ($row = $result->fetch_assoc()) fputcsv($output, $row, "\t");

    } elseif ($reportType === 'users') {
        fputcsv($output, ['User ID','Role','Name','Email','Phone','Status','Created At'], "\t");
        $sql = "SELECT user_id, role, name, email, phone, status, created_at FROM users";
        if ($startDate && $endDate) {
            $sql .= " WHERE DATE(created_at) BETWEEN '$startDate' AND '$endDate'";
        }
        $result = $link->query($sql);
        while ($row = $result->fetch_assoc()) fputcsv($output, $row, "\t");

    } elseif ($reportType === 'franchisees') {
        fputcsv($output, ['Franchisee ID','Business Name','Address','ABN','Start Date','Status','Contact','Phone','Email'], "\t");
        $sql = "SELECT franchisee_id, business_name, address, abn, start_date, status, point_of_contact, phone, email FROM franchisees";
        if ($startDate && $endDate) {
            $sql .= " WHERE start_date BETWEEN '$startDate' AND '$endDate'";
        }
        $result = $link->query($sql);
        while ($row = $result->fetch_assoc()) fputcsv($output, $row, "\t");

    } elseif ($reportType === 'bookings') {
        fputcsv($output, ['Booking ID','Franchisee ID','First Name','Last Name','Email','Phone','Location','Scheduled Date','Status','Notes'], "\t");
        $sql = "SELECT booking_id, franchisee_id, first_name, last_name, email, phone, preferred_location, scheduled_date, status, notes FROM bookings";
        if ($startDate && $endDate) {
            $sql .= " WHERE DATE(scheduled_date) BETWEEN '$startDate' AND '$endDate'";
        }
        $result = $link->query($sql);
        while ($row = $result->fetch_assoc()) fputcsv($output, $row, "\t");

    } else {
        echo "Invalid report type.";
    }

    fclose($output);
    exit;
}
?>
