<?php
// admin_checkin_checkout.php
require 'db.php';

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomRef = trim($_POST['ref'] ?? '');
    $guestId = trim($_POST['guest_id'] ?? '');
    $op      = $_POST['operation'] ?? '';

    if ($roomRef === '' || !ctype_digit($roomRef)) {
        $errors[] = 'Booking Ref must be a valid room number.';
    }
    if ($guestId === '') {
        $errors[] = 'Guest ID is required.';
    }
    if ($op !== 'checkin' && $op !== 'checkout') {
        $errors[] = 'Unknown operation.';
    }

    if (!$errors) {
        $roomNo = (int)$roomRef;
        $status = $op === 'checkin' ? 'Checked In' : 'Checked Out';

        // Ensure record exists
        $stmt = $pdo->prepare("
            SELECT *
            FROM Stays_In
            WHERE Guest_ID = :gid AND Room_Number = :room
        ");
        $stmt->execute([
            ':gid'  => $guestId,
            ':room' => $roomNo,
        ]);
        $stay = $stmt->fetch();

        if (!$stay) {
            $errors[] = 'No stay found for that Guest ID and Room.';
        } else {
            $stmtU = $pdo->prepare("
                UPDATE Stays_In
                SET Status = :st
                WHERE Guest_ID = :gid AND Room_Number = :room
            ");
            $stmtU->execute([
                ':st'   => $status,
                ':gid'  => $guestId,
                ':room' => $roomNo,
            ]);
            $success = "Status updated to {$status} for guest {$guestId}, room {$roomNo}.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Check-in / Check-out</title>
</head>
<body>
<h1>Check-in / Check-out</h1>

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
    <label>Booking Ref (Room #):
        <input name="ref" required value="<?= htmlspecialchars($_POST['ref'] ?? '') ?>">
    </label>
    <label>Guest ID:
        <input name="guest_id" required value="<?= htmlspecialchars($_POST['guest_id'] ?? '') ?>">
    </label>
    <button type="submit" name="operation" value="checkin">Check In</button>
    <button type="submit" name="operation" value="checkout">Check Out</button>
</form>
</body>
</html>
