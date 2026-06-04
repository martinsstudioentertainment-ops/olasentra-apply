<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die('Invalid Staff ID');
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
    die('Staff not found');
}

$success = '';
$error = '';

// Configuration
$uploadsDir = __DIR__ . '/../uploads/psa/';
$frontDir = $uploadsDir . 'front/';
$backDir = $uploadsDir . 'back/';
$maxFileSize = 5 * 1024 * 1024; // 5MB
$allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

// Ensure directories exist
if (!is_dir($frontDir)) {
    mkdir($frontDir, 0755, true);
}
if (!is_dir($backDir)) {
    mkdir($backDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $psaLicence = trim($_POST['psa_licence'] ?? '');

        if ($psaLicence !== '') {

            $checkPsa = $pdo->prepare("
                SELECT id
                FROM staff_master
                WHERE psa_licence = :psa
                AND id != :id
                LIMIT 1
            ");

            $checkPsa->execute([
                ':psa' => $psaLicence,
                ':id' => $id
            ]);

            if ($checkPsa->fetch()) {
                throw new Exception(
                    'PSA Licence already exists.'
                );
            }
        }

        $psaFrontImage = $staff['psa_front_image'];
        $psaBackImage = $staff['psa_back_image'];

        // Handle PSA Front Image Upload
        if (isset($_FILES['psa_front_image']) && $_FILES['psa_front_image']['error'] === UPLOAD_ERR_OK) {
            
            $file = $_FILES['psa_front_image'];
            
            // Validate file
            if ($file['size'] > $maxFileSize) {
                throw new Exception('PSA Front image exceeds 5MB limit');
            }
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedMimes, true)) {
                throw new Exception('PSA Front image must be JPEG, PNG, or WebP');
            }
            
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExtensions, true)) {
                throw new Exception('Invalid file extension for PSA Front image');
            }
            
            // Generate unique filename
            $filename = 'psa_front_' . $id . '_' . time() . '.' . $extension;
            $filepath = $frontDir . $filename;
            
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception('Failed to upload PSA Front image');
            }
            
            // Delete old image if exists
            if ($psaFrontImage && file_exists(__DIR__ . '/../' . $psaFrontImage)) {
                unlink(__DIR__ . '/../' . $psaFrontImage);
            }
            
            $psaFrontImage = 'uploads/psa/front/' . $filename;
        }

        // Handle PSA Back Image Upload
        if (isset($_FILES['psa_back_image']) && $_FILES['psa_back_image']['error'] === UPLOAD_ERR_OK) {
            
            $file = $_FILES['psa_back_image'];
            
            // Validate file
            if ($file['size'] > $maxFileSize) {
                throw new Exception('PSA Back image exceeds 5MB limit');
            }
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedMimes, true)) {
                throw new Exception('PSA Back image must be JPEG, PNG, or WebP');
            }
            
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExtensions, true)) {
                throw new Exception('Invalid file extension for PSA Back image');
            }
            
            // Generate unique filename
            $filename = 'psa_back_' . $id . '_' . time() . '.' . $extension;
            $filepath = $backDir . $filename;
            
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception('Failed to upload PSA Back image');
            }
            
            // Delete old image if exists
            if ($psaBackImage && file_exists(__DIR__ . '/../' . $psaBackImage)) {
                unlink(__DIR__ . '/../' . $psaBackImage);
            }
            
            $psaBackImage = 'uploads/psa/back/' . $filename;
        }

        $update = $pdo->prepare("

            UPDATE staff_master

            SET

                first_name = :first_name,
                last_name = :last_name,
                email = :email,
                phone = :phone,
                address = :address,
                postcode = :postcode,
                gender = :gender,
                psa_licence = :psa_licence,
                psa_expiry_date = :psa_expiry_date,
                psa_front_image = :psa_front_image,
                psa_back_image = :psa_back_image,
                profile_status = :profile_status,
                verification_notes = :verification_notes

            WHERE id = :id

        ");

        $update->execute([

            ':first_name' =>
                trim($_POST['first_name'] ?? ''),

            ':last_name' =>
                trim($_POST['last_name'] ?? ''),

            ':email' =>
                trim($_POST['email'] ?? ''),

            ':phone' =>
                trim($_POST['phone'] ?? ''),

            ':address' =>
                trim($_POST['address'] ?? ''),

            ':postcode' =>
                trim($_POST['postcode'] ?? ''),

            ':gender' =>
                trim($_POST['gender'] ?? ''),

            ':psa_licence' =>
                $psaLicence,

            ':psa_expiry_date' =>
                trim($_POST['psa_expiry_date'] ?? ''),

            ':psa_front_image' =>
                $psaFrontImage,

            ':psa_back_image' =>
                $psaBackImage,

            ':profile_status' =>
                trim($_POST['profile_status'] ?? 'Incomplete'),

            ':verification_notes' =>
                trim($_POST['verification_notes'] ?? ''),

            ':id' => $id

        ]);

        $success = 'Staff updated successfully';

        $stmt->execute([
            ':id' => $id
        ]);

        $staff = $stmt->fetch();

    } catch (Throwable $e) {

        $error = $e->getMessage();

    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Edit Staff</title>

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
max-width:1200px;
margin:auto;
padding:20px;
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
gap:15px;
margin-bottom:15px;
}

.col{
flex:1;
min-width:250px;
}

label{
display:block;
margin-bottom:6px;
font-weight:bold;
}

input,
textarea,
select{
width:100%;
padding:12px;
border:none;
border-radius:8px;
background:#1e293b;
color:#fff;
}

textarea{
min-height:120px;
}

.btn{
display:inline-block;
padding:12px 20px;
border:none;
border-radius:8px;
cursor:pointer;
text-decoration:none;
color:#fff;
}

.save{
background:#15803d;
}

.back{
background:#2563eb;
}

.protected{
background:#7f1d1d;
}

.success{
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

.upload-section{
border:2px dashed #1e293b;
padding:15px;
border-radius:8px;
margin-bottom:15px;
}

.upload-preview{
margin-top:10px;
}

.upload-preview img{
max-width:200px;
max-height:200px;
border-radius:8px;
}

.file-info{
font-size:12px;
color:#94a3b8;
margin-top:5px;
}

</style>

</head>

<body>

<div class="header">

<h1>Edit Staff</h1>

<p>
<?= htmlspecialchars($staff['first_name']) ?>
<?= htmlspecialchars($staff['last_name']) ?>
</p>

</div>

<div class="container">

<?php if (!empty($success)): ?>

<div class="success">
<?= htmlspecialchars($success) ?>
</div>
<?php endif; ?>

<?php if (!empty($error)): ?>

<div class="error">
<?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="card">

<a href="show-blade.php?id=<?= (int)$staff['id'] ?>" class="btn back">
Back To Review
</a>

</div>

<div class="card">

<h2>Editable Fields</h2>

<br>

<form method="POST" enctype="multipart/form-data">

<div class="row">

<div class="col">
<label>First Name</label>
<input type="text" name="first_name" value="<?= htmlspecialchars((string)$staff['first_name']) ?>">
</div>

<div class="col">
<label>Last Name</label>
<input type="text" name="last_name" value="<?= htmlspecialchars((string)$staff['last_name']) ?>">
</div>

</div>

<div class="row">

<div class="col">
<label>Email</label>
<input type="email" name="email" value="<?= htmlspecialchars((string)$staff['email']) ?>">
</div>

<div class="col">
<label>Phone</label>
<input type="text" name="phone" value="<?= htmlspecialchars((string)$staff['phone']) ?>">
</div>

</div>

<div class="row">

<div class="col">
<label>Address</label>
<textarea name="address"><?= htmlspecialchars((string)$staff['address']) ?></textarea>
</div>

</div>

<div class="row">

<div class="col">
<label>Postcode</label>
<input type="text" name="postcode" value="<?= htmlspecialchars((string)$staff['postcode']) ?>">
</div>

<div class="col">
<label>Gender</label>
<input type="text" name="gender" value="<?= htmlspecialchars((string)$staff['gender']) ?>">
</div>

</div>

<div class="row">

<div class="col">
<label>PSA Licence</label>
<input type="text" name="psa_licence" value="<?= htmlspecialchars((string)$staff['psa_licence']) ?>">
</div>

<div class="col">
<label>PSA Expiry Date</label>
<input type="date" name="psa_expiry_date" value="<?= htmlspecialchars((string)$staff['psa_expiry_date']) ?>">
</div>

</div>

<div class="row">

<div class="col">

<label>Profile Status</label>

<select name="profile_status">

<option value="Incomplete" <?= $staff['profile_status'] === 'Incomplete' ? 'selected' : '' ?>>
Incomplete
</option>

<option value="Pending Review" <?= $staff['profile_status'] === 'Pending Review' ? 'selected' : '' ?>>
Pending Review
</option>

<option value="Verified" <?= $staff['profile_status'] === 'Verified' ? 'selected' : '' ?>>
Verified
</option>

<option value="Expired PSA" <?= $staff['profile_status'] === 'Expired PSA' ? 'selected' : '' ?>>
Expired PSA
</option>

</select>

</div>

</div>

<div class="row">

<div class="col">

<label>Verification Notes</label>

<textarea name="verification_notes"><?= htmlspecialchars((string)$staff['verification_notes']) ?></textarea>

</div>

</div>

<button
type="submit"
class="btn save">

Save Changes

</button>

</form>

</div>

<div class="card">

<h2>PSA Document Images</h2>

<br>

<div class="row">

<div class="col">

<label>PSA Front Image</label>

<div class="upload-section">

<input type="file" name="psa_front_image" accept="image/jpeg,image/png,image/webp">

<div class="file-info">
Accepted: JPG, PNG, WebP (Max 5MB)
</div>

<?php if (!empty($staff['psa_front_image']) && file_exists(__DIR__ . '/../' . $staff['psa_front_image'])): ?>

<div class="upload-preview">

<p><strong>Current Image:</strong></p>

<img src="<?= htmlspecialchars('../' . $staff['psa_front_image']) ?>" alt="PSA Front">

</div>

<?php endif; ?>

</div>

</div>

<div class="col">

<label>PSA Back Image</label>

<div class="upload-section">

<input type="file" name="psa_back_image" accept="image/jpeg,image/png,image/webp">

<div class="file-info">
Accepted: JPG, PNG, WebP (Max 5MB)
</div>

<?php if (!empty($staff['psa_back_image']) && file_exists(__DIR__ . '/../' . $staff['psa_back_image'])): ?>

<div class="upload-preview">

<p><strong>Current Image:</strong></p>

<img src="<?= htmlspecialchars('../' . $staff['psa_back_image']) ?>" alt="PSA Back">

</div>

<?php endif; ?>

</div>

</div>

</div>

</div>

<div class="card">

<h2>Protected Fields</h2>

<br>

<div class="row">

<div class="col">
<label>PPS Number</label>
<input class="protected" type="text" value="<?= htmlspecialchars((string)$staff['national_insurance']) ?>" readonly>
</div>

<div class="col">
<label>Date Of Birth</label>
<input class="protected" type="text" value="<?= htmlspecialchars((string)$staff['date_of_birth']) ?>" readonly>
</div>

</div>

<div class="row">

<div class="col">
<label>IBAN</label>
<input class="protected" type="text" value="<?= htmlspecialchars((string)$staff['bank_iban']) ?>" readonly>
</div>

</div>

</div>

</div>

</body>

</html>
