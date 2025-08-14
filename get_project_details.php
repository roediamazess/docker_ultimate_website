<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';

header('Content-Type: application/json; charset=utf-8');

try {
	if (!isset($_SESSION['user_id'])) {
		echo json_encode([]);
		exit;
	}
	$project_id = isset($_GET['project_id']) ? trim($_GET['project_id']) : '';
	if ($project_id === '') { echo json_encode([]); exit; }

$sql = 'SELECT pd.user_id, u.full_name, pd.start_date, pd.end_date, pd.total_days, pd.status, pd.assignment_status, pd.assignment_pic
            FROM projects_detail pd
            LEFT JOIN users u ON pd.user_id = u.user_id
            WHERE pd.project_id = ?
            ORDER BY COALESCE(pd.start_date, pd.id) ASC, pd.id ASC';
	$stmt = $pdo->prepare($sql);
	$stmt->execute([$project_id]);
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	// Log for debugging
	try {
		require_once 'user_utils.php';
		log_user_activity('get_project_details', 'project_id=' . $project_id . ' found=' . count($rows));
	} catch (Throwable $e) {}
	
	echo json_encode($rows, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
	echo json_encode([]);
}




