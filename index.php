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
}