<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_once 'user_utils.php';

require_login();

// CSRF helpers
function csrf_field_pd() {
	if (!isset($_SESSION['csrf_token_pd'])) {
		$_SESSION['csrf_token_pd'] = bin2hex(random_bytes(32));
	}
	return '<input type="hidden" name="csrf_token_pd" value="' . $_SESSION['csrf_token_pd'] . '">';
}
function csrf_verify_pd() {
	return isset($_POST['csrf_token_pd']) && hash_equals($_SESSION['csrf_token_pd'], $_POST['csrf_token_pd']);
}

$message = '';

// Ensure projects_detail table and columns per spec
try {
	$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
	if ($driver === 'pgsql') {
		$pdo->exec("CREATE TABLE IF NOT EXISTS projects_detail (
			id SERIAL PRIMARY KEY,
			project_id VARCHAR(150) NOT NULL,
			customer_id VARCHAR(150) NULL,
			customer_name TEXT NULL,
			project_name TEXT NULL,
			project_remark TEXT NULL,
			user_id INT NOT NULL,
			start_date DATE NOT NULL,
			end_date DATE NULL,
			total_days INT NULL,
			type VARCHAR(80) NULL,
			status VARCHAR(60) NULL,
			assignment_status VARCHAR(30) NULL,
			assignment_pic VARCHAR(30) NULL,
			handover_official_report DATE NULL,
			handover_days INT NULL,
			ketertiban_admin VARCHAR(20) NULL,
			point_ach NUMERIC NULL,
			point_req NUMERIC NULL,
			point_percent NUMERIC NULL,
			month VARCHAR(20) NULL,
			quarter VARCHAR(20) NULL,
			week_no VARCHAR(20) NULL,
			s1_estimation_kpi2 TEXT NULL,
			s1_over_days TEXT NULL,
			s1_count_of_email_sent TEXT NULL,
			s2_email_sent TEXT NULL,
			s3_email_sent TEXT NULL,
			created_by INT NULL,
			created_at TIMESTAMP NOT NULL DEFAULT NOW()
		);");
		$pdo->exec("CREATE INDEX IF NOT EXISTS idx_pd_project ON projects_detail(project_id);");
	} else {
		$pdo->exec("CREATE TABLE IF NOT EXISTS projects_detail (
			id INT AUTO_INCREMENT PRIMARY KEY,
			project_id VARCHAR(150) NOT NULL,
			customer_id VARCHAR(150) NULL,
			customer_name TEXT NULL,
			project_name TEXT NULL,
			project_remark TEXT NULL,
			user_id INT NOT NULL,
			start_date DATE NOT NULL,
			end_date DATE NULL,
			total_days INT NULL,
			type VARCHAR(80) NULL,
			status VARCHAR(60) NULL,
			assignment_status VARCHAR(30) NULL,
			assignment_pic VARCHAR(30) NULL,
			handover_official_report DATE NULL,
			handover_days INT NULL,
			ketertiban_admin VARCHAR(20) NULL,
			point_ach DECIMAL(12,2) NULL,
			point_req DECIMAL(12,2) NULL,
			point_percent DECIMAL(6,2) NULL,
			month VARCHAR(20) NULL,
			quarter VARCHAR(20) NULL,
			week_no VARCHAR(20) NULL,
			s1_estimation_kpi2 TEXT NULL,
			s1_over_days TEXT NULL,
			s1_count_of_email_sent TEXT NULL,
			s2_email_sent TEXT NULL,
			s3_email_sent TEXT NULL,
			created_by INT NULL,
			created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			KEY idx_pd_project (project_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
	}
} catch (Throwable $e) {
}

// Fetch header project by project_id
$project_id = trim($_GET['project_id'] ?? '');
if ($project_id === '') {
header('Location: projects.php');
	exit;
}

$stmt = $pdo->prepare('SELECT * FROM projects WHERE project_id = ? LIMIT 1');
$stmt->execute([$project_id]);
$header = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$header) {
header('Location: projects.php');
	exit;
}

// Helpers
function compute_total_days_nullable($start_date, $end_date) {
	if ($start_date && $end_date) {
		try { $diff = date_diff(date_create($start_date), date_create($end_date)); return $diff->days + 1; } catch (Throwable $e) { return null; }
	}
	return null;
}
function compute_handover_days($end_date, $handover) {
	if ($end_date && $handover) {
		try { $diff = date_diff(date_create($end_date), date_create($handover)); return (int)$diff->days; } catch (Throwable $e) { return null; }
	}
	return null;
}
function compute_ketertiban($handover_days) {
	if ($handover_days === null) return null;
	$d = (int)$handover_days;
	if ($d <= 3) return 'Excellent';
	if ($d <= 7) return 'Good';
	if ($d <= 14) return 'Average';
	if ($d <= 30) return 'Poor';
	return 'Bad';
}
function compute_month_quarter_week($start_date) {
	if (!$start_date) return [null, null, null];
	$ts = strtotime($start_date);
	$month = date('F', $ts);
	$q = (int)ceil((int)date('n', $ts)/3);
	$quarter = 'Quarter ' . $q;
	$week = 'Week ' . date('W', $ts);
	return [$month, $quarter, $week];
}

// Handle create/update/delete detail
if (isset($_POST['save_detail']) && csrf_verify_pd()) {
	$id = isset($_POST['detail_id']) && $_POST['detail_id'] !== '' ? (int)$_POST['detail_id'] : null;
	$user_id = $_POST['user_id'] !== '' ? (int)$_POST['user_id'] : null;
	$start_date_d = $_POST['start_date_d'] ?: null;
	$end_date_d = $_POST['end_date_d'] ?: null;
	$status_d = $_POST['status_d'] ?: null;
	$assignment_status = $_POST['assignment_status'] ?: null;
	$assignment_pic = $_POST['assignment_pic'] ?: null;
	$handover_official_report = $_POST['handover_official_report'] ?: null;
	$point_ach = $_POST['point_ach'] !== '' ? $_POST['point_ach'] : null;
	$point_req = $_POST['point_req'] !== '' ? $_POST['point_req'] : null;
	$s1_estimation_kpi2 = trim((string)($_POST['s1_estimation_kpi2'] ?? ''));
	$s1_over_days = trim((string)($_POST['s1_over_days'] ?? ''));
	$s1_count_of_email_sent = trim((string)($_POST['s1_count_of_email_sent'] ?? ''));
	$s2_email_sent = trim((string)($_POST['s2_email_sent'] ?? ''));
	$s3_email_sent = trim((string)($_POST['s3_email_sent'] ?? ''));

	if (!$user_id || !$start_date_d) {
		$message = 'User dan Start Date wajib.';
	} else {
		$total_days_d = compute_total_days_nullable($start_date_d, $end_date_d);
		$handover_days = compute_handover_days($end_date_d, $handover_official_report);
		$ketertiban_admin = compute_ketertiban($handover_days);
		$point_percent = ($point_ach !== null && $point_req) ? (float)$point_ach / (float)$point_req * 100.0 : null;
		list($monthVal, $quarterVal, $weekNo) = compute_month_quarter_week($start_date_d);
		$type_d = $header['type'] ?? null;
		$created_by = $_SESSION['user_id'] ?? null;
		if ($id) {
			$sql = 'UPDATE projects_detail SET user_id=?, start_date=?, end_date=?, total_days=?, status=?, assignment_status=?, assignment_pic=?, handover_official_report=?, handover_days=?, ketertiban_admin=?, point_ach=?, point_req=?, point_percent=?, month=?, quarter=?, week_no=?, s1_estimation_kpi2=?, s1_over_days=?, s1_count_of_email_sent=?, s2_email_sent=?, s3_email_sent=? WHERE id=?';
			$pdo->prepare($sql)->execute([$user_id, $start_date_d, $end_date_d, $total_days_d, $status_d, $assignment_status, $assignment_pic, $handover_official_report, $handover_days, $ketertiban_admin, $point_ach, $point_req, $point_percent, $monthVal, $quarterVal, $weekNo, $s1_estimation_kpi2, $s1_over_days, $s1_count_of_email_sent, $s2_email_sent, $s3_email_sent, $id]);
			$message = 'Detail updated';
		} else {
			$sql = 'INSERT INTO projects_detail (project_id, customer_id, customer_name, project_name, project_remark, user_id, start_date, end_date, total_days, type, status, assignment_status, assignment_pic, handover_official_report, handover_days, ketertiban_admin, point_ach, point_req, point_percent, month, quarter, week_no, s1_estimation_kpi2, s1_over_days, s1_count_of_email_sent, s2_email_sent, s3_email_sent, created_by, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, ?, ?)';
			$pdo->prepare($sql)->execute([
				$header['project_id'], $header['hotel_id'] ?? null, $header['hotel_name_text'] ?? null, $header['project_name'] ?? null, $header['project_remark'] ?? null,
				$user_id, $start_date_d, $end_date_d, $total_days_d, $type_d, $status_d, $assignment_status, $assignment_pic,
				$handover_official_report, $handover_days, $ketertiban_admin, $point_ach, $point_req, $point_percent, $monthVal, $quarterVal, $weekNo,
				$s1_estimation_kpi2, $s1_over_days, $s1_count_of_email_sent, $s2_email_sent, $s3_email_sent, $created_by, date('Y-m-d H:i:s')
			]);
			$message = 'Detail created';
		}
	}
}

if (isset($_POST['delete_detail']) && csrf_verify_pd()) {
	$did = (int)$_POST['detail_id'];
	$pdo->prepare('DELETE FROM projects_detail WHERE id = ?')->execute([$did]);
	$message = 'Detail deleted';
}

// Load details
$details = [];
$st = $pdo->prepare('SELECT pd.*, u.full_name FROM projects_detail pd LEFT JOIN users u ON pd.user_id = u.user_id WHERE pd.project_id = ? ORDER BY pd.start_date ASC, pd.id ASC');
$st->execute([$project_id]);
$details = $st->fetchAll(PDO::FETCH_ASSOC);

include './partials/layouts/layoutTop.php';
?>

<div class="content-wrapper">
	<div class="container-xxl">
		<div class="row mb-3">
			<div class="col-12 d-flex align-items-center justify-content-between">
				<h4 class="mb-0">Project Details - <?= htmlspecialchars($project_id) ?></h4>
				<a href="projects.php" class="btn btn-sm btn-secondary">Back to Projects</a>
			</div>
		</div>
		<?php if ($message): ?>
		<div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
		<?php endif; ?>

		<div class="card mb-3">
			<div class="card-body">
				<form method="post" class="row g-3">
					<?= csrf_field_pd() ?>
					<input type="hidden" name="detail_id" id="detail_id">
					<div class="col-md-3">
						<label class="form-label">User (PIC) *</label>
						<div class="input-group">
							<input type="text" id="pic_display" class="form-control" placeholder="Cari atau pilih PIC..." onfocus="openUserLookup('pic_display','user_id', this)" onkeyup="liveFilterUserLookup(this.value)">
							<button type="button" class="btn btn-secondary" onclick="openUserLookup('pic_display','user_id', this)"><iconify-icon icon="ion:search-outline" style="font-size:20px;"></iconify-icon></button>
						</div>
						<input type="hidden" name="user_id" id="user_id" required>
					</div>
					<div class="col-md-2">
						<label class="form-label">Start Date *</label>
						<input type="date" name="start_date_d" id="start_date_d" class="form-control" required>
					</div>
					<div class="col-md-2">
						<label class="form-label">End Date</label>
						<input type="date" name="end_date_d" id="end_date_d" class="form-control">
					</div>
					<div class="col-md-2">
						<label class="form-label">Status</label>
						<select name="status_d" id="status_d" class="form-select">
							<option value="Scheduled">Scheduled</option>
							<option value="Running">Running</option>
							<option value="Document">Document</option>
							<option value="Document Check">Document Check</option>
							<option value="Done">Done</option>
							<option value="Cancel">Cancel</option>
							<option value="Rejected">Rejected</option>
						</select>
					</div>
					<div class="col-md-2">
						<label class="form-label">Assignment Status</label>
						<select name="assignment_status" class="form-select">
							<option value="Leader">Leader</option>
							<option value="Assist">Assist</option>
						</select>
					</div>
					<div class="col-md-2">
						<label class="form-label">Assignment PIC</label>
						<select name="assignment_pic" class="form-select">
							<option value="Request">Request</option>
							<option value="Assignment">Assignment</option>
						</select>
					</div>
					<div class="col-md-2">
						<label class="form-label">Handover Official Report</label>
						<input type="date" name="handover_official_report" class="form-control">
					</div>
					<div class="col-md-2">
						<label class="form-label">Point Ach</label>
						<input type="number" step="0.01" name="point_ach" class="form-control">
					</div>
					<div class="col-md-2">
						<label class="form-label">Point Req</label>
						<input type="number" step="0.01" name="point_req" class="form-control">
					</div>
					<div class="col-md-2">
						<label class="form-label">S1 Estimation KPI2</label>
						<input type="text" name="s1_estimation_kpi2" class="form-control">
					</div>
					<div class="col-md-2">
						<label class="form-label">S1 Over Days</label>
						<input type="text" name="s1_over_days" class="form-control">
					</div>
					<div class="col-md-2">
						<label class="form-label">S1 Count of Email Sent</label>
						<input type="text" name="s1_count_of_email_sent" class="form-control">
					</div>
					<div class="col-md-2">
						<label class="form-label">S2 Email Sent</label>
						<input type="text" name="s2_email_sent" class="form-control">
					</div>
					<div class="col-md-2">
						<label class="form-label">S3 Email Sent</label>
						<input type="text" name="s3_email_sent" class="form-control">
					</div>
					<div class="col-12">
						<button type="submit" name="save_detail" class="btn btn-primary">Save Detail</button>
						<button type="reset" class="btn btn-secondary" onclick="document.getElementById('detail_id').value=''; document.getElementById('pic_display').value=''; document.getElementById('user_id').value='';">Reset</button>
					</div>
				</form>
			</div>
		</div>

		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-striped mb-0">
						<thead>
							<tr>
								<th>User</th>
								<th>Start</th>
								<th>End</th>
								<th>Total</th>
								<th>Status</th>
								<th>Assignment</th>
								<th>Handover</th>
								<th>Ketertiban</th>
								<th>Point %</th>
								<th>Month</th>
								<th>Quarter</th>
								<th>Week</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($details as $d): ?>
							<tr>
								<td><?= htmlspecialchars($d['full_name'] ?? $d['user_id']) ?></td>
								<td><?= htmlspecialchars($d['start_date'] ?: '-') ?></td>
								<td><?= htmlspecialchars($d['end_date'] ?: '-') ?></td>
								<td><?= htmlspecialchars($d['total_days'] ?? '-') ?></td>
								<td><?= htmlspecialchars($d['status'] ?? '-') ?></td>
								<td><?= htmlspecialchars(($d['assignment_status'] ?? '-') . ' / ' . ($d['assignment_pic'] ?? '-')) ?></td>
								<td><?= htmlspecialchars($d['handover_official_report'] ?? '-') ?></td>
								<td><?= htmlspecialchars($d['ketertiban_admin'] ?? '-') ?></td>
								<td><?= isset($d['point_percent']) ? htmlspecialchars(number_format((float)$d['point_percent'], 2)) : '-' ?></td>
								<td><?= htmlspecialchars($d['month'] ?? '-') ?></td>
								<td><?= htmlspecialchars($d['quarter'] ?? '-') ?></td>
								<td><?= htmlspecialchars($d['week_no'] ?? '-') ?></td>
								<td>
									<form method="post" style="display:inline-block;">
										<?= csrf_field_pd() ?>
										<input type="hidden" name="detail_id" value="<?= (int)$d['id'] ?>">
										<button type="button" class="btn btn-sm btn-info" onclick="fillEdit(<?= (int)$d['id'] ?>)">Edit</button>
										<button type="submit" name="delete_detail" class="btn btn-sm btn-danger" onclick="return confirm('Delete detail?')">Delete</button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Reuse User Lookup Modal -->
<div id="userLookupModal" class="custom-modal-overlay" style="display:none;">
	<div class="custom-modal" style="max-width:700px;">
		<div class="custom-modal-header">
			<h5 class="custom-modal-title">Select PIC</h5>
			<button type="button" class="custom-modal-close" onclick="document.getElementById('userLookupModal').style.display='none'">&times;</button>
		</div>
		<div class="custom-modal-body">
			<input id="userLookupSearch" type="text" class="custom-modal-input" placeholder="Search by User ID or Full Name..." style="margin-bottom:12px;">
			<div style="max-height:50vh; overflow:auto;">
				<table class="table table-striped mb-0" id="userLookupTable">
					<thead>
						<tr>
							<th style="width: 180px;"><div class="table-header">User ID</div></th>
							<th><div class="table-header">Full Name</div></th>
						</tr>
					</thead>
					<tbody id="userLookupBody"></tbody>
				</table>
			</div>
		</div>
		<div class="custom-modal-footer">
			<button type="button" class="custom-btn custom-btn-secondary" onclick="document.getElementById('userLookupModal').style.display='none'">Close</button>
		</div>
	</div>
</div>

<script>
let lookupUserTargetTextEl = null, lookupUserTargetHiddenEl = null, lookupUserDebounceTimer = null;
function openUserLookup(targetTextId, targetHiddenId){
	lookupUserTargetTextEl = document.getElementById(targetTextId);
	lookupUserTargetHiddenEl = document.getElementById(targetHiddenId);
	const container = document.getElementById('userLookupModal');
	container.style.display = 'flex';
	const q = (lookupUserTargetTextEl && lookupUserTargetTextEl.value) ? lookupUserTargetTextEl.value.trim() : '';
	const searchEl = document.getElementById('userLookupSearch');
	if (searchEl) { searchEl.value = q; searchEl.oninput = function(){ liveFilterUserLookup(this.value); }; searchEl.focus(); }
	document.getElementById('userLookupBody').innerHTML = '<tr><td colspan="2" style="padding:12px;text-align:center;color:#64748b;">Loading...</td></tr>';
	fetchUsers(q);
}
function renderUserLookup(list){
	const tbody = document.getElementById('userLookupBody');
	tbody.innerHTML = '';
	if (!Array.isArray(list) || list.length === 0) {
		tbody.innerHTML = '<tr><td colspan="2" style="padding:12px; text-align:center; color:#64748b;">Tidak ada data</td></tr>';
		return;
	}
	const frag = document.createDocumentFragment();
	list.forEach(item=>{
		const tr = document.createElement('tr');
		tr.innerHTML = `<td>${escapeHtml(item.user_id||'')}</td><td>${escapeHtml(item.full_name||'')}</td>`;
		tr.style.cursor = 'pointer';
		tr.addEventListener('dblclick', ()=>{ selectUser(String(item.user_id), `${escapeHtml(item.full_name||'')}`); });
		frag.appendChild(tr);
	});
	tbody.appendChild(frag);
}
function liveFilterUserLookup(value){
	const q = (value||'').trim();
	const searchEl = document.getElementById('userLookupSearch');
	if (searchEl) searchEl.value = q;
	if (lookupUserDebounceTimer) clearTimeout(lookupUserDebounceTimer);
	lookupUserDebounceTimer = setTimeout(()=>{ fetchUsers(q); }, 250);
}
function fetchUsers(q){
	let url = 'lookup_users.php';
	if (q && q.length) url += '?q='+encodeURIComponent(q);
	try {
		const xhr = new XMLHttpRequest();
		xhr.open('GET', url, true);
		xhr.withCredentials = true;
		xhr.onreadystatechange = function(){
			if (xhr.readyState !== 4) return;
			if (xhr.status >= 200 && xhr.status < 300) {
				let data = [];
				try { data = JSON.parse(xhr.responseText); } catch(_) { data = []; }
				renderUserLookup(Array.isArray(data)?data:[]);
			} else {
				document.getElementById('userLookupBody').innerHTML = '<tr><td colspan="2" style="padding:12px;text-align:center;color:#ef4444;">Gagal memuat data</td></tr>';
			}
		};
		xhr.onerror = function(){ document.getElementById('userLookupBody').innerHTML = '<tr><td colspan="2" style="padding:12px;text-align:center;color:#ef4444;">Gagal memuat data</td></tr>'; };
		xhr.send();
	} catch (e) {
		document.getElementById('userLookupBody').innerHTML = '<tr><td colspan="2" style="padding:12px;text-align:center;color:#ef4444;">Gagal memuat data</td></tr>';
	}
}
function selectUser(id, label){
	if (lookupUserTargetHiddenEl) lookupUserTargetHiddenEl.value = id;
	if (lookupUserTargetTextEl) lookupUserTargetTextEl.value = label;
	document.getElementById('userLookupModal').style.display = 'none';
}
function escapeHtml(str){return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');}

// Fill form for editing
function fillEdit(id){
	var row = Array.from(document.querySelectorAll('table tbody tr')).find(tr => tr.querySelector('form input[name="detail_id"]').value == id);
	if (!row) return;
	document.getElementById('detail_id').value = id;
	document.getElementById('pic_display').value = row.children[0].innerText.trim();
	// Cannot recover user_id from name; advise to pick again or embed data attributes in future
}
</script>

<?php include './partials/layouts/layoutBottom.php'; ?>




