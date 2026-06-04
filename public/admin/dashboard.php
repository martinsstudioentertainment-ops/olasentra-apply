<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Get statistics
try {
    $totalStaff = 0;
    $incompleteCount = 0;
    $pendingCount = 0;
    $verifiedCount = 0;
    $expiredCount = 0;

    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM staff_master");
    $totalStaff = (int)$stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM staff_master WHERE profile_status = 'Incomplete'");
    $incompleteCount = (int)$stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM staff_master WHERE profile_status = 'Pending Review'");
    $pendingCount = (int)$stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM staff_master WHERE profile_status = 'Verified'");
    $verifiedCount = (int)$stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM staff_master WHERE profile_status = 'Expired PSA'");
    $expiredCount = (int)$stmt->fetch()['total'];

} catch (Exception $e) {
    $totalStaff = 0;
    $incompleteCount = 0;
    $pendingCount = 0;
    $verifiedCount = 0;
    $expiredCount = 0;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Admin Dashboard</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#020617;
    color:#fff;
    font-family:Arial,sans-serif;
}

.header{
    background:#0f172a;
    padding:20px;
    border-bottom:1px solid #1e293b;
}

.header h1{
    font-size:24px;
}

.header p {
    margin-top: 5px;
    color: #94a3b8;
}

.container{
    padding:20px;
    max-width:1200px;
    margin:auto;
}

.top-links{
    margin-bottom:20px;
}

.top-links a{
    color:#60a5fa;
    text-decoration:none;
    margin-right:15px;
}

.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:20px;
    margin-bottom:30px;
}

.card{
    background:#0f172a;
    border:1px solid #1e293b;
    border-radius:15px;
    padding:20px;
}

.card h3{
    margin-bottom:10px;
    color:#94a3b8;
    font-size:14px;
}

.count{
    font-size:32px;
    font-weight:bold;
}

.stat-card {
    border-left: 4px solid #2563eb;
}

.stat-card.incomplete {
    border-left-color: #92400e;
}

.stat-card.pending {
    border-left-color: #ca8a04;
}

.stat-card.verified {
    border-left-color: #15803d;
}

.stat-card.expired {
    border-left-color: #b91c1c;
}

.actions{
    margin-top:30px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
    gap:20px;
}

.btn{
    display:block;
    text-align:center;
    text-decoration:none;
    padding:15px;
    border-radius:10px;
    color:#fff;
    font-weight:bold;
    border:none;
    cursor:pointer;
}

.btn-import{
    background:#2563eb;
}

.btn-sync{
    background:#16a34a;
}

.btn-staff{
    background:#7c3aed;
}

.btn-compliance{
    background:#d97706;
}

.btn-payroll{
    background:#0891b2;
}

.btn-settings{
    background:#64748b;
}

.btn-logout{
    background:#dc2626;
}

.footer{
    margin-top:40px;
    opacity:.7;
    text-align:center;
}

</style>

</head>

<body>

<div class="header">

<h1>Staff Application Admin Dashboard</h1>

<p>
Welcome, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Administrator') ?>
</p>

</div>

<div class="container">

<div class="top-links">
    <a href="staff-list.php">Staff Directory</a>
    <a href="psa-compliance.php">PSA Compliance</a>
    <a href="payroll.php">Payroll</a>
    <a href="settings.php">Settings</a>
    <a href="logout.php">Logout</a>
</div>

<div class="cards">

<div class="card">
<h3>Total Staff</h3>
<div class="count"><?= $totalStaff ?></div>
</div>

<div class="card stat-card pending">
<h3>Pending Review</h3>
<div class="count"><?= $pendingCount ?></div>
</div>

<div class="card stat-card verified">
<h3>Verified</h3>
<div class="count"><?= $verifiedCount ?></div>
</div>

<div class="card stat-card incomplete">
<h3>Incomplete</h3>
<div class="count"><?= $incompleteCount ?></div>
</div>

<div class="card stat-card expired">
<h3>Expired PSA</h3>
<div class="count"><?= $expiredCount ?></div>
</div>

</div>

<div class="actions">

<a class="btn btn-staff" href="staff-list.php">
View Staff Directory
</a>

<a class="btn btn-compliance" href="psa-compliance.php">
PSA Compliance Report
</a>

<a class="btn btn-payroll" href="payroll.php">
Payroll Management
</a>

<a class="btn btn-import" href="/import-main-staff.php">
Import New Applicants
</a>

<a class="btn btn-sync" href="/sync/google-sync.php">
Sync Google Sheets
</a>

<a class="btn btn-settings" href="settings.php">
Settings
</a>

<a class="btn btn-logout" href="logout.php">
Logout
</a>

</div>

<div class="footer">

Admin System Version 1.0 | Last Updated: <?= date('Y-m-d H:i:s') ?>

</div>

</div>

</body>

</html>
