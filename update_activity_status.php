<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
header('Content-Type: application/json');

try {
  require_login();
  $body = json_decode(file_get_contents('php://input'), true) ?: [];
  $id = (int)($body['id'] ?? 0);
  $status = trim($body['status'] ?? '');
  $allowed = ['Open','On Progress','Need Requirement','Done','Cancel'];
  
  if (!$id || !in_array($status, $allowed, true)) {
    http_response_code(400); 
    echo json_encode(['success'=>false,'message'=>'Invalid payload']); 
    exit;
  }
  
  $stmt = $pdo->prepare('UPDATE activities SET status=? WHERE id=?');
  $stmt->execute([$status, $id]);
  
  // Just return success without message to avoid duplicate notifications
  echo json_encode(['success'=>true]);
  
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
?>

