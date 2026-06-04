<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die('Invalid Staff ID');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $newStatus = trim($_POST['profile_status'] ?? '');

    $allowed = [
        'Incomplete',
        'Pending Review',
        'Verified',
        'Expired PSA'
    ];

    if (in_array($newStatus, $allowed, true)) {

        $update = $pdo->prepare("
            UPDATE staff_master
            SET profile_status = :status
            WHERE id = :id
        ");

        $update->execute([
            ':status' => $newStatus,
            ':id' => $id
        ]);
    }

    header("Location: view-staff.php?id=" . $id);
    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM staff_master
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ':id' => $id
]);

$staff = $stmt->fetch();

if (!$staff) {
    die('Staff record not found');
}

$status = $staff['profile_status'] ?? 'Incomplete';

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>View Staff</title>

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
max-width:1200px;
margin:auto;
}

.card{
background:#0f172a;
border:1px solid #1e293b;
border-radius:15px;
padding:20px;
margin-bottom:20px;
}

.row{
display:flex;
flex-wrap:wrap;
margin-bottom:12px;
}

.label{
width:250px;
font-weight:bold;
color:#94a3b8;
}

.value{
flex:1;
}

.btn{
display:inline-block;
padding:12px 18px;
border:none;
border-radius:8px;
cursor:pointer;
text-decoration:none;
color:#fff;
margin-right:10px;
margin-top:10px;
}

.verified{
background:#15803d;
}

.pending{
background:#ca8a04;
}

.expired{
background:#b91c1c;
}

.incomplete{
background:#92400e;
}

.back{
background:#2563eb;
}

select{
padding:10px;
border-radius:8px;
width:250px;
}

.top-links{
margin-bottom:20px;
}

.top-links a{
color:#60a5fa;
text-decoration:none;
margin-right:15px;
}

</style>

</head>

<body>

<div class="header">

<h1>Staff Profile</h1>

<p>
<?= htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) ?>
</p>

</div>

<div class="container">

<div class="top-links">

<a href="index.php">Dashboard</a>

<a href="staff-list.php">Staff Directory</a>

<a href="logout.php">Logout</a>

</div>

<div class="card">

<div class="row">
<div class="label">ID</div>
<div class="value"><?= (int)$staff['id'] ?></div>
</div>

<div class="row">
<div class="label">First Name</div>
<div class="value"><?= htmlspecialchars((string)$staff['first_name']) ?></div>
</div>

<div class="row">
<div class="label">Last Name</div>
<div class="value"><?= htmlspecialchars((string)$staff['last_name']) ?></div>
</div>

<div class="row">
<div class="label">Email</div>
<div class="value"><?= htmlspecialchars((string)$staff['email']) ?></div>
</div>

<div class="row">
<div class="label">Phone</div>
<div class="value"><?= htmlspecialchars((string)$staff['phone']) ?></div>
</div>

<div class="row">
<div class="label">PSA Licence</div>
<div class="value"><?= htmlspecialchars((string)$staff['psa_licence']) ?></div>
</div>

<div class="row">
<div class="label">PSA Expiry Date</div>
<div class="value"><?= htmlspecialchars((string)$staff['psa_expiry_date']) ?></div>
</div>

<div class="row">
<div class="label">Date of Birth</div>
<div class="value"><?= htmlspecialchars((string)$staff['date_of_birth']) ?></div>
</div>

<div class="row">
<div class="label">Gender</div>
<div class="value"><?= htmlspecialchars((string)$staff['gender']) ?></div>
</div>

<div class="row">
<div class="label">Address</div>
<div class="value"><?= nl2br(htmlspecialchars((string)$staff['address'])) ?></div>
</div>

<div class="row">
<div class="label">Postcode</div>
<div class="value"><?= htmlspecialchars((string)$staff['postcode']) ?></div>
</div>

<div class="row">
<div class="label">National Insurance / PPS</div>
<div class="value"><?= htmlspecialchars((string)$staff['national_insurance']) ?></div>
</div>

<div class="row">
<div class="label">IBAN</div>
<div class="value"><?= htmlspecialchars((string)$staff['bank_iban']) ?></div>
</div>

<div class="row">
<div class="label">Current Status</div>
<div class="value"><?= htmlspecialchars((string)$status) ?></div>
</div>

</div>

<div class="card">

<h3>Update Profile Status</h3>

<br>

<a
href="edit-staff.php?id=<?= (int)$staff['id'] ?>"
class="btn back">

Edit Staff

</a>

<br><br>

<form method="POST">

<select name="profile_status">

<option value="Incomplete" <?= $status === 'Incomplete' ? 'selected' : '' ?>>Incomplete</option>
<option value="Pending Review" <?= $status === 'Pending Review' ? 'selected' : '' ?>>Pending Review</option>
<option value="Verified" <?= $status === 'Verified' ? 'selected' : '' ?>>Verified</option>
<option value="Expired PSA" <?= $status === 'Expired PSA' ? 'selected' : '' ?>>Expired PSA</option>

</select>

<br><br>

<button
type="submit"
class="btn verified">

Save Status

</button>

</form>

</div>

</div>

</body>
</html>
