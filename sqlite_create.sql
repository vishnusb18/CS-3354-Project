-- =====================================================
-- StaySmart Hotel Booking System
-- =====================================================

PRAGMA foreign_keys = ON;

-- =====================================================
-- Table 1: Guest
-- Stores all guest information
-- =====================================================
CREATE TABLE Guest (
    Guest_ID       VARCHAR(20) PRIMARY KEY,
    First_Name     VARCHAR(50)  DEFAULT NULL,
    MI             CHAR(1)      DEFAULT NULL,
    Last_Name      VARCHAR(50)  DEFAULT NULL,
    Amount_Due     DECIMAL(10, 2) DEFAULT 0.00,
    Payment_Type   VARCHAR(10)  DEFAULT NULL,
    Phone_Number   VARCHAR(15)  DEFAULT NULL,
    Email_Address  VARCHAR(100) DEFAULT NULL,
    Address        VARCHAR(200) DEFAULT NULL,
    CONSTRAINT chk_payment_type
        CHECK (Payment_Type IN ('Cash', 'Card', 'Check', NULL))
);

-- =====================================================
-- Table 2: Benefits_Tier_Discount (NEW - Normalized)
-- Stores discount rates for each loyalty tier
-- =====================================================
CREATE TABLE Benefits_Tier_Discount (
    Benefits_Tier INT PRIMARY KEY,
    Discount_Rate INT NOT NULL,
    CONSTRAINT chk_tier_range
        CHECK (Benefits_Tier BETWEEN 1 AND 5),
    CONSTRAINT chk_discount_range
        CHECK (Discount_Rate BETWEEN 0 AND 100)
);

-- =====================================================
-- Table 3: Loyalty_Member (Normalized - Removed Discount_Rate)
-- Stores loyalty member information
-- =====================================================
CREATE TABLE Loyalty_Member (
    Guest_ID      VARCHAR(20),
    Loyalty_ID    VARCHAR(20),
    Benefits_Tier INT DEFAULT 1,
    Exp_Date      DATE,
    PRIMARY KEY (Guest_ID, Loyalty_ID),
    FOREIGN KEY (Guest_ID)
        REFERENCES Guest(Guest_ID)
        ON DELETE CASCADE,
    FOREIGN KEY (Benefits_Tier)
        REFERENCES Benefits_Tier_Discount(Benefits_Tier)
);

-- =====================================================
-- Table 4: Regular_Member
-- Stores regular (non-loyalty) member information
-- =====================================================
CREATE TABLE Regular_Member (
    Guest_ID VARCHAR(20) PRIMARY KEY,
    FOREIGN KEY (Guest_ID)
        REFERENCES Guest(Guest_ID)
        ON DELETE CASCADE
);

-- =====================================================
-- Table 5: Employee
-- Stores all employee information
-- =====================================================
CREATE TABLE Employee (
    eID        VARCHAR(20) PRIMARY KEY,
    First_Name VARCHAR(50) DEFAULT NULL,
    MI         CHAR(1)     DEFAULT NULL,
    Last_Name  VARCHAR(50) DEFAULT NULL,
    eSSN       VARCHAR(11) NOT NULL,
    eCategory  VARCHAR(20) NOT NULL,
    payRate    DECIMAL(10, 2) NOT NULL,
    CONSTRAINT chk_category
        CHECK (eCategory IN ('Receptionist', 'Administrator', 'Accountant', 'Housekeeper'))
);

-- =====================================================
-- Table 6: Housekeeper
-- Stores housekeeper-specific information
-- =====================================================
CREATE TABLE Housekeeper (
    eID             VARCHAR(20) PRIMARY KEY,
    Assigned_Rooms  VARCHAR(100) DEFAULT NULL,
    Cleaning_Status VARCHAR(20)  DEFAULT 'Not Cleaned',
    Shift_Area      VARCHAR(50)  DEFAULT NULL,
    FOREIGN KEY (eID)
        REFERENCES Employee(eID)
        ON DELETE CASCADE,
    CONSTRAINT chk_cleaning_status
        CHECK (Cleaning_Status IN ('Cleaned', 'Not Cleaned'))
);

-- =====================================================
-- Table 7: Hotel_Room
-- Stores room information
-- =====================================================
CREATE TABLE Hotel_Room (
    Room_Number         INT PRIMARY KEY,
    Room_Type           VARCHAR(50) NOT NULL,
    Num_Beds            INT DEFAULT 1,
    Base_Rate           DECIMAL(10, 2) DEFAULT 50.00,
    Assigned_Housekeeper VARCHAR(20),
    Housekeeper_Status  VARCHAR(20) DEFAULT 'Not Cleaned',
    FOREIGN KEY (Assigned_Housekeeper)
        REFERENCES Housekeeper(eID)
        ON DELETE RESTRICT,
    CONSTRAINT chk_num_beds
        CHECK (Num_Beds > 0),
    CONSTRAINT chk_base_rate
        CHECK (Base_Rate >= 0),
    CONSTRAINT chk_housekeeper_status
        CHECK (Housekeeper_Status IN ('Cleaned', 'Not Cleaned'))
);

-- =====================================================
-- Table 8: Stays_In
-- Stores guest room assignment and stay information
-- =====================================================
CREATE TABLE Stays_In (
    Guest_ID      VARCHAR(20),
    Room_Number   INT,
    Check_In_Date  DATE NOT NULL,
    Check_Out_Date DATE NOT NULL,
    Status        VARCHAR(20) NOT NULL,
    PRIMARY KEY (Guest_ID, Room_Number),
    FOREIGN KEY (Guest_ID)
        REFERENCES Guest(Guest_ID)
        ON DELETE CASCADE,
    FOREIGN KEY (Room_Number)
        REFERENCES Hotel_Room(Room_Number)
        ON DELETE CASCADE,
    CONSTRAINT chk_dates
        CHECK (Check_In_Date < Check_Out_Date),
    CONSTRAINT chk_status
        CHECK (Status IN ('Checked In', 'Checked Out', 'Not Checked In'))
);

-- =====================================================
-- Table 9: Makes_Payment
-- Stores payment transaction information
-- =====================================================
CREATE TABLE Makes_Payment (
    Guest_ID       VARCHAR(20),
    Invoice_Number INT,
    Payment_Amount DECIMAL(10, 2) NOT NULL,
    Payment_Date   DATE DEFAULT NULL,
    Payment_Method VARCHAR(10) DEFAULT 'Card',
    PRIMARY KEY (Guest_ID, Invoice_Number),
    FOREIGN KEY (Guest_ID)
        REFERENCES Guest(Guest_ID)
        ON DELETE CASCADE,
    CONSTRAINT chk_payment_amount
        CHECK (Payment_Amount >= 0),
    CONSTRAINT chk_payment_method
        CHECK (Payment_Method IN ('Cash', 'Card', 'Check'))
);

-- =====================================================
-- Table 10: Receptionist
-- Stores receptionist-specific information
-- =====================================================
CREATE TABLE Receptionist (
    eID            VARCHAR(20) PRIMARY KEY,
    Shift_Time     TIME NOT NULL,
    Desk_Number    INT DEFAULT 1,
    Check_In_Count INT DEFAULT 0,
    FOREIGN KEY (eID)
        REFERENCES Employee(eID)
        ON DELETE CASCADE,
    CONSTRAINT chk_desk_number
        CHECK (Desk_Number > 0),
    CONSTRAINT chk_checkin_count
        CHECK (Check_In_Count >= 0)
);

-- =====================================================
-- Table 11: Administrator
-- Stores administrator-specific information
-- =====================================================
CREATE TABLE Administrator (
    eID               VARCHAR(20) PRIMARY KEY,
    Privilege_Level   INT        DEFAULT 1,
    Report_Access     VARCHAR(3) DEFAULT 'No',
    Managed_Department VARCHAR(20) DEFAULT NULL,
    FOREIGN KEY (eID)
        REFERENCES Employee(eID)
        ON DELETE CASCADE,
    CONSTRAINT chk_privilege
        CHECK (Privilege_Level BETWEEN 1 AND 5),
    CONSTRAINT chk_report_access
        CHECK (Report_Access IN ('Yes', 'No')),
    CONSTRAINT chk_managed_dept
        CHECK (Managed_Department IN ('Administrator', 'Receptionist', 'Accountant', 'Housekeeper', NULL))
);

-- =====================================================
-- Table 12: Accountant
-- Stores accountant-specific information
-- =====================================================
CREATE TABLE Accountant (
    eID                     VARCHAR(20) PRIMARY KEY,
    Invoices_Processed      INT DEFAULT 0,
    Last_Audit_Date         DATE DEFAULT NULL,
    Payment_Clearance_Limit DECIMAL(10, 2) DEFAULT 5000.00,
    FOREIGN KEY (eID)
        REFERENCES Employee(eID)
        ON DELETE CASCADE,
    CONSTRAINT chk_invoices
        CHECK (Invoices_Processed >= 0),
    CONSTRAINT chk_clearance
        CHECK (Payment_Clearance_Limit >= 0)
);

-- =====================================================
-- Create Indexes for Performance
-- =====================================================
CREATE INDEX idx_guest_email        ON Guest(Email_Address);
CREATE INDEX idx_guest_phone        ON Guest(Phone_Number);
CREATE INDEX idx_loyalty_expdate    ON Loyalty_Member(Exp_Date);
CREATE INDEX idx_room_type          ON Hotel_Room(Room_Type);
CREATE INDEX idx_room_status        ON Hotel_Room(Housekeeper_Status);
CREATE INDEX idx_stays_checkin      ON Stays_In(Check_In_Date);
CREATE INDEX idx_stays_status       ON Stays_In(Status);
CREATE INDEX idx_payment_date       ON Makes_Payment(Payment_Date);
CREATE INDEX idx_employee_category  ON Employee(eCategory);

-- =====================================================
-- Display Table Creation Summary (optional)
-- These SELECTs are fine in SQLite; they'll just return rows.
-- =====================================================
SELECT 'Database StaySmart_Hotel created successfully!' AS Status;
SELECT 'All 12 tables created with constraints and indexes.' AS Status;
