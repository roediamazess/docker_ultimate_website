-- Tambahan data dummy users
INSERT INTO users (display_name, full_name, email, password, tier, role, start_work, created_at) VALUES
('user3', 'User Tiga', 'user3@example.com', '$2y$10$QeQw1Qw1Qw1Qw1Qw1Qw1QeQw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw', 'Tier 3', 'User', '2022-07-01', NOW()),
('client2', 'Client Dua', 'client2@example.com', '$2y$10$QeQw1Qw1Qw1Qw1Qw1Qw1QeQw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw', 'Tier 2', 'Client', '2022-08-01', NOW()),
('client3', 'Client Tiga', 'client3@example.com', '$2y$10$QeQw1Qw1Qw1Qw1Qw1Qw1QeQw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw', 'Tier 3', 'Client', '2022-09-01', NOW()),
('office2', 'Admin Office Dua', 'office2@example.com', '$2y$10$QeQw1Qw1Qw1Qw1Qw1Qw1QeQw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw', 'Tier 1', 'Admin Office', '2022-10-01', NOW());

-- Tambahan data dummy customers
INSERT INTO customers (customer_id, name, star, room, outlet, type, "group", zone, address, billing, created_at) VALUES
('CUST003', 'Hotel Anggrek', 5, '303', 'Restoran Anggrek', 'Hotel', 'Group C', 'Zone 3', 'Jl. Anggrek No.3', 'Subscription', NOW()),
('CUST004', 'Restoran Kenanga', 2, '404', 'Outlet Kenanga', 'Restaurant', 'Group D', 'Zone 4', 'Jl. Kenanga No.4', 'Contract Maintenance', NOW()),
('CUST005', 'Hotel Melati', 3, '505', 'Restoran Melati', 'Hotel', 'Group E', 'Zone 5', 'Jl. Melati No.5', 'Subscription', NOW());

-- Tambahan data dummy projects
INSERT INTO projects (project_id, pic, assignment, project_information, req_pic, hotel_name, project_name, start_date, end_date, total_days, type, status, handover_official_report, handover_days, ketertiban_admin, point_ach, point_req, percent_point, month, quarter, week_no, s1_estimation_kpi2, s1_over_days, s1_count_of_emails_sent, s2_email_sent, s3_email_sent, created_at) VALUES
('PRJ003', 3, 'Leader', 'Request', 'Request', 3, 'Implementasi POS', '2023-03-01', '2023-03-10', 10, 'Implementation', 'Done', '2023-03-12', 2, 'Excellent', 90, 100, 90, 'March', 'Quarter 1', 9, 'KPI3', '0', '4', '1', '0', NOW()),
('PRJ004', 4, 'Assist', 'Submission', 'Assignment', 4, 'Upgrade Server', '2023-04-05', '2023-04-15', 11, 'Upgrade', 'Running', NULL, NULL, NULL, 60, 100, 60, 'April', 'Quarter 2', 14, 'KPI4', '2', '2', '0', '0', NOW()),
('PRJ005', 5, 'Leader', 'Request', 'Request', 5, 'Maintenance Tahunan', '2023-05-10', '2023-05-20', 11, 'Maintenance', 'Done', NULL, NULL, NULL, 0, 100, 0, 'May', 'Quarter 2', 19, 'KPI5', '0', '0', '0', '0', NOW());

-- Tambahan data dummy activities
INSERT INTO activities (project_id, no, information_date, user_position, department, application, type, description, action_solution, due_date, status, cnc_number, created_at) VALUES
('PRJ003', 1, '2023-03-02', 'User Dua', 'IT / EDP', 'My POS', 'Setup', 'Install POS', 'Berhasil', '2023-03-03', 'Done', 'CNC004', NOW()),
('PRJ003', 2, '2023-03-04', 'User Tiga', 'Front Office', 'My POS', 'Issue', 'Printer error', 'Ganti kabel', '2023-03-05', 'Done', 'CNC005', NOW()),
('PRJ004', 1, '2023-04-06', 'Client Dua', 'Engineering', 'Power FO', 'Setup', 'Upgrade server', 'Berhasil', '2023-04-08', 'On Progress', 'CNC006', NOW()),
('PRJ005', 1, '2023-05-11', 'Admin Office Dua', 'Sales & Marketing', 'Power FO', 'Setup', 'Maintenance tahunan', 'Berjalan', '2023-05-15', 'Done', 'CNC007', NOW());
