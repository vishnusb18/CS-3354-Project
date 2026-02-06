# StaySmart Hotel Management System

## Application Screenshots

### Guest Reservation Form (Before Submission)

![Guest Reservation Form](assets/guest_reservation_form_before.png)

### Guest Reservation Success

![Guest Reservation Success](assets/guest_reservation_success.png)

### Guest History Report (Before Check-In)

![Guest History Pre Check-In](assets/guest_history_pre_checkin.png)

### Check-In Success

![Check-In Success](assets/checkin_success.png)

### Guest History Report (After Check-In)

![Guest History Post Check-In](assets/guest_history_post_checkin.png)

## Project Structure

```
staysmart/
│
├── index.html
├── db.php
│
├── guest_reservation.php
├── payment_form.php
├── admin_room_availability.php
├── admin_checkin_checkout.php
├── reports.php
│
├── sql/
│   ├── sqlite_create.sql
│   └── sqlite_load.sql
│
└── data/
    ├── benefits_tier_discount.csv
    ├── guests.csv
    ├── employees.csv
    ├── hotel_rooms.csv
    ├── housekeepers.csv
    ├── loyalty_members.csv
    ├── regular_members.csv
    ├── stays_in.csv
    ├── payments.csv
    ├── receptionists.csv
    ├── administrators.csv
    └── accountants.csv
```

## 📦 Requirements

- **PHP 8+**
- **SQLite3** (built into macOS)
- **Project folders**:
  - `sql/sqlite_create.sql`
  - `sql/sqlite_load.sql`
  - `data/*.csv`
  - PHP pages and `db.php`

---

## 🍎 macOS Installation (Homebrew)

### 1️⃣ Install PHP

```bash
brew install php
```

### 2️⃣ Verify SQLite is installed

```bash
sqlite3 --version
```

### 3️⃣ Create the SQLite database

From project root:

```bash
sqlite3 sql/staysmart.db ".read sql/sqlite_create.sql"
```

### 4️⃣ Load your CSV data

```bash
sqlite3 sql/staysmart.db ".read sql/sqlite_load.sql"
```

⚠ **Ignore header-row errors** — these occur because `.import` tries to insert the header line, which violates CHECK constraints. Actual data loads correctly.

### 5️⃣ Run the PHP Server

```bash
php -S localhost:8000
```

Open the site:

```
http://localhost:8000
```

---

## 🪟 Windows Installation (XAMPP or Standalone PHP)

### 1️⃣ Install XAMPP

Download: https://www.apachefriends.org

OR install PHP directly via Windows Store.

### 2️⃣ Run database creation

```bat
sqlite3 sql\\staysmart.db ".read sql/sqlite_create.sql"
sqlite3 sql\\staysmart.db ".read sql/sqlite_load.sql"
```

### 3️⃣ If using XAMPP:

Place your project in:

```
C:\\xampp\\htdocs\\staysmart
```

Start Apache → Visit:

```
http://localhost/staysmart/
```

If using standalone PHP:

```bat
php -S localhost:8000
```

---

## 🔌 Database Configuration (db.php)

```php
<?php
$dbPath = __DIR__ . '/sql/staysmart.db';
$dsn = "sqlite:" . $dbPath;

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    exit("DB Connection failed: " . $e->getMessage());
}
?>
```

---

# 🧪 Testing the System

### ✔ Make a reservation

`guest_reservation.php`

### ✔ Check room availability

`admin_room_availability.php`

### ✔ Check-in / Check-out

`admin_checkin_checkout.php`

### ✔ Make payments

`payment_form.php`

### ✔ Run reports

`reports.php`

---

## ⚠ Troubleshooting

### “CHECK constraint failed” during CSV import

This is normal — the first row (header) fails. Data loads properly.

### “no such table”

Run:

```bash
sqlite3 sql/staysmart.db ".tables"
```

If empty → recreate DB and load again.

---

## course pals made for students by studnets

## Checking Push and Pull.....

## Checking push and pull by lalith

## Checking push and pull - Hansi
