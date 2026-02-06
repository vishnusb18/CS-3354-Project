<?php
// guest_reservation.php
require 'db.php';

$errors = [];
$success = null;
$assignedRoom = null;
$generatedGuestId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $check_in   = $_POST['check_in'] ?? '';
    $check_out  = $_POST['check_out'] ?? '';
    $room_type  = $_POST['room_type'] ?? '';
    $guests     = (int)($_POST['guests'] ?? 1);

    // Basic validation
    if ($first_name === '') $errors[] = 'First name is required.';
    if ($last_name === '')  $errors[] = 'Last name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if ($check_in === '' || $check_out === '') $errors[] = 'Check-in and check-out dates are required.';
    if ($room_type === '') $errors[] = 'Room type is required.';
    if ($guests < 1) $errors[] = 'Number of guests must be at least 1.';

    if (!$errors) {
        try {
            $ci = new DateTime($check_in);
            $co = new DateTime($check_out);
            if ($ci >= $co) {
                $errors[] = 'Check-out date must be after check-in date.';
            }
        } catch (Exception $e) {
            $errors[] = 'Invalid date format.';
        }
    }

    if (!$errors) {
        // Find an available room of requested type
        $sql = "
            SELECT hr.Room_Number, hr.Base_Rate
            FROM Hotel_Room hr
            WHERE hr.Room_Type = :room_type
              AND hr.Room_Number NOT IN (
                SELECT Room_Number
                FROM Stays_In
                WHERE Status IN ('Checked In', 'Not Checked In')
                  AND Check_In_Date < :check_out
                  AND Check_Out_Date > :check_in
              )
            ORDER BY hr.Room_Number
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':room_type'  => $room_type,
            ':check_in'   => $check_in,
            ':check_out'  => $check_out,
        ]);
        $room = $stmt->fetch();

        if (!$room) {
            $errors[] = 'No available rooms for the selected type and dates.';
        } else {
            $pdo->beginTransaction();
            try {
                $assignedRoom = (int)$room['Room_Number'];
                $baseRate     = (float)$room['Base_Rate'];

                // nights = difference in days
                $nights = $ci->diff($co)->days;
                if ($nights < 1) $nights = 1;
                $amountDue = $baseRate * $nights;

                // Create new Guest
                $generatedGuestId = generateGuestId($pdo);
                $insertGuest = "
                    INSERT INTO Guest
                    (Guest_ID, First_Name, Last_Name, Amount_Due, Payment_Type,
                     Phone_Number, Email_Address, Address)
                    VALUES
                    (:gid, :fn, :ln, :amt, NULL, :phone, :email, NULL)
                ";
                $stmtG = $pdo->prepare($insertGuest);
                $stmtG->execute([
                    ':gid'   => $generatedGuestId,
                    ':fn'    => $first_name,
                    ':ln'    => $last_name,
                    ':amt'   => $amountDue,
                    ':phone' => $phone ?: null,
                    ':email' => $email,
                ]);

                // Insert stay record
                $insertStay = "
                    INSERT INTO Stays_In
                    (Guest_ID, Room_Number, Check_In_Date, Check_Out_Date, Status)
                    VALUES
                    (:gid, :room, :ci, :co, 'Not Checked In')
                ";
                $stmtS = $pdo->prepare($insertStay);
                $stmtS->execute([
                    ':gid'  => $generatedGuestId,
                    ':room' => $assignedRoom,
                    ':ci'   => $check_in,
                    ':co'   => $check_out,
                ]);

                $pdo->commit();
                $success = "Reservation successful! 
                    Your Guest ID (booking reference) is {$generatedGuestId}, 
                    Room {$assignedRoom}, Amount Due \$" . number_format($amountDue, 2);
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = 'Reservation failed: ' . htmlspecialchars($e->getMessage());
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Make a Reservation</title>
</head>
<body>
<h1>Guest Reservation Form</h1>

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
        <?= nl2br(htmlspecialchars($success)) ?>
    </div>
<?php endif; ?>

<form method="post" action="">
    <label>First name:
        <input type="text" name="first_name" required
               value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
    </label><br>
    <label>Last name:
        <input type="text" name="last_name" required
               value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
    </label><br>
    <label>Email:
        <input type="email" name="email" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </label><br>
    <label>Phone:
        <input type="tel" name="phone"
               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
    </label><br>
    <label>Check-in:
        <input type="date" name="check_in" required
               value="<?= htmlspecialchars($_POST['check_in'] ?? '') ?>">
    </label><br>
    <label>Check-out:
        <input type="date" name="check_out" required
               value="<?= htmlspecialchars($_POST['check_out'] ?? '') ?>">
    </label><br>
    <label>Room type:
        <select name="room_type" required>
            <?php
            $types = ['Single','Double','Suite','Deluxe','Presidential'];
            $sel = $_POST['room_type'] ?? '';
            foreach ($types as $t) {
                $selected = $t === $sel ? 'selected' : '';
                echo "<option $selected>" . htmlspecialchars($t) . "</option>";
            }
            ?>
        </select>
    </label><br>
    <label>Number of guests:
        <input type="number" name="guests" min="1"
               value="<?= htmlspecialchars($_POST['guests'] ?? '1') ?>">
    </label><br>
    <button type="submit">Book Now</button>
</form>
</body>
</html>
