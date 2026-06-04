<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$message = '';
$error = '';

try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM staff_master WHERE profile_status = 'Verified'");
    $verifiedCount = (int)$stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM staff_master WHERE bank_iban IS NOT NULL AND bank_iban != ''");
    $withIbanCount = (int)$stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM staff_master WHERE national_insurance IS NOT NULL AND national_insurance != ''");
    $withPpsCount = (int)$stmt->fetch()['total'];

} catch (Exception $e) {
    $verifiedCount = 0;
    $withIbanCount = 0;
    $withPpsCount = 0;
}

if (($_GET['action'] ?? '') === 'export') {
    
    try {
        $stmt = $pdo->query("
            SELECT 
                id,
                first_name,
                last_name,
                email,
                phone,
                national_insurance,
                bank_iban,
                profile_status,
                verified_at
            FROM staff_master
            WHERE profile_status = 'Verified'
            ORDER BY id ASC
        ");
        
        $payrollData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="payroll_export_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        fputcsv($output, [
            'ID',
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'PPS Number',
            'IBAN',
            'Status',
            'Verified At'
        ]);
        
        foreach ($payrollData as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
        
    } catch (Exception $e) {
        $error = 'Export failed: ' . $e->getMessage();
    }
}

try {
    $stmt = $pdo->query("
        SELECT 
            id,
            first_name,
            last_name,
            email,
            phone,
            national_insurance,
            bank_iban,
            profile_status,
            verified_at
        FROM staff_master
        WHERE profile_status = 'Verified'
        ORDER BY id ASC
        LIMIT 200
    ");
    
    $payroll = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = 'Failed to load payroll data: ' . $e->getMessage();
    $payroll = [];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Payroll Management</title>

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

.container{
padding:20px;
max-width:1400px;
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

.stats{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
gap:20px;
margin-bottom:30px;
}

.stat-card{
background:#0f172a;
border:1px solid #1e293b;
border-radius:15px;
padding:20px;
border-left:4px solid #2563eb;
}

.stat-card h3{
color:#94a3b8;
font-size:14px;
margin-bottom:10px;
}

.stat-value{
font-size:32px;
font-weight:bold;
}

.card{
background:#0f172a;
border:1px solid #1e293b;
border-radius:15px;
padding:20px;
margin-bottom:20px;
}

.btn{
display:inline-block;
padding:12px 20px;
border:none;
border-radius:8px;
cursor:pointer;
text-decoration:none;
color:#fff;
font-weight:bold;
}

.btn-export{
background:#16a34a;
}

.message{
background:#14532d;
padding:15px;
border-radius:10px;
margin-bottom:15px;
}

.error{
background:#7f1d1d;
padding:15px;
border-radius:10px;
margin-bottom:15px;
}

table{
width:100%;
border-collapse:collapse;
}

th,
td{
padding:12px;
border-bottom:1px solid #1e293b;
text-align:left;
}

th{
background:#1e293b;
font-weight:bold;
}

@media(max-width:900px){

table{
display:block;
overflow-x:auto;
}

}

</style>

</head>

<body>

<div class="header">

<h1>Payroll Management</h1>

<p>Manage verified staff payroll information</p>

</div>

<div class="container">

<div class="top-links">

<a href="dashboard.php">Dashboard</a>
<a href="staff-list.php">Staff Directory</a>
<a href="logout.php">Logout</a>

</div>

<?php if (!empty($message)): ?>

<div class="message">
<?= htmlspecialchars($message) ?>
</div>

<?php endif; ?>

<?php if (!empty($error)): ?>

<div class="error">
<?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>

<div class="stats">

<div class="stat-card">
<h3>Verified Staff</h3>
<div class="stat-value"><?= $verifiedCount ?></div>
</div>

<div class="stat-card">
<h3>With IBAN</h3>
<div class="stat-value"><?= $withIbanCount ?></div>
</div>

<div class="stat-card">
<h3>With PPS</h3>
<div class="stat-value"><?= $withPpsCount ?></div>
</div>

</div>

<div class="card">

<h2>Payroll Records</h2>

<br>

<a href="?action=export" class="btn btn-export">

Download CSV Export

</a>

<br><br>

<table>

<thead>

<tr>

<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Phone</th>
<th>PPS Number</th>
<th>IBAN</th>
<th>Status</th>
<th>Verified At</th>

</tr>

</thead>

<tbody>

<?php foreach($payroll as $row): ?>

<tr>

<td><?= (int)$row['id'] ?></td>

<td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>

<td><?= htmlspecialchars((string)$row['email']) ?></td>

<td><?= htmlspecialchars((string)$row['phone']) ?></td>

<td><?= htmlspecialchars((string)($row['national_insurance'] ?? 'N/A')) ?></td>

<td><?= htmlspecialchars((string)($row['bank_iban'] ?? 'N/A')) ?></td>

<td><?= htmlspecialchars((string)($row['profile_status'] ?? 'Unknown')) ?></td>

<td><?= $row['verified_at'] ? htmlspecialchars((string)$row['verified_at']) : 'Not Verified' ?></td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</body>

</html>
