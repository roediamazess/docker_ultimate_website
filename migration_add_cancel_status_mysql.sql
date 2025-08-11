-- Migration Script: Add Cancel Status to Activity Status ENUM
-- File: migration_add_cancel_status_mysql.sql
-- Database: MySQL
-- Purpose: Add 'Cancel' value to status ENUM column in activities table

-- Step 1: Modify the ENUM column to include 'Cancel'
ALTER TABLE activities MODIFY COLUMN status ENUM('Open', 'On Progress', 'Need Requirement', 'Done', 'Cancel');

-- Step 2: Verify the change
SHOW COLUMNS FROM activities LIKE 'status';

-- Note: This will modify the existing ENUM column to include the new 'Cancel' value
-- All existing data will be preserved
