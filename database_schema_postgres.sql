-- ENUM definitions
CREATE TYPE user_tier AS ENUM ('New Born', 'Tier 1', 'Tier 2', 'Tier 3');
CREATE TYPE user_role AS ENUM ('Administrator', 'Management', 'Admin Office', 'User', 'Client');
CREATE TYPE customer_type AS ENUM ('Hotel', 'Restaurant', 'Head Quarter', 'Education');
CREATE TYPE billing_type AS ENUM ('Contract Maintenance', 'Subscription');
CREATE TYPE assignment_type AS ENUM ('Leader', 'Assist', '');
CREATE TYPE project_info_type AS ENUM ('Request', 'Submission');
CREATE TYPE req_pic_type AS ENUM ('Request', 'Assignment');
CREATE TYPE project_type AS ENUM ('Implementation', 'Upgrade', 'Maintenance', 'Retraining', 'On Line Training', 'On Line Maintenance', 'Remote Installation', 'In House Training', 'Special Request', '2nd Implementation', 'Jakarta Support', 'Bali Support', 'Others');
CREATE TYPE project_status AS ENUM ('Scheduled', 'Running', 'Document', 'Document Check', 'Done', 'Cancel', 'Rejected');
CREATE TYPE department_type AS ENUM ('Food & Beverage', 'Kitchen', 'Room Division', 'Front Office', 'Housekeeping', 'Engineering', 'Sales & Marketing', 'IT / EDP', 'Accounting', 'Executive Office');
CREATE TYPE application_type AS ENUM ('Power FO', 'My POS', 'My MGR', 'Power AR', 'Power INV', 'Power AP', 'Power GL', 'Keylock', 'PABX', 'DIM', 'Dynamic Room Rate', 'Channel Manager', 'PB1', 'Power SIGN', 'Multi Properties', 'Scanner ID', 'IPOS', 'Power Runner', 'Power RA', 'Power ME', 'ECOS', 'Cloud WS', 'Power GO', 'Dashpad', 'IPTV', 'HSIA', 'SGI', 'Guest Survey', 'Loyalty Management', 'AccPac', 'GL Consolidation', 'Self Check In', 'Check In Desk', 'Others');
CREATE TYPE activity_type AS ENUM ('Setup', 'Question', 'Issue', 'Report Issue', 'Report Request', 'Feature Request');
CREATE TYPE activity_status AS ENUM ('Open', 'On Progress', 'Need Requirement', 'Done', 'Cancel');

-- USERS TABLE
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    display_name VARCHAR(100),
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    tier user_tier,
    role user_role,
    start_work DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CUSTOMERS TABLE
CREATE TABLE customers (
    id SERIAL PRIMARY KEY,
    customer_id VARCHAR(50) NOT NULL,
    name VARCHAR(100),
    star SMALLINT,
    room VARCHAR(50),
    outlet VARCHAR(50),
    type customer_type,
    "group" TEXT,
    zone TEXT,
    address TEXT,
    billing billing_type,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- PROJECTS TABLE
CREATE TABLE projects (
    id SERIAL PRIMARY KEY,
    project_id VARCHAR(50) NOT NULL,
    pic INTEGER REFERENCES users(id),
    assignment assignment_type,
    project_information project_info_type,
    req_pic req_pic_type,
    hotel_name INTEGER NOT NULL REFERENCES customers(id),
    project_name VARCHAR(100),
    start_date DATE NOT NULL,
    end_date DATE,
    total_days INTEGER,
    type project_type NOT NULL,
    status project_status NOT NULL,
    handover_official_report DATE,
    handover_days INTEGER,
    ketertiban_admin VARCHAR(20),
    point_ach INTEGER,
    point_req INTEGER,
    percent_point FLOAT,
    month VARCHAR(20),
    quarter VARCHAR(20),
    week_no INTEGER,
    s1_estimation_kpi2 TEXT,
    s1_over_days TEXT,
    s1_count_of_emails_sent TEXT,
    s2_email_sent TEXT,
    s3_email_sent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ACTIVITIES TABLE
CREATE TABLE activities (
    id SERIAL PRIMARY KEY,
    project_id VARCHAR(50),
    no INTEGER,
    information_date DATE,
    priority VARCHAR(20) DEFAULT 'Normal',
    user_position VARCHAR(100),
    department department_type,
    application application_type,
    type activity_type,
    description TEXT,
    action_solution TEXT,
    customer VARCHAR(100),
    project VARCHAR(100),
    completed_date DATE,
    due_date DATE,
    status activity_status,
    cnc_number VARCHAR(50),
    created_by INTEGER REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
