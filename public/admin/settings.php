<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $action = $_POST['action'] ?? '';
    
    try {
        
        if ($action === 'update_admin') {
            
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                throw new Exception('All password fields are required');
            }
            
            if ($newPassword !== $confirmPassword) {
                throw new Exception('New passwords do not match');
            }
            
            if (strlen($newPassword) < 8) {
                throw new Exception('Password must be at least 8 characters');
            }
            
            $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = :id");
            $stmt->execute([':id' => $_SESSION['admin_id']]);
            $admin = $stmt->fetch();
            
            if (!$admin || !password_verify($currentPassword, $admin['password'])) {
                throw new Exception('Current password is incorrect');
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $update = $pdo->prepare("UPDATE admins SET password = :password WHERE id = :id");
            $update->execute([
                ':password' => $hashedPassword,
                ':id' => $_SESSION['admin_id']
            ]);
            
            $message = 'Password updated successfully';
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$adminInfo = [];
try {
    $stmt = $pdo->prepare("SELECT id, full_name, username, email, role, created_at FROM admins WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['admin_id']]);
    $adminInfo = $stmt->fetch();
} catch (Exception $e) {
    $error = 'Failed to load admin information';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Settings</title>

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
max-width:800px;
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

.row{
margin-bottom:15px;
}

label{
display:block;
margin-bottom:6px;
font-weight:bold;
color:#94a3b8;
}

input[type="text"],
input[type="email"],
input[type="password"],
select{
width:100%;
padding:12px;
border:none;
border-radius:8px;
background:#1e293b;
color:#fff;
}

.info-row{
display:flex;
justify-content:space-between;
padding:12px;
border-bottom:1px solid #1e293b;
}

.info-label{
color:#94a3b8;
font-weight:bold;
}

.info-value{
color:#fff;
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

.btn-save{
background:#15803d;
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

</style>

</head>

<body>

<div class="header">

<h1>Settings</h1>

<p>Manage your admin account settings</p>

</div>

<div class="container">

<div class="top-links">

<a href="dashboard.php">Dashboard</a>
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

<div class="card">

<h2>Account Information</h2>

<br>

<?php if ($adminInfo): ?>

<div class="info-row">
<div class="info-label">Full Name</div>
<div class="info-value"><?= htmlspecialchars((string)$adminInfo['full_name']) ?></div>
</div>

<div class="info-row">
<div class="info-label">Username</div>
<div class="info-value"><?= htmlspecialchars((string)$adminInfo['username']) ?></div>
</div>

<div class="info-row">
<div class="info-label">Email</div>
<div class="info-value"><?= htmlspecialchars((string)$adminInfo['email']) ?></div>
</div>

<div class="info-row">
<div class="info-label">Role</div>
<div class="info-value"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string)$adminInfo['role']))) ?></div>
</div>

<div class="info-row">
<div class="info-label">Member Since</div>
<div class="info-value"><?= htmlspecialchars(date('F d, Y', strtotime((string)$adminInfo['created_at']))) ?></div>
</div>

<?php endif; ?>

</div>

<div class="card">

<h2>Change Password</h2>

<br>

<form method="POST">

<input type="hidden" name="action" value="update_admin">

<div class="row">
<label>Current Password</label>
<input type="password" name="current_password" required>
</div>

<div class="row">
<label>New Password</label>
<input type="password" name="new_password" required>
</div>

<div class="row">
<label>Confirm Password</label>
<input type="password" name="confirm_password" required>
</div>

<button type="submit" class="btn btn-save">

Update Password

</button>

</form>

</div>

<div class="card">

<h2>System Information</h2>

<br>

<div class="info-row">
<div class="info-label">System Version</div>
<div class="info-value">1.0</div>
</div>

<div class="info-row">
<div class="info-label">PHP Version</div>
<div class="info-value"><?= phpversion() ?></div>
</div>

<div class="info-row">
<div class="info-label">Current Time</div>
<div class="info-value"><?= date('Y-m-d H:i:s') ?></div>
</div>

</div>

</div>

</body>

</html>
