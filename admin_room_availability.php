<?php
// admin_room_availability.php
require 'db.php';

$errors = [];
$rooms  = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['from'], $_GET['to'])) {
    $from = $_GET['from'];
    $to   = $_GET['to'];
    $room_type = $_GET['room_type'] ?? '';

    if ($from === '' || $to === '') {
        $errors[] = 'From and To dates are required.';
    } else {
        try {
            $df = new DateTime($from);
            $dt = new DateTime($to);
            if ($df >= $dt) {
                $errors[] = 'To date must be after From date.';
            }
        } catch (Exception $e) {
            $errors[] = 'Invalid date format.';
        }
    }

    if (!$errors) {
        $sql = "
            SELECT hr.Room_Number, hr.Room_Type, hr.Num_Beds, hr.Base_Rate, hr.Housekeeper_Status
            FROM Hotel_Room hr
            WHERE (:rtype = '' OR hr.Room_Type = :rtype)
              AND hr.Room_Number NOT IN (
                SELECT Room_Number
                FROM Stays_In
                WHERE Status IN ('Checked In', 'Not Checked In')
                  AND Check_In_Date < :to
                  AND Check_Out_Date > :from
              )
            ORDER BY hr.Room_Number
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':rtype' => $room_type,
            ':from'  => $from,
            ':to'    => $to,
        ]);
        $rooms = $stmt->fetchAll();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Room Availability</title>
</head>
<body>
<h1>Room Availability</h1>

<?php if ($errors): ?>
    <div style="color:red;">
        <ul>
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="get" action="">
    <label>From:
        <input type="date" name="from" required
               value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">
    </label>
    <label>To:
        <input type="date" name="to" required
               value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
    </label>
    <label>Room Type:
        <select name="room_type">
            <option value="">Any</option>
            <?php
            $types = ['Single','Double','Suite','Deluxe','Presidential'];
            $sel = $_GET['room_type'] ?? '';
            foreach ($types as $t) {
                $selected = $t === $sel ? 'selected' : '';
                echo "<option $selected>" . htmlspecialchars($t) . "</option>";
            }
            ?>
        </select>
    </label>
    <button type="submit">Check</button>
</form>

<?php if ($rooms): ?>
    <h2>Available Rooms</h2>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
        <tr>
            <th>Room #</th>
            <th>Type</th>
            <th>Beds</th>
            <th>Base Rate</th>
            <th>Housekeeper Status</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rooms as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['Room_Number']) ?></td>
                <td><?= htmlspecialchars($r['Room_Type']) ?></td>
                <td><?= htmlspecialchars($r['Num_Beds']) ?></td>
                <td>$<?= number_format($r['Base_Rate'], 2) ?></td>
                <td><?= htmlspecialchars($r['Housekeeper_Status']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php elseif ($_GET): ?>
    <p>No available rooms for the selected criteria.</p>
<?php endif; ?>
</body>
</html>
