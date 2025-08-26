-- USERS TABLE
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    display_name VARCHAR(100),
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    tier ENUM('New Born', 'Tier 1', 'Tier 2', 'Tier 3'),
    role ENUM('Administrator', 'Management', 'Admin Office', 'User', 'Client'),
    start_work DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CUSTOMERS TABLE
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id VARCHAR(50) NOT NULL,
    name VARCHAR(100),
    star TINYINT,
    room VARCHAR(50),
    outlet VARCHAR(50),
    type ENUM('Hotel', 'Restaurant', 'Head Quarter', 'Education'),
    `group` TEXT,
    zone TEXT,
    address TEXT,
    billing ENUM('Contract Maintenance', 'Subscription'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- PROJECTS TABLE
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id VARCHAR(50) NOT NULL,
    pic INT,
    assignment ENUM('Leader', 'Assist', ''),
    project_information ENUM('Request', 'Submission'),
    req_pic ENUM('Request', 'Assignment'),
    hotel_name INT NOT NULL,
    project_name VARCHAR(100),
    start_date DATE NOT NULL,
    end_date DATE,
    total_days INT,
    type ENUM('Implementation', 'Upgrade', 'Maintenance', 'Retraining', 'On Line Training', 'On Line Maintenance', 'Remote Installation', 'In House Training', 'Special Request', '2nd Implementation', 'Jakarta Support', 'Bali Support', 'Others') NOT NULL,
    status ENUM('Scheduled', 'Running', 'Document', 'Document Check', 'Done', 'Cancel', 'Rejected') NOT NULL,
    handover_official_report DATE,
    handover_days INT,
    ketertiban_admin VARCHAR(20),
    point_ach INT,
    point_req INT,
    percent_point FLOAT,
    month VARCHAR(20),
    quarter VARCHAR(20),
    week_no INT,
    s1_estimation_kpi2 TEXT,
    s1_over_days TEXT,
    s1_count_of_emails_sent TEXT,
    s2_email_sent TEXT,
    s3_email_sent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pic) REFERENCES users(id),
    FOREIGN KEY (hotel_name) REFERENCES customers(id)
);

-- ACTIVITIES TABLE
CREATE TABLE activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id VARCHAR(50),
    no INT,
    information_date DATE,
    user_position VARCHAR(100),
    department ENUM('Food & Beverage', 'Kitchen', 'Room Division', 'Front Office', 'Housekeeping', 'Engineering', 'Sales & Marketing', 'IT / EDP', 'Accounting', 'Executive Office'),
    application ENUM('Power FO', 'My POS', 'My MGR', 'Power AR', 'Power INV', 'Power AP', 'Power GL', 'Keylock', 'PABX', 'DIM', 'Dynamic Room Rate', 'Channel Manager', 'PB1', 'Power SIGN', 'Multi Properties', 'Scanner ID', 'IPOS', 'Power Runner', 'Power RA', 'Power ME', 'ECOS', 'Cloud WS', 'Power GO', 'Dashpad', 'IPTV', 'HSIA', 'SGI', 'Guest Survey', 'Loyalty Management', 'AccPac', 'GL Consolidation', 'Self Check In', 'Check In Desk', 'Others'),
    type ENUM('Setup', 'Question', 'Issue', 'Report Issue', 'Report Request', 'Feature Request'),
    description TEXT,
    action_solution TEXT,
    due_date DATE,
    status ENUM('Open', 'On Progress', 'Need Requirement', 'Done', 'Cancel'),
    cnc_number VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

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
-- USERS TABLE
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    display_name VARCHAR(100),
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    tier ENUM('New Born', 'Tier 1', 'Tier 2', 'Tier 3'),
    role ENUM('Administrator', 'Management', 'Admin Office', 'User', 'Client'),
    start_work DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CUSTOMERS TABLE
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id VARCHAR(50) NOT NULL,
    name VARCHAR(100),
    star TINYINT,
    room VARCHAR(50),
    outlet VARCHAR(50),
    type ENUM('Hotel', 'Restaurant', 'Head Quarter', 'Education'),
    `group` TEXT,
    zone TEXT,
    address TEXT,
    billing ENUM('Contract Maintenance', 'Subscription'),
    email_gm VARCHAR(255),
    email_executive VARCHAR(255),
    email_hr VARCHAR(255),
    email_acc_head VARCHAR(255),
    email_chief_acc VARCHAR(255),
    email_cost_control VARCHAR(255),
    email_ap VARCHAR(255),
    email_ar VARCHAR(255),
    email_fb VARCHAR(255),
    email_fo VARCHAR(255),
    email_hk VARCHAR(255),
    email_engineering VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- PROJECTS TABLE
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id VARCHAR(50) NOT NULL,
    pic INT,
    assignment ENUM('Leader', 'Assist', ''),
    project_information ENUM('Request', 'Submission'),
    req_pic ENUM('Request', 'Assignment'),
    hotel_name INT NOT NULL,
    project_name VARCHAR(100),
    start_date DATE NOT NULL,
    end_date DATE,
    total_days INT,
    type ENUM('Implementation', 'Upgrade', 'Maintenance', 'Retraining', 'On Line Training', 'On Line Maintenance', 'Remote Installation', 'In House Training', 'Special Request', '2nd Implementation', 'Jakarta Support', 'Bali Support', 'Others') NOT NULL,
    status ENUM('Scheduled', 'Running', 'Document', 'Document Check', 'Done', 'Cancel', 'Rejected') NOT NULL,
    handover_official_report DATE,
    handover_days INT,
    ketertiban_admin VARCHAR(20),
    point_ach INT,
    point_req INT,
    percent_point FLOAT,
    month VARCHAR(20),
    quarter VARCHAR(20),
    week_no INT,
    s1_estimation_kpi2 TEXT,
    s1_over_days TEXT,
    s1_count_of_emails_sent TEXT,
    s2_email_sent TEXT,
    s3_email_sent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pic) REFERENCES users(id),
    FOREIGN KEY (hotel_name) REFERENCES customers(id)
);

-- ACTIVITIES TABLE
CREATE TABLE activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id VARCHAR(50),
    no INT,
    information_date DATE,
    user_position VARCHAR(100),
    department ENUM('Food & Beverage', 'Kitchen', 'Room Division', 'Front Office', 'Housekeeping', 'Engineering', 'Sales & Marketing', 'IT / EDP', 'Accounting', 'Executive Office'),
    application ENUM('Power FO', 'My POS', 'My MGR', 'Power AR', 'Power INV', 'Power AP', 'Power GL', 'Keylock', 'PABX', 'DIM', 'Dynamic Room Rate', 'Channel Manager', 'PB1', 'Power SIGN', 'Multi Properties', 'Scanner ID', 'IPOS', 'Power Runner', 'Power RA', 'Power ME', 'ECOS', 'Cloud WS', 'Power GO', 'Dashpad', 'IPTV', 'HSIA', 'SGI', 'Guest Survey', 'Loyalty Management', 'AccPac', 'GL Consolidation', 'Self Check In', 'Check In Desk', 'Others'),
    type ENUM('Setup', 'Question', 'Issue', 'Report Issue', 'Report Request', 'Feature Request'),
    description TEXT,
    action_solution TEXT,
    due_date DATE,
    status ENUM('Open', 'On Progress', 'Need Requirement', 'Done', 'Cancel'),
    cnc_number VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

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
