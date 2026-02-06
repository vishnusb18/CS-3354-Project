<?php
// db.php — SQLite version

// Path to your SQLite database file (relative to this file)
$dbPath = __DIR__ . '/sql/staysmart.db';

$dsn = "sqlite:" . $dbPath;

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    exit('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}

/**
 * Generate next Guest_ID like G051
 * Uses SQLite-friendly SUBSTR + CAST
 */
function generateGuestId(PDO $pdo): string {
    $sql = "SELECT MAX(CAST(SUBSTR(Guest_ID, 2) AS INTEGER)) AS max_id FROM Guest";
    $row = $pdo->query($sql)->fetch();
    $next = (int)($row['max_id'] ?? 0) + 1;
    return 'G' . str_pad((string)$next, 3, '0', STR_PAD_LEFT);
}
