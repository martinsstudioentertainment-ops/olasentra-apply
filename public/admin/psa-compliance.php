<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

try {
    $stmt = $pdo->query("
        SELECT 
            id,
            first_name,
            last_name,
            email,
            psa_licence,
            psa_expiry_date,
            profile_status,
            verified_by,
            verified_at,
            verification_notes
        FROM staff_master
        ORDER BY profile_status DESC, id ASC
    ");
    
    $staff = $stmt->fetchAll();
    $error = '';
    
} catch (Exception $e) {
    $error = 'Failed to load staff data: ' . $e->getMessage();
    $staff = [];
}

function getPsaStatus($expiryDate) {
    if (empty($expiryDate) || $expiryDate === '0000-00-00') {
        return 'No Date';
    }
    
    $expiry = new DateTime($expiryDate);
    $today = new DateTime();
    
    if ($expiry < $today) {
        return 'Expired';
    } elseif ($expiry->diff($today)->days <= 30) {
        return 'Expiring Soon';
    }
    
    return 'Valid';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>PSA Compliance Report</title>

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

.card{
background:#0f172a;
border:1px solid #1e293b;
border-radius:15px;
padding:20px;
margin-bottom:20px;
}

.filters {
display: flex;
gap: 15px;
margin-bottom: 20px;
flex-wrap: wrap;
}

.filters input,
.filters select {
padding: 10px 15px;
border: none;
border-radius: 8px;
background: #1e293b;
color: #fff;
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

.badge{
padding:6px 10px;
border-radius:6px;
font-size:12px;
display:inline-block;
}

.status-incomplete{
background:#92400e;
color:#fff;
}

.status-pending-review{
background:#ca8a04;
color:#fff;
}

.status-verified{
background:#15803d;
color:#fff;
}

.status-expired-psa{
background:#b91c1c;
color:#fff;
}

.psa-valid{
background:#166534;
color:#fff;
}

.psa-expiring{
background:#d97706;
color:#fff;
}

.psa-expired{
background:#991b1b;
color:#fff;
}

.psa-nodate{
background:#475569;
color:#fff;
}

.action-btn{
background:#2563eb;
padding:6px 12px;
border-radius:6px;
text-decoration:none;
color:#fff;
font-size:12px;
display:inline-block;
}

.action-btn:hover {
background:#1d4ed8;
}

.error{
background:#7f1d1d;
padding:15px;
border-radius:10px;
color:#fff;
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

<h1>PSA Compliance Report</h1>

<p>Monitor and verify PSA compliance status for all staff members</p>

</div>

<div class="container">

<div class="top-links">

<a href="dashboard.php">Dashboard</a>
<a href="staff-list.php">Staff Directory</a>
<a href="logout.php">Logout</a>

</div>

<?php if (!empty($error)): ?>

<div class="error">
<?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>

<div class="card">

<h2>All Staff Compliance Status</h2>

<br>

<div class="filters">

<input type="text" id="search" placeholder="Search name, email, or PSA...">

<select id="statusFilter">
<option value="">All Status</option>
<option value="Incomplete">Incomplete</option>
<option value="Pending Review">Pending Review</option>
<option value="Verified">Verified</option>
<option value="Expired PSA">Expired PSA</option>
</select>

</div>

<table>

<thead>

<tr>

<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>PSA Licence</th>
<th>Expiry Date</th>
<th>PSA Status</th>
<th>Profile Status</th>
<th>Verified By</th>
<th>Action</th>

</tr>

</thead>

<tbody id="tableBody">

<?php foreach($staff as $row): ?>

<?php
    $psaStatus = getPsaStatus($row['psa_expiry_date']);
    $psaClass = 'psa-nodate';
    
    if ($psaStatus === 'Expired') {
        $psaClass = 'psa-expired';
    } elseif ($psaStatus === 'Expiring Soon') {
        $psaClass = 'psa-expiring';
    } elseif ($psaStatus === 'Valid') {
        $psaClass = 'psa-valid';
    }
    
    $statusLower = strtolower(str_replace(' ', '-', (string)($row['profile_status'] ?? 'incomplete')));
?>

<tr class="staff-row" data-status="<?= htmlspecialchars((string)$row['profile_status']) ?>">

<td><?= (int)$row['id'] ?></td>

<td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>

<td><?= htmlspecialchars((string)$row['email']) ?></td>

<td><?= htmlspecialchars((string)$row['psa_licence']) ?></td>

<td><?= $row['psa_expiry_date'] && $row['psa_expiry_date'] !== '0000-00-00' ? htmlspecialchars((string)$row['psa_expiry_date']) : 'Not Set' ?></td>

<td>

<span class="badge <?= htmlspecialchars($psaClass) ?>">
<?= htmlspecialchars($psaStatus) ?>
</span>

</td>

<td>

<span class="badge status-<?= htmlspecialchars($statusLower) ?>">
<?= htmlspecialchars((string)($row['profile_status'] ?? 'Incomplete')) ?>
</span>

</td>

<td><?= $row['verified_by'] ? htmlspecialchars((string)$row['verified_by']) : 'Not Verified' ?></td>

<td>

<a href="show-blade.php?id=<?= (int)$row['id'] ?>" class="action-btn">
Review
</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<script>

document.getElementById('search').addEventListener('keyup', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);

function filterTable() {

    const searchTerm = document.getElementById('search').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('.staff-row');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const status = row.getAttribute('data-status');
        
        const matchesSearch = text.includes(searchTerm);
        const matchesStatus = !statusFilter || status === statusFilter;

        row.style.display = matchesSearch && matchesStatus ? '' : 'none';
    });
}

</script>

</div>

</body>

</html>
