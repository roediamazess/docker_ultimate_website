-- Migration Script: Add Cancel Status to Activity Status ENUM
-- File: migration_add_cancel_status.sql
-- Database: PostgreSQL
-- Purpose: Add 'Cancel' value to activity_status ENUM type

-- Step 1: Add 'Cancel' value to the ENUM type
ALTER TYPE activity_status ADD VALUE 'Cancel';

-- Step 2: Verify the change
SELECT enumlabel FROM pg_enum WHERE enumtypid = (SELECT oid FROM pg_type WHERE typname = 'activity_status');

-- Note: If you get an error about the value already existing, it means the migration has already been run
-- You can safely ignore that error.

-- Alternative method if the above doesn't work (for older PostgreSQL versions):
-- CREATE TYPE activity_status_new AS ENUM ('Open', 'On Progress', 'Need Requirement', 'Done', 'Cancel');
-- ALTER TABLE activities ALTER COLUMN status TYPE activity_status_new USING status::text::activity_status_new;
-- DROP TYPE activity_status;
-- ALTER TYPE activity_status_new RENAME TO activity_status;
