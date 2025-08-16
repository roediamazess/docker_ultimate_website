-- Add email field to customers table
-- This script adds an email field to store contact information for different departments

-- For PostgreSQL
ALTER TABLE customers ADD COLUMN IF NOT EXISTS email VARCHAR(255);

-- For MySQL (if using MySQL instead)
-- ALTER TABLE customers ADD COLUMN email VARCHAR(255) AFTER address;

-- Add comment to describe the field
COMMENT ON COLUMN customers.email IS 'Email address for customer contact (General Manager, Executive Office, HR, Accounting, etc.)';

-- Update existing records with placeholder email if needed
-- UPDATE customers SET email = 'contact@' || LOWER(REPLACE(name, ' ', '')) || '.com' WHERE email IS NULL;

-- Create index on email field for better search performance
CREATE INDEX IF NOT EXISTS idx_customers_email ON customers(email);

-- Sample data update for existing customers (optional)
-- UPDATE customers SET email = 'gm@hotelmawar.com' WHERE customer_id = 'CUST001';
-- UPDATE customers SET email = 'hr@restoranmelati.com' WHERE customer_id = 'CUST002';
