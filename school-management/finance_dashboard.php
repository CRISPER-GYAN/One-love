<?php
session_start();
require 'db.php';
$settings = $conn->query('SELECT * FROM settings LIMIT 1')->fetch_assoc();
$finance_managers = isset($settings['finance_managers']) ? json_decode($settings['finance_managers'], true) : [];
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'finance' || !in_array($_SESSION['user_name'], $finance_managers)) {
    header('Location: login.php');
    exit();
}
// Handle payment approval and invoice generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve'])) {
    $fee_id = intval($_POST['fee_id']);
    // Mark payment as approved
    $conn->query("UPDATE fees SET status='Paid' WHERE id=$fee_id");
    // Generate invoice number
    $invoice_number = 'INV-' . strtoupper(uniqid());
    $created_at = date('Y-m-d H:i:s');
    // Insert invoice
    $stmt = $conn->prepare('INSERT INTO invoices (fee_id, invoice_number, created_at) VALUES (?, ?, ?)');
    $stmt->bind_param('iss', $fee_id, $invoice_number, $created_at);
    $stmt->execute();
    $stmt->close();
    $success = 'Payment approved and invoice generated.';
}
// List all pending payments
$pending = $conn->query('SELECT f.*, s.name as student_name FROM fees f JOIN students s ON f.student_id = s.id WHERE f.status != "Paid"');
// List all paid payments with invoice
$paid = $conn->query('SELECT f.*, s.name as student_name, i.id as invoice_id, i.invoice_number FROM fees f JOIN students s ON f.student_id = s.id JOIN invoices i ON f.id = i.fee_id WHERE f.status = "Paid"');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Finance Manager Dashboard</title>
</head>
<body>
    <h2>Finance Manager Dashboard</h2>
    <?php if (!empty($success)) echo '<p style="color:green;">'.$success.'</p>'; ?>
    <h3>Pending Payments</h3>
    <table border="1">
        <tr><th>Student</th><th>Amount</th><th>Date</th><th>Status</th><th>Action</th></tr>
        <?php while ($row = $pending->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['student_name']) ?></td>
            <td><?= number_format($row['amount'], 2) ?></td>
            <td><?= $row['date'] ?></td>
            <td><?= $row['status'] ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="fee_id" value="<?= $row['id'] ?>">
                    <button type="submit" name="approve">Approve & Generate Invoice</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <h3>Paid Payments & Receipts</h3>
    <table border="1">
        <tr><th>Student</th><th>Amount</th><th>Date</th><th>Invoice</th><th>Download Receipt</th></tr>
        <?php while ($row = $paid->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['student_name']) ?></td>
            <td><?= number_format($row['amount'], 2) ?></td>
            <td><?= $row['date'] ?></td>
            <td><?= htmlspecialchars($row['invoice_number']) ?></td>
            <td><a href="receipt.php?invoice_id=<?= $row['invoice_id'] ?>&download=1">Download PDF</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <p><a href="logout.php">Logout</a></p>
</body>
</html>
