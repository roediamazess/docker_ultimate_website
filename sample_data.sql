-- Sample data for users
INSERT INTO users (display_name, full_name, email, password, tier, role, start_work, created_at)
VALUES
('admin', 'Admin Utama', 'admin@example.com', '$2y$10$QeQw1Qw1Qw1Qw1Qw1Qw1QeQw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw', 'Tier 1', 'Administrator', '2022-01-01', NOW()),
('manager', 'Manajer Satu', 'manager@example.com', '$2y$10$QeQw1Qw1Qw1Qw1Qw1Qw1QeQw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw', 'Tier 2', 'Management', '2022-02-01', NOW()),
('office', 'Admin Office', 'office@example.com', '$2y$10$QeQw1Qw1Qw1Qw1Qw1Qw1QeQw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw', 'Tier 3', 'Admin Office', '2022-03-01', NOW()),
('user1', 'User Satu', 'user1@example.com', '$2y$10$QeQw1Qw1Qw1Qw1Qw1Qw1QeQw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw', 'New Born', 'User', '2022-04-01', NOW()),
('client1', 'Client Satu', 'client1@example.com', '$2y$10$QeQw1Qw1Qw1Qw1Qw1Qw1QeQw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw', 'Tier 1', 'Client', '2022-05-01', NOW());

-- Sample data for customers
INSERT INTO customers (customer_id, name, star, room, outlet, type, "group", zone, address, billing, created_at)
VALUES
('CUST001', 'Hotel Mawar', 4, '101', 'Restoran Mawar', 'Hotel', 'Group A', 'Zone 1', 'Jl. Mawar No.1', 'Contract Maintenance', NOW()),
('CUST002', 'Restoran Melati', 3, '202', 'Outlet Melati', 'Restaurant', 'Group B', 'Zone 2', 'Jl. Melati No.2', 'Subscription', NOW());

-- Sample data for projects
INSERT INTO projects (project_id, pic, assignment, project_information, req_pic, hotel_name, project_name, start_date, end_date, total_days, type, status, handover_official_report, handover_days, ketertiban_admin, point_ach, point_req, percent_point, month, quarter, week_no, s1_estimation_kpi2, s1_over_days, s1_count_of_emails_sent, s2_email_sent, s3_email_sent, created_at)
VALUES
('PRJ001', 1, 'Leader', 'Request', 'Request', 1, 'Implementasi PMS', '2023-01-10', '2023-01-20', 11, 'Implementation', 'Done', '2023-01-22', 2, 'Excellent', 100, 100, 100, 'January', 'Quarter 1', 2, 'KPI1', '0', '5', '2', '1', NOW()),
('PRJ002', 2, 'Assist', 'Submission', 'Assignment', 2, 'Upgrade POS', '2023-02-01', '2023-02-10', 10, 'Upgrade', 'Running', NULL, NULL, NULL, 50, 100, 50, 'February', 'Quarter 1', 5, 'KPI2', '1', '3', '1', '0', NOW());

-- Sample data for activities
INSERT INTO activities (project_id, no, information_date, user_position, department, application, type, description, action_solution, due_date, status, cnc_number, created_at)
VALUES
('PRJ001', 1, '2023-01-11', 'Admin Utama', 'IT / EDP', 'Power FO', 'Setup', 'Installasi awal', 'Berhasil', '2023-01-12', 'Done', 'CNC001', NOW()),
('PRJ001', 2, '2023-01-13', 'Manajer Satu', 'Front Office', 'Power FO', 'Issue', 'Koneksi error', 'Restart server', '2023-01-14', 'Done', 'CNC002', NOW()),
('PRJ002', 1, '2023-02-02', 'Admin Office', 'Sales & Marketing', 'My POS', 'Setup', 'Upgrade modul', 'Berhasil', '2023-02-05', 'On Progress', 'CNC003', NOW());
