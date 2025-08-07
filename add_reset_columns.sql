-- Add reset password columns to users table
ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token VARCHAR(64) NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_expires TIMESTAMP NULL;

-- Add index for better performance
CREATE INDEX IF NOT EXISTS idx_users_reset_token ON users(reset_token);
CREATE INDEX IF NOT EXISTS idx_users_reset_expires ON users(reset_expires); 