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

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editRow = null;
if ($editId) {
  $st = $pdo->prepare("SELECT * FROM policies WHERE id=?");
  $st->execute([$editId]);
  $editRow = $st->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Policy Tracker</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <div class="wrap">
    <header class="card">
      <h1> Policy Tracker</h1>
      <p class="muted">Simple, fast, and built with raw PHP + MySQL.</p>
    </header>

    <section class="card">
      <h2>Add Policy</h2>
      <form class="row">
        <div>
          <div class="muted" style="font-size:12px;">Client</div>
          <input type="text" placeholder="Alice Johnson" />
        </div>
        <div>
          <div class="muted" style="font-size:12px;">Policy #</div>
          <input type="text" placeholder="POL-1001" />
        </div>
        <div>
          <div class="muted" style="font-size:12px;">Premium ($)</div>
          <input type="number" step="0.01" value="0.00" />
        </div>
        <div>
          <div class="muted" style="font-size:12px;">Status</div>
          <select>
            <option>Active</option>
            <option selected>Pending</option>
            <option>Expired</option>
          </select>
        </div>
        <button type="submit">Add</button>
      </form>
    </section>

    <section class="card">
      <h2>Policies</h2>
      <table>
        <thead>
          <tr>
            <th>Client</th>
            <th>Policy #</th>
            <th>Premium</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Alice Johnson</td>
            <td class="mono">POL-1001</td>
            <td>$120.00</td>
            <td><span class="pill green">Active</span></td>
            <td class="muted">2025-10-12</td>
            <td class="flex">
              <a href="#">Edit</a>
              <button>Delete</button>
            </td>
          </tr>
          <tr>
            <td>Beacon Logistics</td>
            <td class="mono">POL-1002</td>
            <td>$350.50</td>
            <td><span class="pill yellow">Pending</span></td>
            <td class="muted">2025-10-10</td>
            <td class="flex">
              <a href="#">Edit</a>
              <button>Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </section>

    <footer class="card" style="text-align:center;">
      <p class="muted">© 2025 Policy Tracker — Built with PHP</p>
    </footer>
  </div>
</body>
</html>
