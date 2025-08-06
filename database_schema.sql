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
    status ENUM('Open', 'On Progress', 'Need Requirement', 'Done'),
    cnc_number VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
