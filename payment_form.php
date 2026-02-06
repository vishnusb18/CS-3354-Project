<?php
// payment_form.php
require 'db.php';

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ref        = trim($_POST['ref'] ?? '');   // Guest_ID
    $card_name  = trim($_POST['card_name'] ?? '');
    $card_num   = preg_replace('/\D+/', '', $_POST['card_number'] ?? '');
    $expiry     = $_POST['expiry'] ?? '';
    $cvv        = trim($_POST['cvv'] ?? '');
    $amount     = (float)($_POST['amount'] ?? 0);

    if ($ref === '') $errors[] = 'Booking reference (Guest ID) is required.';
    if ($card_name === '') $errors[] = 'Cardholder name is required.';
    if (strlen($card_num) < 12) $errors[] = 'Card number appears invalid.';
    if ($expiry === '') $errors[] = 'Expiry is required.';
    if (strlen($cvv) < 3) $errors[] = 'CVV appears invalid.';
    if ($amount <= 0) $errors[] = 'Amount must be greater than 0.';

    // Check guest exists
    if (!$errors) {
        $stmt = $pdo->prepare("SELECT Amount_Due FROM Guest WHERE Guest_ID = :gid");
        $stmt->execute([':gid' => $ref]);
        $guest = $stmt->fetch();
        if (!$guest) {
            $errors[] = 'Guest ID / booking reference not found.';
        }
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            // Next invoice number
            $stmt = $pdo->query("SELECT IFNULL(MAX(Invoice_Number), 1000) + 1 AS nextInv FROM Makes_Payment");
            $row  = $stmt->fetch();
            $invoice = (int)$row['nextInv'];

            $insert = "
                INSERT INTO Makes_Payment
                (Guest_ID, Invoice_Number, Payment_Amount, Payment_Date, Payment_Method)
                VALUES
                (:gid, :inv, :amt, DATE('now'), 'Card')
            ";
            $stmtIns = $pdo->prepare($insert);
            $stmtIns->execute([
                ':gid' => $ref,
                ':inv' => $invoice,
                ':amt' => $amount,
            ]);

            // Update Amount_Due
            $newDue = max(0, (float)$guest['Amount_Due'] - $amount);
            $stmtUpd = $pdo->prepare("UPDATE Guest SET Amount_Due = :due, Payment_Type = 'Card' WHERE Guest_ID = :gid");
            $stmtUpd->execute([
                ':due' => $newDue,
                ':gid' => $ref,
            ]);

            $pdo->commit();
            $success = "Payment recorded. Invoice #{$invoice}. New balance: \$" . number_format($newDue, 2);
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Payment failed: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payment</title>
</head>
<body>
<h1>Secure Payment Form</h1>

<?php if ($errors): ?>
    <div style="color:red;">
        <ul>
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div style="color:green; font-weight:bold;">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<form method="post" action="">
    <label>Booking Reference (Guest ID):
        <input type="text" name="ref" required
               value="<?= htmlspecialchars($_POST['ref'] ?? '') ?>">
    </label><br><br>
    <label>Cardholder Name:
        <input type="text" name="card_name" required
               value="<?= htmlspecialchars($_POST['card_name'] ?? '') ?>">
    </label><br><br>
    <label>Card Number:
        <input type="text" name="card_number" required>
    </label><br><br>
    <label>Expiry:
        <input type="month" name="expiry" required
               value="<?= htmlspecialchars($_POST['expiry'] ?? '') ?>">
    </label><br><br>
    <label>CVV:
        <input type="password" name="cvv" maxlength="4" required>
    </label><br><br>
    <label>Amount:
        <input type="number" step="0.01" name="amount" required
               value="<?= htmlspecialchars($_POST['amount'] ?? '') ?>">
    </label><br><br>
    <button type="submit">Pay</button>
</form>
</body>
</html>
