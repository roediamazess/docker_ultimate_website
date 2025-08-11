-- Migration to add new fields to activities table
-- Based on user requirements for new activity data structure

-- Add new fields to activities table
ALTER TABLE activities 
ADD COLUMN priority VARCHAR(20) DEFAULT 'Normal',
ADD COLUMN customer VARCHAR(100),
ADD COLUMN project VARCHAR(100),
ADD COLUMN created_by INTEGER REFERENCES users(id),
ADD COLUMN completed_date DATE;

-- Update existing records to set default values
UPDATE activities SET 
    priority = 'Normal' WHERE priority IS NULL,
    created_by = 1 WHERE created_by IS NULL; -- Assuming user ID 1 exists

-- Make priority field NOT NULL after setting defaults
ALTER TABLE activities ALTER COLUMN priority SET NOT NULL;

-- Add constraint to ensure priority is one of the allowed values
ALTER TABLE activities ADD CONSTRAINT check_priority 
CHECK (priority IN ('Low', 'Normal', 'Hard'));

-- Add constraint to ensure department is mandatory
ALTER TABLE activities ALTER COLUMN department SET NOT NULL;

-- Add constraint to ensure application is mandatory  
ALTER TABLE activities ALTER COLUMN application SET NOT NULL;

-- Add constraint to ensure type is mandatory
ALTER TABLE activities ALTER COLUMN type SET NOT NULL;

-- Add constraint to ensure description is mandatory
ALTER TABLE activities ALTER COLUMN description SET NOT NULL;

-- Add constraint to ensure status is mandatory
ALTER TABLE activities ALTER COLUMN status SET NOT NULL;

-- Add constraint to ensure information_date is mandatory
ALTER TABLE activities ALTER COLUMN information_date SET NOT NULL;

-- Set default values for mandatory fields
UPDATE activities SET 
    application = 'Power FO' WHERE application IS NULL,
    type = 'Issue' WHERE type IS NULL,
    status = 'Open' WHERE status IS NULL,
    information_date = CURRENT_DATE WHERE information_date IS NULL;

-- Add comments for documentation
COMMENT ON COLUMN activities.priority IS 'Activity priority: Low, Normal, Hard (default: Normal)';
COMMENT ON COLUMN activities.customer IS 'Customer name or identifier';
COMMENT ON COLUMN activities.project IS 'Project name or identifier';
COMMENT ON COLUMN activities.created_by IS 'User ID who created the activity';
COMMENT ON COLUMN activities.completed_date IS 'Date when activity was completed';
COMMENT ON COLUMN activities.department IS 'Department responsible (mandatory)';
COMMENT ON COLUMN activities.application IS 'Application system (mandatory, default: Power FO)';
COMMENT ON COLUMN activities.type IS 'Activity type (mandatory, default: Issue)';
COMMENT ON COLUMN activities.description IS 'Activity description (mandatory)';
COMMENT ON COLUMN activities.status IS 'Activity status (mandatory, default: Open)';
COMMENT ON COLUMN activities.information_date IS 'Information date (mandatory, default: today)';
