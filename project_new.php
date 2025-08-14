<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_once 'user_utils.php';

require_login();

// Ensure projects_detail table exists with correct structure
try {
    // Detect DB driver
    $driver = '';
    try { $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME); } catch (Throwable $e) {}

    $pdo->exec("CREATE TABLE IF NOT EXISTS projects_detail (
        id SERIAL PRIMARY KEY,
        project_id VARCHAR(150) NOT NULL,
        user_id VARCHAR(150) NOT NULL,
        start_date DATE NULL,
        end_date DATE NULL,
        total_days INT NULL,
        status VARCHAR(60) NULL,
        assignment_status VARCHAR(30) NULL,
        assignment_pic VARCHAR(30) NULL,
        customer_id VARCHAR(150) NULL,
        customer_name TEXT NULL,
        project_name TEXT NULL,
        project_remark TEXT NULL,
        type VARCHAR(80) NULL,
        month VARCHAR(20) NULL,
        quarter VARCHAR(20) NULL,
        week_no VARCHAR(20) NULL,
        created_by VARCHAR(150) NULL,
        created_at TIMESTAMP NOT NULL DEFAULT NOW()
    )");
    
    // Add missing columns if they don't exist
    $existingCols = [];
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'projects_detail' ORDER BY ordinal_position");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $existingCols[] = $row['column_name'];
    }
    
		$requiredCols = [
        'assignment_status' => 'VARCHAR(30)',
        'assignment_pic' => 'VARCHAR(30)',
        'customer_id' => 'VARCHAR(150)',
        'customer_name' => 'TEXT',
        'project_name' => 'TEXT',
        'project_remark' => 'TEXT',
        'type' => 'VARCHAR(80)',
        'month' => 'VARCHAR(20)',
        'quarter' => 'VARCHAR(20)',
        'week_no' => 'VARCHAR(20)',
			'created_by' => 'VARCHAR(150)',
			'approved_status' => 'VARCHAR(20)',
			'approved_by' => 'VARCHAR(150)',
			'approved_at' => 'TIMESTAMP'
    ];
    
    foreach ($requiredCols as $colName => $colType) {
        if (!in_array($colName, $existingCols)) {
            try {
                $pdo->exec("ALTER TABLE projects_detail ADD COLUMN $colName $colType NULL");
            } catch (Throwable $e) {}
        }
    }

		// Ensure column types are compatible (auto-migrate if needed)
    try {
        $colTypes = [];
        $stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'projects_detail'");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $colTypes[$row['column_name']] = $row['data_type'];
        }

        // user_id should be VARCHAR
        if (isset($colTypes['user_id']) && $colTypes['user_id'] === 'integer') {
            if ($driver === 'pgsql') {
                $pdo->exec("ALTER TABLE projects_detail ALTER COLUMN user_id TYPE VARCHAR(150)");
            } else {
                $pdo->exec("ALTER TABLE projects_detail MODIFY COLUMN user_id VARCHAR(150) NOT NULL");
            }
        }

        // created_by should be VARCHAR to accept non-numeric session identifiers
        if (isset($colTypes['created_by']) && $colTypes['created_by'] === 'integer') {
            if ($driver === 'pgsql') {
                $pdo->exec("ALTER TABLE projects_detail ALTER COLUMN created_by TYPE VARCHAR(150)");
            } else {
                $pdo->exec("ALTER TABLE projects_detail MODIFY COLUMN created_by VARCHAR(150) NULL");
            }
        }

        // assignment_pic should be VARCHAR(30)
        if (isset($colTypes['assignment_pic']) && $colTypes['assignment_pic'] === 'integer') {
            if ($driver === 'pgsql') {
                $pdo->exec("ALTER TABLE projects_detail ALTER COLUMN assignment_pic TYPE VARCHAR(30)");
            } else {
                $pdo->exec("ALTER TABLE projects_detail MODIFY COLUMN assignment_pic VARCHAR(30) NULL");
            }
        }

			// approved_status, approved_by, approved_at types
			if (isset($colTypes['approved_status']) && $colTypes['approved_status'] !== 'character varying') {
				if ($driver === 'pgsql') {
					$pdo->exec("ALTER TABLE projects_detail ALTER COLUMN approved_status TYPE VARCHAR(20)");
				}
			}
			if (isset($colTypes['approved_by']) && $colTypes['approved_by'] !== 'character varying') {
				if ($driver === 'pgsql') {
					$pdo->exec("ALTER TABLE projects_detail ALTER COLUMN approved_by TYPE VARCHAR(150)");
				}
			}
			if (isset($colTypes['approved_at']) && strpos((string)$colTypes['approved_at'], 'timestamp') === false) {
				if ($driver === 'pgsql') {
					$pdo->exec("ALTER TABLE projects_detail ALTER COLUMN approved_at TYPE TIMESTAMP USING approved_at::timestamp");
				}
			}
    } catch (Throwable $e) {}
} catch (Throwable $e) {}

$projectsColumns = [];
$projectsColumnTypes = [];
try {
    $stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'projects'");
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $projectsColumns[$r['column_name']] = true;
        $projectsColumnTypes[$r['column_name']] = $r['data_type'];
    }
} catch (Throwable $e) {}

// Handle form submission
if ($_POST) {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'save_project') {
            try {
                $pdo->beginTransaction();
                
                // Save project header
                $project_id = $_POST['project_id'];
                $project_name = $_POST['project_name'];
                $hotel_id = $_POST['hotel_id'];
                $hotel_name = $_POST['hotel_name'];
                $start_date = $_POST['start_date'];
                $end_date = $_POST['end_date'];
                $type = $_POST['type'];
                $status = $_POST['status'];
                $remark = $_POST['project_remark'];
                // Compute header total_days
                $header_total_days = null;
                if (!empty($start_date) && !empty($end_date)) {
                    try { $d = date_diff(date_create($start_date), date_create($end_date)); $header_total_days = $d->days + 1; } catch (Throwable $e) {}
                }
                
                // Check if project exists
                $stmt = $pdo->prepare("SELECT id FROM projects WHERE project_id = ?");
                $stmt->execute([$project_id]);
                $existing = $stmt->fetch();
                
                // Prepare typed hotel_name value if that column exists and is integer
                $hotelNameIntValue = null;
                if (isset($projectsColumns['hotel_name']) && ($projectsColumnTypes['hotel_name'] ?? '') === 'integer') {
                    if ($hotel_id === '' || !is_numeric($hotel_id)) {
                        throw new Exception('Pilih Hotel/Customer yang valid (dibutuhkan karena kolom hotel_name bertipe integer).');
                    }
                    $hotelNameIntValue = (int)$hotel_id;
                }

                if ($existing) {
                    // Dynamic UPDATE sets hotel_name_text and/or hotel_name when present
                    $setClauses = [];
                    $params = [];
                    $setClauses[] = 'project_name = ?'; $params[] = $project_name;
                    $setClauses[] = 'hotel_id = ?'; $params[] = $hotel_id;
                    if (isset($projectsColumns['hotel_name_text'])) { $setClauses[] = 'hotel_name_text = ?'; $params[] = $hotel_name; }
                    if (isset($projectsColumns['hotel_name'])) {
                        if (($projectsColumnTypes['hotel_name'] ?? '') === 'integer') { $setClauses[] = 'hotel_name = ?'; $params[] = $hotelNameIntValue; }
                        else { $setClauses[] = 'hotel_name = ?'; $params[] = $hotel_name; }
                    }
                    $setClauses[] = 'start_date = ?'; $params[] = $start_date;
                    $setClauses[] = 'end_date = ?'; $params[] = $end_date;
                    $setClauses[] = 'total_days = ?'; $params[] = $header_total_days;
                    $setClauses[] = 'type = ?'; $params[] = $type;
                    $setClauses[] = 'status = ?'; $params[] = $status;
                    $setClauses[] = 'project_remark = ?'; $params[] = $remark;
                    $sql = 'UPDATE projects SET ' . implode(', ', $setClauses) . ' WHERE project_id = ?';
                    $params[] = $project_id;
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                } else {
                    // Dynamic INSERT includes hotel_name_text and/or hotel_name when present
                    $cols = ['project_id','project_name','hotel_id'];
                    $vals = [$project_id, $project_name, $hotel_id];
                    if (isset($projectsColumns['hotel_name_text'])) { $cols[] = 'hotel_name_text'; $vals[] = $hotel_name; }
                    if (isset($projectsColumns['hotel_name'])) {
                        $cols[] = 'hotel_name';
                        if (($projectsColumnTypes['hotel_name'] ?? '') === 'integer') { $vals[] = $hotelNameIntValue; }
                        else { $vals[] = $hotel_name; }
                    }
                    $cols = array_merge($cols, ['start_date','end_date','total_days','type','status','project_remark','created_at']);
                    $vals = array_merge($vals, [$start_date, $end_date, $header_total_days, $type, $status, $remark, date('Y-m-d H:i:s')]);
                    $placeholders = rtrim(str_repeat('?,', count($vals)), ',');
                    $sql = 'INSERT INTO projects (' . implode(', ', $cols) . ') VALUES (' . $placeholders . ')';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($vals);
                }
                
                                 // Save project details
                 if (isset($_POST['details']) && is_array($_POST['details'])) {
                     // Debug: log the details data
                     try {
                         log_activity('debug', 'Details data: ' . json_encode($_POST['details']));
                     } catch (Throwable $e) {}
                     
                     // Validate detail dates within project header period
                     $headerStart = $start_date ?: null;
                     $headerEnd = $end_date ?: null;
                     if ($headerStart !== null) { try { $headerStartObj = new DateTime($headerStart); } catch (Throwable $e) { $headerStartObj = null; } } else { $headerStartObj = null; }
                     if ($headerEnd !== null) { try { $headerEndObj = new DateTime($headerEnd); } catch (Throwable $e) { $headerEndObj = null; } } else { $headerEndObj = null; }

                     // Map existing detail approvals to decide which rows will be updated
                     $existing = [];
                     $stmt = $pdo->prepare("SELECT id, approved_status FROM projects_detail WHERE project_id = ?");
                     $stmt->execute([$project_id]);
                     while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                         $existing[(string)$r['id']] = $r;
                     }

                     $rowIndex = 0;
                     foreach ($_POST['details'] as $detail) {
                         $rowIndex++;
                         $detailId = isset($detail['id']) ? trim((string)$detail['id']) : '';
                         $willUpdate = true;
                         if ($detailId !== '' && isset($existing[$detailId]) && ($existing[$detailId]['approved_status'] ?? null) === 'Approved') {
                             $willUpdate = false; // skip validation for approved rows since they won't be changed
                         }
                         if (!$willUpdate) { continue; }

                         $dStart = $detail['start_date'] ?? null;
                         $dEnd = $detail['end_date'] ?? null;
                         if (($dStart || $dEnd) && ($headerStartObj || $headerEndObj)) {
                             try { $dStartObj = $dStart ? new DateTime($dStart) : null; } catch (Throwable $e) { $dStartObj = null; }
                             try { $dEndObj = $dEnd ? new DateTime($dEnd) : null; } catch (Throwable $e) { $dEndObj = null; }
                             if ($dStartObj && $headerStartObj && $dStartObj < $headerStartObj) {
                                 throw new Exception('Detail row ' . $rowIndex . ': Start date di luar rentang proyek (lebih awal dari ' . $headerStartObj->format('Y-m-d') . ').');
                             }
                             if ($dEndObj && $headerEndObj && $dEndObj > $headerEndObj) {
                                 throw new Exception('Detail row ' . $rowIndex . ': End date di luar rentang proyek (lebih akhir dari ' . $headerEndObj->format('Y-m-d') . ').');
                             }
                         }
                     }

					 // Smart sync details without touching Approved rows
                     // $existing already populated above
					 
					 $submittedIds = [];
					 foreach ($_POST['details'] as $detail) {
					 	$user_id = trim((string)($detail['user_id'] ?? ''));
					 	if ($user_id === '') { continue; }
					 	$start = $detail['start_date'] ?? null;
					 	$end = $detail['end_date'] ?? null;
					 	$total_days = null;
					 	if ($start && $end) {
					 		try { $diff = date_diff(date_create($start), date_create($end)); $total_days = $diff->days + 1; } catch (Throwable $e) {}
					 	}
					 	$detailId = isset($detail['id']) ? trim((string)$detail['id']) : '';
					 	if ($detailId !== '' && isset($existing[$detailId])) {
					 		$submittedIds[] = $detailId;
					 		// Update only if not Approved
					 		if (($existing[$detailId]['approved_status'] ?? null) !== 'Approved') {
					 			$stmt = $pdo->prepare("UPDATE projects_detail SET user_id = ?, start_date = ?, end_date = ?, total_days = ?, status = ?, assignment_status = ?, assignment_pic = ?, customer_id = ?, customer_name = ?, project_name = ?, project_remark = ?, type = ? WHERE id = ?");
					 			$stmt->execute([$user_id, $start, $end, $total_days, ($detail['status'] ?? null), ($detail['assignment_status'] ?? null), ($detail['assignment_pic'] ?? null), $hotel_id, $hotel_name, $project_name, $remark, $type, $detailId]);
					 		}
					 	} else {
					 		// Insert new row
					 		$stmt = $pdo->prepare("INSERT INTO projects_detail (
					 			project_id, user_id, start_date, end_date, total_days, status,
					 			assignment_status, assignment_pic, customer_id, customer_name,
					 			project_name, project_remark, type, created_by, created_at
					 		) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
					 		$stmt->execute([$project_id, $user_id, $start, $end, $total_days, ($detail['status'] ?? null), ($detail['assignment_status'] ?? null), ($detail['assignment_pic'] ?? null), $hotel_id, $hotel_name, $project_name, $remark, $type, ($_SESSION['user_id'] ?? null)]);
					 	}
					 }
					 
					 // Delete rows not submitted and not Approved
					 if (!empty($existing)) {
					 	$idsToKeep = array_flip($submittedIds);
					 	foreach ($existing as $exId => $exRow) {
					 		if (!isset($idsToKeep[$exId]) && ($exRow['approved_status'] ?? null) !== 'Approved') {
					 			$stmt = $pdo->prepare("DELETE FROM projects_detail WHERE id = ?");
					 			$stmt->execute([$exId]);
					 		}
					 	}
					 }
                 }
                
                $pdo->commit();
                $success_message = "Project saved successfully!";
                // Reload page to reflect changes in the list
                header('Location: projects.php');
                exit;
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $error_message = "Error: " . $e->getMessage();
            }
        }
    }
}

// Get projects list with filters similar to project.php
$projects = [];
$limit = (int)($_GET['limit'] ?? 10); if (!in_array($limit, [10,15,20], true)) { $limit = 10; }
$search = trim($_GET['search'] ?? '');
$filter_status = trim($_GET['filter_status'] ?? '');
$filter_type = trim($_GET['filter_type'] ?? '');
$filter_project_information = trim($_GET['filter_project_information'] ?? '');

try {
    $drv = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $castExpr = ($drv === 'pgsql') ? 'CAST(c.id AS TEXT)' : 'CAST(c.id AS CHAR)';
    $joinCustomers = "LEFT JOIN customers c ON p.hotel_id = $castExpr";
    $where = [];
    $params = [];
    if ($search !== '') {
        $where[] = "(p.project_id ILIKE ? OR p.project_name ILIKE ? OR c.name ILIKE ? OR c.customer_id ILIKE ?)";
        $searchTerm = "%$search%";
        array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    }
    if ($filter_status !== '') { $where[] = 'p.status = ?'; $params[] = $filter_status; }
    if ($filter_type !== '') { $where[] = 'p.type = ?'; $params[] = $filter_type; }
    if ($filter_project_information !== '') { $where[] = 'p.project_information = ?'; $params[] = $filter_project_information; }
    $whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
    $sql = "SELECT p.* FROM projects p $joinCustomers $whereClause ORDER BY p.created_at DESC LIMIT $limit";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// Get users for PIC lookup
$users = [];
try {
    $stmt = $pdo->query("SELECT user_id, full_name FROM users ORDER BY full_name");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// Get customers for hotel lookup
$customers = [];
try {
    $stmt = $pdo->query("SELECT id, customer_id, name FROM customers ORDER BY name");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>
<?php include './partials/layouts/layoutHorizontal.php'; ?>

        <div class="dashboard-main-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
                <div class="d-flex align-items-center gap-3">
                    <h6 class="fw-semibold mb-0">Project List</h6>
                    
                </div>
                <ul class="d-flex align-items-center gap-2">
                    <li class="fw-medium">
                        <a href="index.php" class="d-flex align-items-center gap-1 hover-text-primary">
                            <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                            Dashboard
                        </a>
                    </li>
                    <li>-</li>
                    <li class="fw-medium">Project List</li>
                </ul>
            </div>

            <style>
        .detail-row { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 8px; border: 1px solid #dee2e6; }
        .detail-row:hover { background: #e9ecef; }
        .btn-remove { background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; }
        .btn-remove:hover { background: #c82333; }
        .success-message { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 15px 0; }
        .error-message { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 15px 0; }
            </style>

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Project Management - New Version</h2>
            <div>
                
                <a href="index.php" class="btn btn-outline-primary">← Back to Dashboard</a>
            </div>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="success-message"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <div class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-sm btn-primary-600" onclick="openAddModal()">Create Project</button>
        </div>

        <!-- Add/Edit Modal (custom style to match project.php) -->
        <style>
            .custom-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); display: none; align-items: center; justify-content: center; z-index: 1050; }
            .custom-modal { width: min(980px, 96vw); background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
            .custom-modal-header { padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 100%); color: #fff; }
            .custom-modal-title { margin: 0; font-size: 18px; font-weight: 600; }
            .custom-modal-close { background: transparent; border: 0; color: #fff; font-size: 22px; cursor: pointer; line-height: 1; }
            .custom-modal-body { padding: 20px; }
            .custom-modal-footer { padding: 14px 20px; background: #f8fafc; display: flex; gap: 10px; justify-content: flex-end; }
        </style>
        <div class="custom-modal-overlay" id="projectModal">
            <div class="custom-modal">
                <div class="custom-modal-header">
                    <h5 class="custom-modal-title" id="projectModalTitle">Add Project</h5>
                    <button type="button" class="custom-modal-close" onclick="closeProjectModal()">&times;</button>
                </div>
                <form method="post" id="projectForm">
                    <div class="custom-modal-body">
                        <input type="hidden" name="action" value="save_project">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Project ID *</label>
                                <input type="text" name="project_id" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Project Name *</label>
                                <input type="text" name="project_name" class="form-control" required>
                            </div>
                        </div>
                    <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Hotel/Customer</label>
                                <div class="input-group">
                                    <input type="text" id="edit_hotel_name_display" class="form-control" placeholder="Cari atau pilih customer..." onfocus="openCustomerLookup('edit_hotel_name_display','edit_hotel_id', this)" onkeyup="liveFilterCustomerLookup(this.value)">
                                    <button type="button" class="btn btn-secondary" onclick="openCustomerLookup('edit_hotel_name_display','edit_hotel_id', this)" aria-label="Cari Customer">
                                        <iconify-icon icon="ion:search-outline" style="font-size:20px;"></iconify-icon>
                                    </button>
                                </div>
                                <input type="hidden" name="hotel_id" id="edit_hotel_id">
                                <input type="hidden" name="hotel_name" id="edit_hotel_name_text">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" onchange="calculateTotalDays()">
                            </div>
                        <div class="col-md-2">
                                <label class="form-label">Total Days</label>
                                <input type="number" id="total_days" class="form-control" readonly>
                            </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-secondary w-100" onclick="calculateTotalDays()">Calc</button>
                        </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select">
                                    <option value="Implementation">Implementation</option>
                                    <option value="Upgrade">Upgrade</option>
                                    <option value="Maintenance">Maintenance</option>
                                    <option value="Retraining">Retraining</option>
                                    <option value="On Line Training">On Line Training</option>
                                    <option value="On Line Maintenance">On Line Maintenance</option>
                                    <option value="Remote Installation">Remote Installation</option>
                                    <option value="In House Training">In House Training</option>
                                    <option value="Special Request">Special Request</option>
                                    <option value="2nd Implementation">2nd Implementation</option>
                                    <option value="Jakarta Support">Jakarta Support</option>
                                    <option value="Bali Support">Bali Support</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="Scheduled">Scheduled</option>
                                    <option value="Running">Running</option>
                                    <option value="Document">Document</option>
                                    <option value="Document Check">Document Check</option>
                                    <option value="Done">Done</option>
                                    <option value="Cancel">Cancel</option>
                                    <option value="Rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Project Remark</label>
                                <input type="text" name="project_remark" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <h6>Project Details (PIC Assignments)</h6>
                            <div id="detailsContainer"></div>
                            <button type="button" class="btn btn-success btn-sm" onclick="addDetailRow()">+ Add Detail Row</button>
                        </div>
                    </div>
                    <div class="custom-modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" onclick="closeProjectModal()">Close</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Projects List -->
        <div class="card" id="projectsListCard">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-semibold">Show</span>
                    <form method="get" class="d-inline">
                        <select class="form-select form-select-sm w-auto" name="limit" onchange="this.form.submit()">
                            <option value="10" <?= (int)($_GET['limit'] ?? 10)===10?'selected':''; ?>>10</option>
                            <option value="15" <?= (int)($_GET['limit'] ?? 10)===15?'selected':''; ?>>15</option>
                            <option value="20" <?= (int)($_GET['limit'] ?? 10)===20?'selected':''; ?>>20</option>
                        </select>
                    </form>
                </div>
                <a href="#" class="btn btn-primary btn-sm" onclick="openAddModal(); return false;" style="background: linear-gradient(90deg,#6f74f6,#a37cf5); border:none;">
                    <iconify-icon icon="mdi:plus-circle-outline" class="icon"></iconify-icon>
                    Create Project
                </a>
            </div>
            <div class="card-body">
                <style>
                    #projectsListCard .card-header{
                        background: linear-gradient(135deg,#78b8c9 0%, #6aa9ba 100%);
                        color:#fff; border-bottom:none;
                    }
                    #projectsListCard .card-header .form-select{ color:#111; }
                    #projectsListCard .filter-section{ background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px; padding:14px; margin-bottom:16px; }
                    #projectsListCard .filter-row{ display:flex; flex-wrap:wrap; gap:16px; }
                    #projectsListCard .filter-group{ min-width:240px; flex:1; }
                    #projectsListCard .filter-label{ font-weight:600; font-size:12px; color:#374151; margin-bottom:6px; display:block; }
                    #projectsListCard .icon-field{ position:relative; }
                    #projectsListCard .icon-field input{ padding-left:40px; }
                    #projectsListCard .icon-field .icon{ position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#9ca3af; }
                    #projectsListCard .btn-apply{ background:#6BB2C8; border-color:#6BB2C8; }
                        .table-responsive { overflow-x:auto; overflow-y:hidden; border-radius:2px; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
                        .table-header { padding:12px 16px; border:none; border-radius:8px; margin:0; font-weight:600; color:white; font-size:12px; text-transform:uppercase; letter-spacing:.5px; text-align:center; box-shadow:0 2px 8px rgba(79,70,229,.3); transition:all .3s ease; position:relative; overflow:hidden; display:flex; align-items:center; justify-content:center; height:100%; min-height:52px; }
                        .table-header::before { content:''; position:absolute; top:0; left:-100%; width:100%; height:100%; background:linear-gradient(90deg, transparent, rgba(255,255,255,.2), transparent); transition:left .5s; }
                        .table-header:hover::before { left:100%; }
                        .table thead th { padding:0 !important; vertical-align:middle !important; }
                </style>
                <!-- Filter Section (match project.php) -->
                <div class="filter-section">
                    <form method="get" class="filter-form">
                        <div class="filter-row">
                            <div class="filter-group">
                                <label class="filter-label">Search</label>
                                <div class="icon-field">
                                    <input type="text" name="search" class="form-control" placeholder="Search projects..." value="<?= htmlspecialchars($search) ?>">
                                    <span class="icon"><iconify-icon icon="ion:search-outline"></iconify-icon></span>
                                </div>
                            </div>
                            <div class="filter-group" style="min-width:200px;">
                                <label class="filter-label">Status</label>
                                <select class="form-select" name="filter_status">
                                    <option value="">All Status</option>
                                    <?php foreach (['Scheduled','Running','Document','Document Check','Done','Cancel','Rejected'] as $s): ?>
                                        <option value="<?= htmlspecialchars($s) ?>" <?= $filter_status === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-group" style="min-width:200px;">
                                <label class="filter-label">Type</label>
                                <select class="form-select" name="filter_type">
                                    <option value="">All Type</option>
                                    <?php foreach (['Implementation','Upgrade','Maintenance','Retraining','On Line Training','On Line Maintenance','Remote Installation','In House Training','Special Request','2nd Implementation','Jakarta Support','Bali Support','Others'] as $t): ?>
                                        <option value="<?= htmlspecialchars($t) ?>" <?= $filter_type === $t ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-group" style="min-width:200px;">
                                <label class="filter-label">Project Information</label>
                                <select class="form-select" name="filter_project_information">
                                    <option value="">All</option>
                                    <option value="Request" <?= ($filter_project_information === 'Request') ? 'selected' : '' ?>>Request</option>
                                    <option value="Submission" <?= ($filter_project_information === 'Submission') ? 'selected' : '' ?>>Submission</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex gap-2" style="margin-top:12px;">
                            <button type="submit" class="btn-apply btn btn-sm btn-primary-600">Apply Filters</button>
                            <a href="projects.php" class="btn-reset btn btn-sm btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th scope="col"><div class="table-header">Project ID</div></th>
                                <th scope="col"><div class="table-header">Hotel Name</div></th>
                                <th scope="col"><div class="table-header">Project Name</div></th>
                                <th scope="col"><div class="table-header">Start Date</div></th>
                                <th scope="col"><div class="table-header">End Date</div></th>
                                <th scope="col"><div class="table-header">Total Days</div></th>
                                <th scope="col"><div class="table-header">Type</div></th>
                                <th scope="col"><div class="table-header">Status</div></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $project): ?>
                                <?php
                                // Get details count for this project
                                $stmt = $pdo->prepare("SELECT COUNT(*) FROM projects_detail WHERE project_id = ?");
                                $stmt->execute([$project['project_id']]);
                                $detailsCount = $stmt->fetchColumn();
                                
                                                                 // Get sample detail info
                                 $stmt = $pdo->prepare("SELECT pd.user_id, u.full_name FROM projects_detail pd 
                                                      LEFT JOIN users u ON CAST(pd.user_id AS TEXT) = u.user_id 
                                                      WHERE pd.project_id = ? LIMIT 1");
                                 $stmt->execute([$project['project_id']]);
                                 $sampleDetail = $stmt->fetch(PDO::FETCH_ASSOC);
                                ?>
                                <tr class="project-row" data-project-id="<?= htmlspecialchars($project['project_id']) ?>">
                                    <td data-label="Project ID"><?= htmlspecialchars($project['project_id']) ?></td>
                                    <td data-label="Hotel Name"><?= htmlspecialchars($project['hotel_name_text'] ?? '-') ?></td>
                                    <td data-label="Project Name"><?= htmlspecialchars($project['project_name']) ?></td>
                                    <td data-label="Start Date"><?= $project['start_date'] ? date('d M Y', strtotime($project['start_date'])) : '-' ?></td>
                                    <td data-label="End Date"><?= $project['end_date'] ? date('d M Y', strtotime($project['end_date'])) : '-' ?></td>
                                    <td data-label="Total Days"><?= htmlspecialchars($project['total_days'] ?? '-') ?></td>
                                    <td data-label="Type"><span class="type-badge bg-neutral-200 text-neutral-600 px-8 py-4 rounded-pill fw-medium text-sm"><?= htmlspecialchars($project['type'] ?? '-') ?></span></td>
                                    <td data-label="Status"><span class="status-badge bg-neutral-200 text-neutral-600 px-8 py-4 rounded-pill fw-medium text-sm"><?= htmlspecialchars($project['status'] ?? '-') ?></span></td>
                                </tr>
                                <?php if ($detailsCount > 0 && $sampleDetail): ?>
                                    <tr class="table-light">
                                        <td colspan="8" class="small text-muted">
                                            <strong>PIC:</strong> <?= htmlspecialchars($sampleDetail['full_name'] ?? 'Unknown') ?>
                                            <?php if ($detailsCount > 1): ?>
                                                <span class="ms-2">+<?= $detailsCount - 1 ?> more</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Customer Lookup Modal (copied style from project.php)
        document.write(`
<div id="customerLookupModal" class="custom-modal-overlay" style="display:none;">
  <div class="custom-modal" style="max-width:700px;">
    <div class="custom-modal-header">
      <h5 class="custom-modal-title">Select Customer</h5>
      <button type="button" class="custom-modal-close" onclick="document.getElementById('customerLookupModal').style.display='none'">&times;</button>
    </div>
    <div class="custom-modal-body">
      <div style="display:flex; gap:8px; margin-bottom:12px;">
        <input id="customerLookupSearch" type="text" class="custom-modal-input" placeholder="Search by Customer ID or Name..." style="flex:1;">
      </div>
      <div style="max-height:50vh; overflow:auto;">
        <table class="table table-striped mb-0" id="customerLookupTable">
          <thead>
            <tr>
              <th style="width: 160px;"><div class="table-header">Customer ID</div></th>
              <th><div class="table-header">Name</div></th>
            </tr>
          </thead>
          <tbody id="customerLookupBody"></tbody>
        </table>
      </div>
    </div>
    <div class="custom-modal-footer">
      <button type="button" class="custom-btn custom-btn-secondary" onclick="document.getElementById('customerLookupModal').style.display='none'">Close</button>
    </div>
  </div>
</div>`);
        function openAddModal() {
            const modal = document.getElementById('projectModal');
            if (!modal) return;
            document.getElementById('projectModalTitle').textContent = 'Add Project';
            // reset form
            const form = document.getElementById('projectForm');
            if (form) form.reset();
            const details = document.getElementById('detailsContainer');
            if (details) details.innerHTML = '';
            // add initial row
            try { addDetailRow(); } catch(_) {}
            modal.style.display = 'flex';
        }
        function closeProjectModal() {
            const modal = document.getElementById('projectModal');
            if (modal) modal.style.display = 'none';
        }
        let detailRowCount = 0;
        
        function addDetailRow() {
            detailRowCount++;
            const container = document.getElementById('detailsContainer');
            const row = document.createElement('div');
            row.className = 'detail-row';
            row.innerHTML = `
                <div class="row">
                    <input type="hidden" name="details[${detailRowCount}][id]" value="">
                    <div class="col-md-3">
                        <label class="form-label">PIC *</label>
                        <select name="details[${detailRowCount}][user_id]" class="form-select" required>
                            <option value="">Select PIC</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= htmlspecialchars($user['user_id']) ?>"><?= htmlspecialchars($user['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="details[${detailRowCount}][start_date]" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">End Date</label>
                        <input type="date" name="details[${detailRowCount}][end_date]" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="details[${detailRowCount}][status]" class="form-select">
                            <option value="Scheduled">Scheduled</option>
                            <option value="Running">Running</option>
                            <option value="Done">Done</option>
                            <option value="Cancel">Cancel</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Assignment Status</label>
                        <select name="details[${detailRowCount}][assignment_status]" class="form-select">
                            <option value="">-</option>
                            <option value="Leader">Leader</option>
                            <option value="Assist">Assist</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end justify-content-end">
                        <button type="button" class="btn btn-remove" data-remove-btn onclick="removeDetailRow(this)">×</button>
                    </div>
                </div>
                <div class="row mt-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Assignment PIC</label>
                        <select name="details[${detailRowCount}][assignment_pic]" class="form-select">
                            <option value="Request">Request</option>
                            <option value="Assignment">Assignment</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <span class="badge bg-secondary" data-approval-badge>Draft</span>
                    </div>
                    <div class="col-md-2 d-flex align-items-end justify-content-end">
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="approveDetail(this)">Approve</button>
                        <button type="button" class="btn btn-sm btn-outline-warning d-none" onclick="reopenDetail(this)">Reopen</button>
                    </div>
                    <div class="col-md-1"></div>
                </div>
            `;
            container.appendChild(row);
        }
        
        function removeDetailRow(button) {
            button.closest('.detail-row').remove();
        }
		
		function getDetailIdFromRow(row) {
			const idInput = row.querySelector('input[name*="[id]"]');
			return idInput ? idInput.value : '';
		}
		
		async function approveDetail(btn) {
			const row = btn.closest('.detail-row');
			const detailId = getDetailIdFromRow(row);
			if (!detailId) { alert('Simpan project dulu untuk mendapatkan ID detail sebelum approve.'); return; }
			btn.disabled = true;
			try {
				const xhr = new XMLHttpRequest();
				xhr.open('POST', 'project_detail_approval.php', true);
				xhr.setRequestHeader('Content-Type', 'application/json');
				xhr.onreadystatechange = function(){
					if (xhr.readyState !== 4) return;
					btn.disabled = false;
					if (xhr.status >= 200 && xhr.status < 300) {
						let data = {};
						try { data = JSON.parse(xhr.responseText || '{}'); } catch(e){ alert('Approve failed: invalid server response'); return; }
						if (data && data.success) {
							const badge = row.querySelector('[data-approval-badge]');
							if (badge) { badge.textContent = 'Approved'; badge.classList.remove('bg-secondary'); badge.classList.add('bg-success'); }
							row.querySelectorAll('input, select').forEach(el => { if (el.type !== 'hidden') el.disabled = true; });
							const ab = row.querySelector('button.btn-outline-success'); if (ab) ab.classList.add('d-none');
							const rb = row.querySelector('button.btn-outline-warning'); if (rb) rb.classList.remove('d-none');
							const removeBtn = row.querySelector('[data-remove-btn]'); if (removeBtn) removeBtn.classList.add('d-none');
						} else {
							alert((data && data.error) || 'Approve failed');
						}
					} else {
						alert('Network error');
					}
				};
				xhr.onerror = function(){ btn.disabled = false; alert('Network error'); };
				xhr.send(JSON.stringify({ action: 'approve', id: detailId }));
			} catch (e) {
				btn.disabled = false;
				console.error(e);
				alert('Network error');
			}
		}
		
		async function reopenDetail(btn) {
			const row = btn.closest('.detail-row');
			const detailId = getDetailIdFromRow(row);
			if (!detailId) { alert('ID detail tidak ditemukan'); return; }
			btn.disabled = true;
			try {
				const xhr = new XMLHttpRequest();
				xhr.open('POST', 'project_detail_approval.php', true);
				xhr.setRequestHeader('Content-Type', 'application/json');
				xhr.onreadystatechange = function(){
					if (xhr.readyState !== 4) return;
					btn.disabled = false;
					if (xhr.status >= 200 && xhr.status < 300) {
						let data = {};
						try { data = JSON.parse(xhr.responseText || '{}'); } catch(e){ alert('Reopen failed: invalid server response'); return; }
						if (data && data.success) {
							const badge = row.querySelector('[data-approval-badge]');
							if (badge) { badge.textContent = 'Draft'; badge.classList.remove('bg-success'); badge.classList.add('bg-secondary'); }
							row.querySelectorAll('input, select').forEach(el => { if (el.type !== 'hidden') el.disabled = false; });
							const ab = row.querySelector('button.btn-outline-success'); if (ab) ab.classList.remove('d-none');
							const rb = row.querySelector('button.btn-outline-warning'); if (rb) rb.classList.add('d-none');
							const removeBtn = row.querySelector('[data-remove-btn]'); if (removeBtn) removeBtn.classList.remove('d-none');
						} else {
							alert((data && data.error) || 'Reopen failed');
						}
					} else {
						alert('Network error');
					}
				};
				xhr.onerror = function(){ btn.disabled = false; alert('Network error'); };
				xhr.send(JSON.stringify({ action: 'reopen', id: detailId }));
			} catch (e) {
				btn.disabled = false;
				console.error(e);
				alert('Network error');
			}
		}
        
        // Customer lookup (reuse from project.php simplified)
        function openCustomerLookup(targetTextId, targetHiddenId){
          window.lookupTargetTextEl = document.getElementById(targetTextId);
          window.lookupTargetHiddenEl = document.getElementById(targetHiddenId);
          const container = document.getElementById('customerLookupModal');
          if (container) container.style.display = 'flex';
          const q = (window.lookupTargetTextEl && window.lookupTargetTextEl.value) ? window.lookupTargetTextEl.value.trim() : '';
          const searchEl = document.getElementById('customerLookupSearch');
          if (searchEl) { searchEl.value = q; searchEl.oninput = function(){ liveFilterCustomerLookup(this.value); }; searchEl.focus(); }
          document.getElementById('customerLookupBody').innerHTML = '<tr><td colspan="2" style="padding:12px;text-align:center;color:#64748b;">Loading...</td></tr>';
          fetchCustomers(q);
        }
        function liveFilterCustomerLookup(value){
          const q = (value||'').trim();
          const searchEl = document.getElementById('customerLookupSearch');
          if (searchEl) searchEl.value = q;
          if (window.lookupDebounceTimer) clearTimeout(window.lookupDebounceTimer);
          window.lookupDebounceTimer = setTimeout(()=>{ fetchCustomers(q); }, 250);
        }
        function fetchCustomers(q){
          let url = 'lookup_customers.php';
          if (q && q.length) url += '?q='+encodeURIComponent(q);
          const xhr = new XMLHttpRequest();
          xhr.open('GET', url, true);
          xhr.onreadystatechange = function(){
            if (xhr.readyState !== 4) return;
            if (xhr.status >= 200 && xhr.status < 300) {
              let data = [];
              try { data = JSON.parse(xhr.responseText); } catch(_) {}
              renderCustomerLookup(Array.isArray(data)?data:[]);
            } else {
              document.getElementById('customerLookupBody').innerHTML = '<tr><td colspan="2" style="padding:12px;text-align:center;color:#ef4444;">Gagal memuat data</td></tr>';
            }
          };
          xhr.onerror = function(){ document.getElementById('customerLookupBody').innerHTML = '<tr><td colspan="2" style="padding:12px;text-align:center;color:#ef4444;">Gagal memuat data</td></tr>'; };
          xhr.send();
        }
        function escapeHtml(str){ return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }
        function renderCustomerLookup(list){
          const tbody = document.getElementById('customerLookupBody');
          tbody.innerHTML = '';
          if (!Array.isArray(list) || list.length === 0) {
            tbody.innerHTML = '<tr><td colspan="2" style="padding:12px; text-align:center; color:#64748b;">Tidak ada data</td></tr>';
            return;
          }
          const frag = document.createDocumentFragment();
          list.forEach(item=>{
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${escapeHtml(item.customer_id||'')}</td><td>${escapeHtml(item.name||'')}</td>`;
            tr.style.cursor = 'pointer';
            tr.addEventListener('dblclick', ()=>{ selectCustomer(String(item.id), `${escapeHtml(item.customer_id||'')} - ${escapeHtml(item.name||'')}`); });
            tr.addEventListener('click', ()=>{ selectCustomer(String(item.id), `${escapeHtml(item.customer_id||'')} - ${escapeHtml(item.name||'')}`); });
            frag.appendChild(tr);
          });
          tbody.appendChild(frag);
        }
        function selectCustomer(id, label){
          if (window.lookupTargetHiddenEl) window.lookupTargetHiddenEl.value = id;
          if (window.lookupTargetTextEl) {
            window.lookupTargetTextEl.value = label;
            const nameEl = document.getElementById('edit_hotel_name_text');
            if (nameEl) {
              const parts = String(label).split(' - ');
              nameEl.value = parts.length > 1 ? parts.slice(1).join(' - ') : label;
            }
          }
          const container = document.getElementById('customerLookupModal');
          if (container) container.style.display = 'none';
        }
        
        function calculateTotalDays() {
            const startDate = document.querySelector('input[name="start_date"]').value;
            const endDate = document.querySelector('input[name="end_date"]').value;
            const totalDaysEl = document.getElementById('total_days');
            
            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                totalDaysEl.value = diffDays;
            } else {
                totalDaysEl.value = '';
            }
        }

        // Frontend guard: enforce detail dates within project period
        document.addEventListener('input', function(e) {
            if (e.target && e.target.matches('input[type="date"], select')) {
                const headerStart = document.querySelector('input[name="start_date"]').value;
                const headerEnd = document.querySelector('input[name="end_date"]').value;
                if (!headerStart && !headerEnd) return;

                document.querySelectorAll('#detailsContainer .detail-row').forEach(row => {
                    const ds = row.querySelector('input[name*="[start_date]"]').value;
                    const de = row.querySelector('input[name*="[end_date]"]').value;
                    if (ds && headerStart && new Date(ds) < new Date(headerStart)) {
                        row.querySelector('input[name*="[start_date]"]').value = headerStart;
                    }
                    if (de && headerEnd && new Date(de) > new Date(headerEnd)) {
                        row.querySelector('input[name*="[end_date]"]').value = headerEnd;
                    }
                });
            }
        });
        
        let __isLoadingProject = false;
        async function editProject(projectId) {
            if (__isLoadingProject) return;
            __isLoadingProject = true;
            // Prepare modal
            const modal = document.getElementById('projectModal');
            const titleEl = document.getElementById('projectModalTitle');
            if (titleEl) titleEl.textContent = 'Edit Project';
            // Clear form first
            const form = document.getElementById('projectForm');
            if (form) form.reset();
            document.getElementById('detailsContainer').innerHTML = '';

			try {
				const url = 'get_project_data.php?project_id=' + encodeURIComponent(projectId) + '&_=' + Date.now();
				const xhr = new XMLHttpRequest();
				xhr.open('GET', url, true);
				xhr.setRequestHeader('Accept', 'application/json');
				xhr.onreadystatechange = function(){
					if (xhr.readyState !== 4) return;
					__isLoadingProject = false;
					if (xhr.status >= 200 && xhr.status < 300) {
						let data;
						try { data = JSON.parse(xhr.responseText || '{}'); } catch(e){
							console.error('Invalid JSON from get_project_data.php:', xhr.responseText);
							alert('Failed to load project data');
							return;
						}
						if (!data || data.success !== true) {
							console.error('Load error:', data);
							alert('Error loading project: ' + (data && data.error ? data.error : 'Unknown error'));
							return;
						}

						// Populate project header
						document.querySelector('input[name="project_id"]').value = data.project.project_id;
                document.querySelector('input[name="project_name"]').value = data.project.project_name || '';
						document.querySelector('input[name="start_date"]').value = data.project.start_date || '';
						document.querySelector('input[name="end_date"]').value = data.project.end_date || '';
                // Fill total days if present in DB
                (function(){
                    const tdEl = document.getElementById('total_days');
                    const tdVal = data.project.total_days || '';
                    if (tdEl) tdEl.value = tdVal;
                })();
						document.querySelector('select[name="type"]').value = data.project.type || 'Maintenance';
						document.querySelector('select[name="status"]').value = data.project.status || 'Scheduled';
						document.querySelector('input[name="project_remark"]').value = data.project.project_remark || '';

                // Set hotel via lookup fields (no select)
                (function(){
                    const hid = document.getElementById('edit_hotel_id');
                    const hnameHidden = document.getElementById('edit_hotel_name_text');
                    const disp = document.getElementById('edit_hotel_name_display');
                    const hotelId = data.project.hotel_id || '';
                    const hotelName = data.project.hotel_name_text || '';
                    if (hid) hid.value = hotelId;
                    if (hnameHidden) hnameHidden.value = hotelName;
                    if (disp) disp.value = hotelId ? (hotelId + ' - ' + hotelName) : '';
                })();

						// Populate details
						if (Array.isArray(data.details) && data.details.length > 0) {
							data.details.forEach(detail => {
								addDetailRow();
								const lastRow = document.querySelector('#detailsContainer .detail-row:last-child');
								lastRow.querySelector('select[name*="[user_id]"]').value = detail.user_id || '';
								lastRow.querySelector('input[name*="[start_date]"]').value = detail.start_date || '';
								lastRow.querySelector('input[name*="[end_date]"]').value = detail.end_date || '';
								lastRow.querySelector('select[name*="[status]"]').value = detail.status || 'Scheduled';
								lastRow.querySelector('select[name*="[assignment_status]"]').value = detail.assignment_status || '';
								lastRow.querySelector('select[name*="[assignment_pic]"]').value = detail.assignment_pic || 'Request';
								const idInput = lastRow.querySelector('input[name*="[id]"]');
								if (idInput) { idInput.value = detail.id || ''; }
								const badge = lastRow.querySelector('[data-approval-badge]');
								const isApproved = (detail.approved_status === 'Approved');
                        if (isApproved) {
									if (badge) { badge.textContent = 'Approved'; badge.classList.remove('bg-secondary'); badge.classList.add('bg-success'); }
									const approveBtn = lastRow.querySelector('button.btn-outline-success');
									const reopenBtn = lastRow.querySelector('button.btn-outline-warning');
									if (approveBtn) approveBtn.classList.add('d-none');
									if (reopenBtn) reopenBtn.classList.remove('d-none');
                            const removeBtn = lastRow.querySelector('[data-remove-btn]');
                            if (removeBtn) removeBtn.classList.add('d-none');
									lastRow.querySelectorAll('input, select').forEach(el => { if (el.type !== 'hidden') el.disabled = true; });
								} else {
									if (badge) { badge.textContent = 'Draft'; badge.classList.add('bg-secondary'); badge.classList.remove('bg-success'); }
									const approveBtn = lastRow.querySelector('button.btn-outline-success');
									const reopenBtn = lastRow.querySelector('button.btn-outline-warning');
									if (approveBtn) approveBtn.classList.remove('d-none');
									if (reopenBtn) reopenBtn.classList.add('d-none');
                            const removeBtn = lastRow.querySelector('[data-remove-btn]');
                            if (removeBtn) removeBtn.classList.remove('d-none');
								}
							});
						} else {
							addDetailRow();
						}

						if (modal) modal.style.display = 'flex';
					} else {
						console.error('HTTP error', xhr.status, xhr.responseText);
						alert('Failed to load project data: HTTP ' + xhr.status);
					}
				};
				xhr.onerror = function(){ __isLoadingProject = false; alert('Network error'); };
				xhr.send();
			} catch (error) {
				__isLoadingProject = false;
				console.error('Error:', error);
				alert('Failed to load project data');
			}
        }
        
        // Enable click row to open Edit modal
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('table.table tbody tr[data-project-id]').forEach(function(tr){
                tr.style.cursor = 'pointer';
                tr.addEventListener('click', function(){
                    const pid = tr.getAttribute('data-project-id');
                    if (pid) editProject(pid);
                });
            });
        });
    </script>

<?php include './partials/layouts/layoutBottom.php'; ?>
