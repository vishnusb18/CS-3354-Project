<?php
// reports.php
require 'db.php';

$errors = [];
$reportType = $_GET['type'] ?? '';
$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';
$data = [];

if ($_GET) {
    if ($reportType === '') $errors[] = 'Report type is required.';

    if ($from === '' || $to === '') {
        $errors[] = 'From and To dates are required.';
    } else {
        try {
            $df = new DateTime($from);
            $dt = new DateTime($to);
            if ($df > $dt) {
                $errors[] = 'To date must be on or after From date.';
            }
        } catch (Exception $e) {
            $errors[] = 'Invalid date format.';
        }
    }

    if (!$errors) {
        if ($reportType === 'Occupancy') {
            $sql = "
                SELECT s.Guest_ID, s.Room_Number, s.Check_In_Date, s.Check_Out_Date, s.Status
                FROM Stays_In s
                WHERE s.Check_In_Date < :to
                  AND s.Check_Out_Date > :from
                ORDER BY s.Room_Number, s.Check_In_Date
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':from' => $from, ':to' => $to]);
            $data = $stmt->fetchAll();
        } elseif ($reportType === 'Revenue Summary') {
            $sql = "
                SELECT COUNT(*) AS txn_count,
                       SUM(Payment_Amount) AS total_revenue
                FROM Makes_Payment
                WHERE Payment_Date BETWEEN :from AND :to
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':from' => $from, ':to' => $to]);
            $data = $stmt->fetch() ?: [];
        } elseif ($reportType === 'Guest History') {
            $sql = "
                SELECT g.Guest_ID, g.First_Name, g.Last_Name,
                       s.Room_Number, s.Check_In_Date, s.Check_Out_Date, s.Status
                FROM Guest g
                JOIN Stays_In s ON g.Guest_ID = s.Guest_ID
                WHERE s.Check_In_Date BETWEEN :from AND :to
                ORDER BY g.Guest_ID, s.Check_In_Date
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':from' => $from, ':to' => $to]);
            $data = $stmt->fetchAll();
        } else {
            $errors[] = 'Unknown report type selected.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Reports</title>
</head>
<body>
<h1>Reports</h1>

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
    <label>Report Type:
        <select name="type">
            <?php
            $types = ['Occupancy','Revenue Summary','Guest History'];
            foreach ($types as $t) {
                $selected = ($t === ($reportType ?: '')) ? 'selected' : '';
                echo "<option $selected>" . htmlspecialchars($t) . "</option>";
            }
            ?>
        </select>
    </label>
    <label>From:
        <input type="date" name="from" value="<?= htmlspecialchars($from) ?>">
    </label>
    <label>To:
        <input type="date" name="to" value="<?= htmlspecialchars($to) ?>">
    </label>
    <button type="submit">Generate</button>
</form>

<div id="reportResults">
    <?php if (!$errors && $_GET && $reportType === 'Occupancy'): ?>
        <h2>Occupancy Report</h2>
        <?php if ($data): ?>
            <table border="1" cellpadding="5" cellspacing="0">
                <thead>
                <tr>
                    <th>Guest ID</th>
                    <th>Room #</th>
                    <th>Check-In</th>
                    <th>Check-Out</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['Guest_ID']) ?></td>
                        <td><?= htmlspecialchars($row['Room_Number']) ?></td>
                        <td><?= htmlspecialchars($row['Check_In_Date']) ?></td>
                        <td><?= htmlspecialchars($row['Check_Out_Date']) ?></td>
                        <td><?= htmlspecialchars($row['Status']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No stays in this date range.</p>
        <?php endif; ?>
    <?php elseif (!$errors && $_GET && $reportType === 'Revenue Summary'): ?>
        <h2>Revenue Summary</h2>
        <p>Transactions: <?= (int)($data['txn_count'] ?? 0) ?></p>
        <p>Total Revenue: $<?= number_format((float)($data['total_revenue'] ?? 0), 2) ?></p>
    <?php elseif (!$errors && $_GET && $reportType === 'Guest History'): ?>
        <h2>Guest History</h2>
        <?php if ($data): ?>
            <table border="1" cellpadding="5" cellspacing="0">
                <thead>
                <tr>
                    <th>Guest ID</th>
                    <th>Name</th>
                    <th>Room #</th>
                    <th>Check-In</th>
                    <th>Check-Out</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['Guest_ID']) ?></td>
                        <td><?= htmlspecialchars($row['First_Name'] . ' ' . $row['Last_Name']) ?></td>
                        <td><?= htmlspecialchars($row['Room_Number']) ?></td>
                        <td><?= htmlspecialchars($row['Check_In_Date']) ?></td>
                        <td><?= htmlspecialchars($row['Check_Out_Date']) ?></td>
                        <td><?= htmlspecialchars($row['Status']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No guest stays in this date range.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
