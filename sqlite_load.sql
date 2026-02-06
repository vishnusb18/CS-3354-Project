-- =====================================================
-- StaySmart Hotel Booking System
-- sqlite_load.sql - Data Loading Script (SQLite version)
-- Phase 3 - Task C
-- =====================================================

PRAGMA foreign_keys = OFF;

-- Optional: clear existing data (order: children → parents)
DELETE FROM Accountant;
DELETE FROM Administrator;
DELETE FROM Receptionist;
DELETE FROM Makes_Payment;
DELETE FROM Stays_In;
DELETE FROM Regular_Member;
DELETE FROM Loyalty_Member;
DELETE FROM Hotel_Room;
DELETE FROM Housekeeper;
DELETE FROM Employee;
DELETE FROM Benefits_Tier_Discount;
DELETE FROM Guest;

-- =====================================================
-- CSV import settings
-- Run with: sqlite3 sql/staysmart.db ".read sql/sqlite_load.sql"
-- CSV files are expected under ../data relative to this script,
-- but since we usually run from project root, we use "data/...".
-- =====================================================

.mode csv

-- =====================================================
-- Load Benefits_Tier_Discount (must load first)
-- File: data/benefits_tier_discount.csv
-- Columns: Benefits_Tier, Discount_Rate
-- =====================================================
.import data/benefits_tier_discount.csv Benefits_Tier_Discount

-- Remove header row (assuming header "Benefits_Tier")
DELETE FROM Benefits_Tier_Discount WHERE Benefits_Tier = 'Benefits_Tier';

-- =====================================================
-- Load Guest Data
-- File: data/guests.csv
-- Columns: Guest_ID, First_Name, MI, Last_Name,
--          Amount_Due, Payment_Type, Phone_Number,
--          Email_Address, Address
-- =====================================================
.import data/guests.csv Guest

-- Remove header row (assuming header "Guest_ID")
DELETE FROM Guest WHERE Guest_ID = 'Guest_ID';

-- =====================================================
-- Load Employee Data
-- File: data/employees.csv
-- Columns: eID, First_Name, MI, Last_Name, eSSN, eCategory, payRate
-- =====================================================
.import data/employees.csv Employee

-- Remove header row
DELETE FROM Employee WHERE eID = 'eID';

-- =====================================================
-- Load Housekeeper Data (depends on Employee)
-- File: data/housekeepers.csv
-- Columns: eID, Assigned_Rooms, Cleaning_Status, Shift_Area
-- =====================================================
.import data/housekeepers.csv Housekeeper

-- Remove header row
DELETE FROM Housekeeper WHERE eID = 'eID';

-- =====================================================
-- Load Hotel_Room Data (depends on Housekeeper)
-- File: data/hotel_rooms.csv
-- Columns: Room_Number, Room_Type, Num_Beds, Base_Rate,
--          Assigned_Housekeeper, Housekeeper_Status
-- =====================================================
.import data/hotel_rooms.csv Hotel_Room

-- Remove header row
DELETE FROM Hotel_Room WHERE Room_Number = 'Room_Number';

-- =====================================================
-- Load Loyalty_Member Data (depends on Guest, Benefits_Tier_Discount)
-- File: data/loyalty_members.csv
-- Columns: Guest_ID, Loyalty_ID, Benefits_Tier, Exp_Date
-- =====================================================
.import data/loyalty_members.csv Loyalty_Member

-- Remove header row
DELETE FROM Loyalty_Member WHERE Guest_ID = 'Guest_ID';

-- =====================================================
-- Load Regular_Member Data (depends on Guest)
-- File: data/regular_members.csv
-- Columns: Guest_ID
-- =====================================================
.import data/regular_members.csv Regular_Member

-- Remove header row
DELETE FROM Regular_Member WHERE Guest_ID = 'Guest_ID';

-- =====================================================
-- Load Stays_In Data (depends on Guest and Hotel_Room)
-- File: data/stays_in.csv
-- Columns: Guest_ID, Room_Number, Check_In_Date, Check_Out_Date, Status
-- =====================================================
.import data/stays_in.csv Stays_In

-- Remove header row
DELETE FROM Stays_In WHERE Guest_ID = 'Guest_ID';

-- =====================================================
-- Load Makes_Payment Data (depends on Guest)
-- File: data/payments.csv
-- Columns: Guest_ID, Invoice_Number, Payment_Amount,
--          Payment_Date, Payment_Method
-- =====================================================
.import data/payments.csv Makes_Payment

-- Remove header row
DELETE FROM Makes_Payment WHERE Guest_ID = 'Guest_ID';

-- =====================================================
-- Load Receptionist Data (depends on Employee)
-- File: data/receptionists.csv
-- Columns: eID, Shift_Time, Desk_Number, Check_In_Count
-- =====================================================
.import data/receptionists.csv Receptionist

-- Remove header row
DELETE FROM Receptionist WHERE eID = 'eID';

-- =====================================================
-- Load Administrator Data (depends on Employee)
-- File: data/administrators.csv
-- Columns: eID, Privilege_Level, Report_Access, Managed_Department
-- =====================================================
.import data/administrators.csv Administrator

-- Remove header row
DELETE FROM Administrator WHERE eID = 'eID';

-- =====================================================
-- Load Accountant Data (depends on Employee)
-- File: data/accountants.csv
-- Columns: eID, Invoices_Processed, Last_Audit_Date,
--          Payment_Clearance_Limit
-- =====================================================
.import data/accountants.csv Accountant

-- Remove header row
DELETE FROM Accountant WHERE eID = 'eID';

-- Re-enable foreign key checks
PRAGMA foreign_keys = ON;

-- =====================================================
-- Verify Data Loading
-- =====================================================
SELECT 'Data Loading Summary:' AS Status;
SELECT COUNT(*) AS "Total Guests"          FROM Guest;
SELECT COUNT(*) AS "Loyalty Members"       FROM Loyalty_Member;
SELECT COUNT(*) AS "Regular Members"       FROM Regular_Member;
SELECT COUNT(*) AS "Total Employees"       FROM Employee;
SELECT COUNT(*) AS "Hotel Rooms"           FROM Hotel_Room;
SELECT COUNT(*) AS "Active Stays"          FROM Stays_In;
SELECT COUNT(*) AS "Payment Transactions"  FROM Makes_Payment;
SELECT COUNT(*) AS "Housekeepers"          FROM Housekeeper;
SELECT COUNT(*) AS "Receptionists"         FROM Receptionist;
SELECT COUNT(*) AS "Administrators"        FROM Administrator;
SELECT COUNT(*) AS "Accountants"           FROM Accountant;

-- =====================================================
-- END OF sqlite_load.sql
-- =====================================================
