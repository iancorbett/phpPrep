<?php
require __DIR__ . '/db.php'; 

function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$err = '';
$ok  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
  
    if ($action === 'add') {
      $client = trim($_POST['client_name'] ?? '');
      $number = trim($_POST['policy_number'] ?? '');
      $premium = (float)($_POST['premium'] ?? 0);
      $status = $_POST['status'] ?? 'Pending';
  
      if ($client === '' || $number === '') {
        $err = 'Client and Policy Number are required.';
      } else {
        try {
          $stmt = $pdo->prepare("INSERT INTO policies (client_name, policy_number, premium, status) VALUES (?,?,?,?)");
          $stmt->execute([$client, $number, $premium, $status]);
          $ok = 'Policy added.';
        } catch (PDOException $e) {
          if ($e->getCode() === '23000') {
            $err = 'Policy number must be unique.';
          } else {
            $err = 'DB error: ' . $e->getMessage();
          }
        }
      }
    }

    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $client = trim($_POST['client_name'] ?? '');
        $number = trim($_POST['policy_number'] ?? '');
        $premium = (float)($_POST['premium'] ?? 0);
        $status = $_POST['status'] ?? 'Pending';
    }
    if ($id <= 0 || $client === '' || $number === '') {
        $err = 'Invalid input for update.';
      } else {
        try {
          $stmt = $pdo->prepare("UPDATE policies SET client_name=?, policy_number=?, premium=?, status=? WHERE id=?");
          $stmt->execute([$client, $number, $premium, $status, $id]);
          $ok = 'Policy updated.';
        } catch (PDOException $e) {
          if ($e->getCode() === '23000') {
            $err = 'Policy number must be unique.';
          } else {
            $err = 'DB error: ' . $e->getMessage();
          }
        }
      }
      if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
          $err = 'Invalid ID for delete.';
        } else {
          $stmt = $pdo->prepare("DELETE FROM policies WHERE id=?");
          $stmt->execute([$id]);
          $ok = 'Policy deleted.';
        }
      }
}

$search = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
$validStatuses = ['Active','Pending','Expired'];

$sql = "SELECT * FROM policies WHERE 1=1";
$params = [];
if ($search !== '') {
  $sql .= " AND (client_name LIKE :q OR policy_number LIKE :q)";
  $params[':q'] = "%$search%";
}
if ($status !== '' && in_array($status, $validStatuses, true)) {
  $sql .= " AND status = :status";
  $params[':status'] = $status;
}
$sql .= " ORDER BY created_at DESC, id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$policies = $stmt->fetchAll();

$countTotal   = (int)$pdo->query("SELECT COUNT(*) FROM policies")->fetchColumn();
$countActive  = (int)$pdo->query("SELECT COUNT(*) FROM policies WHERE status='Active'")->fetchColumn();
$countPending = (int)$pdo->query("SELECT COUNT(*) FROM policies WHERE status='Pending'")->fetchColumn();
$countExpired = (int)$pdo->query("SELECT COUNT(*) FROM policies WHERE status='Expired'")->fetchColumn();

