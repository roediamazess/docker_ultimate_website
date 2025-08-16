-- Remove old email field from customers table
-- This script removes the old single email field while keeping all department email fields

-- For PostgreSQL
ALTER TABLE customers DROP COLUMN IF EXISTS email;

-- For MySQL (if using MySQL instead)
-- ALTER TABLE customers DROP COLUMN email;

-- Drop the old email index if it exists
DROP INDEX IF EXISTS idx_customers_email;

-- Verify the remaining email fields are still there
-- The following fields should remain:
-- email_gm, email_executive, email_hr, email_acc_head, email_chief_acc, 
-- email_cost_control, email_ap, email_ar, email_fb, email_fo, email_hk, email_engineering

-- Check current table structure
-- SELECT column_name, data_type FROM information_schema.columns 
-- WHERE table_name = 'customers' AND column_name LIKE 'email%' 
-- ORDER BY ordinal_position;
