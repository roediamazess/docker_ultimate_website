<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';

header('Content-Type: application/json; charset=utf-8');

try {
	if (!isset($_SESSION['user_id'])) {
		echo json_encode(['success' => false, 'error' => 'Not authenticated']);
		exit;
	}
	$raw = file_get_contents('php://input');
	$payload = json_decode($raw, true);
	$action = $payload['action'] ?? '';
	$id = isset($payload['id']) ? trim((string)$payload['id']) : '';
	if ($id === '' || !in_array($action, ['approve','reopen'], true)) {
		echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
		exit;
	}
	
	if ($action === 'approve') {
		$stmt = $pdo->prepare("UPDATE projects_detail SET approved_status = 'Approved', approved_by = ?, approved_at = NOW() WHERE id = ?");
		$stmt->execute([$_SESSION['user_id'], $id]);
		echo json_encode(['success' => true, 'status' => 'Approved']);
		return;
	}
	if ($action === 'reopen') {
		$stmt = $pdo->prepare("UPDATE projects_detail SET approved_status = NULL, approved_by = NULL, approved_at = NULL WHERE id = ?");
		$stmt->execute([$id]);
		echo json_encode(['success' => true, 'status' => 'Draft']);
		return;
	}
	
	echo json_encode(['success' => false, 'error' => 'Unsupported action']);
} catch (Throwable $e) {
	echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
