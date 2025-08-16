-- Add multiple email fields to customers table
-- This script adds separate email fields for different departments

-- For PostgreSQL
ALTER TABLE customers ADD COLUMN IF NOT EXISTS email_gm VARCHAR(255);
ALTER TABLE customers ADD COLUMN IF NOT EXISTS email_executive VARCHAR(255);
ALTER TABLE customers ADD COLUMN IF NOT EXISTS email_hr VARCHAR(255);
ALTER TABLE customers ADD COLUMN IF NOT EXISTS email_acc_head VARCHAR(255);
ALTER TABLE customers ADD COLUMN IF NOT EXISTS email_chief_acc VARCHAR(255);
ALTER TABLE customers ADD COLUMN IF NOT EXISTS email_cost_control VARCHAR(255);
ALTER TABLE customers ADD COLUMN IF NOT EXISTS email_ap VARCHAR(255);
ALTER TABLE customers ADD COLUMN IF NOT EXISTS email_ar VARCHAR(255);
ALTER TABLE customers ADD COLUMN IF NOT EXISTS email_fb VARCHAR(255);
ALTER TABLE customers ADD COLUMN IF NOT EXISTS email_fo VARCHAR(255);
ALTER TABLE customers ADD COLUMN IF NOT EXISTS email_hk VARCHAR(255);
ALTER TABLE customers ADD COLUMN IF NOT EXISTS email_engineering VARCHAR(255);

-- For MySQL (if using MySQL instead)
-- ALTER TABLE customers ADD COLUMN email_gm VARCHAR(255) AFTER email;
-- ALTER TABLE customers ADD COLUMN email_executive VARCHAR(255) AFTER email_gm;
-- ALTER TABLE customers ADD COLUMN email_hr VARCHAR(255) AFTER email_executive;
-- ALTER TABLE customers ADD COLUMN email_acc_head VARCHAR(255) AFTER email_hr;
-- ALTER TABLE customers ADD COLUMN email_chief_acc VARCHAR(255) AFTER email_acc_head;
-- ALTER TABLE customers ADD COLUMN email_cost_control VARCHAR(255) AFTER email_chief_acc;
-- ALTER TABLE customers ADD COLUMN email_ap VARCHAR(255) AFTER email_cost_control;
-- ALTER TABLE customers ADD COLUMN email_ar VARCHAR(255) AFTER email_ap;
-- ALTER TABLE customers ADD COLUMN email_fb VARCHAR(255) AFTER email_ar;
-- ALTER TABLE customers ADD COLUMN email_fo VARCHAR(255) AFTER email_fb;
-- ALTER TABLE customers ADD COLUMN email_hk VARCHAR(255) AFTER email_fo;
-- ALTER TABLE customers ADD COLUMN email_engineering VARCHAR(255) AFTER email_hk;

-- Add comments to describe the fields
COMMENT ON COLUMN customers.email_gm IS 'Email for General Manager';
COMMENT ON COLUMN customers.email_executive IS 'Email for Executive Office';
COMMENT ON COLUMN customers.email_hr IS 'Email for HR Department Head';
COMMENT ON COLUMN customers.email_acc_head IS 'Email for Accounting Department Head';
COMMENT ON COLUMN customers.email_chief_acc IS 'Email for Chief Accounting';
COMMENT ON COLUMN customers.email_cost_control IS 'Email for Cost Control';
COMMENT ON COLUMN customers.email_ap IS 'Email for Accounting Payable';
COMMENT ON COLUMN customers.email_ar IS 'Email for Accounting Receivable';
COMMENT ON COLUMN customers.email_fb IS 'Email for F&B Department Head';
COMMENT ON COLUMN customers.email_fo IS 'Email for Front Office Department Head';
COMMENT ON COLUMN customers.email_hk IS 'Email for Housekeeping Department Head';
COMMENT ON COLUMN customers.email_engineering IS 'Email for Engineering Department Head';

-- Create indexes on email fields for better search performance
CREATE INDEX IF NOT EXISTS idx_customers_email_gm ON customers(email_gm);
CREATE INDEX IF NOT EXISTS idx_customers_email_executive ON customers(email_executive);
CREATE INDEX IF NOT EXISTS idx_customers_email_hr ON customers(email_hr);
CREATE INDEX IF NOT EXISTS idx_customers_email_acc_head ON customers(email_acc_head);
CREATE INDEX IF NOT EXISTS idx_customers_email_chief_acc ON customers(email_chief_acc);
CREATE INDEX IF NOT EXISTS idx_customers_email_cost_control ON customers(email_cost_control);
CREATE INDEX IF NOT EXISTS idx_customers_email_ap ON customers(email_ap);
CREATE INDEX IF NOT EXISTS idx_customers_email_ar ON customers(email_ar);
CREATE INDEX IF NOT EXISTS idx_customers_email_fb ON customers(email_fb);
CREATE INDEX IF NOT EXISTS idx_customers_email_fo ON customers(email_fo);
CREATE INDEX IF NOT EXISTS idx_customers_email_hk ON customers(email_hk);
CREATE INDEX IF NOT EXISTS idx_customers_email_engineering ON customers(email_engineering);

-- Sample data update for existing customers (optional)
-- UPDATE customers SET 
--     email_gm = 'gm@hotelmawar.com',
--     email_hr = 'hr@hotelmawar.com',
--     email_executive = 'executive@hotelmawar.com'
-- WHERE customer_id = 'CUST001';

-- UPDATE customers SET 
--     email_hr = 'hr@restoranmelati.com',
--     email_fb = 'f&b@restoranmelati.com'
-- WHERE customer_id = 'CUST002';
