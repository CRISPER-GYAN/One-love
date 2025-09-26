<?php
require 'db.php';
if (!isset($_GET['invoice_id'])) {
    die('Invoice not specified.');
}
$invoice_id = intval($_GET['invoice_id']);
$invoice = $conn->query("SELECT i.*, f.amount, f.date, s.name as student_name FROM invoices i JOIN fees f ON i.fee_id = f.id JOIN students s ON f.student_id = s.id WHERE i.id = $invoice_id")->fetch_assoc();
if (!$invoice) {
    die('Invoice not found.');
}
// Generate PDF receipt
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>School Fees Receipt</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .receipt { max-width: 500px; margin: 0 auto; border: 1px solid #ccc; padding: 24px; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <div class="receipt">
        <h2>School Fees Receipt</h2>
        <p><strong>Invoice Number:</strong> <?= htmlspecialchars($invoice['invoice_number']) ?></p>
        <p><strong>Student Name:</strong> <?= htmlspecialchars($invoice['student_name']) ?></p>
        <p><strong>Amount Paid:</strong> <?= number_format($invoice['amount'], 2) ?></p>
        <p><strong>Date:</strong> <?= $invoice['date'] ?></p>
        <p><strong>Receipt Generated:</strong> <?= $invoice['created_at'] ?></p>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();
if (isset($_GET['download']) && $_GET['download'] == 1) {
    require_once __DIR__ . '/vendor/autoload.php';
    $mpdf = new \Mpdf\Mpdf();
    $mpdf->WriteHTML($html);
    $mpdf->Output('receipt.pdf', 'D');
    exit();
}
echo $html;
