<?php
session_start();
include __DIR__ . "../../../../config.php";




// Debug helper
function debugUploadError($fileInputName) {
    $errors = [
        UPLOAD_ERR_OK         => 'Tidak ada error, file berhasil diupload.',
        UPLOAD_ERR_INI_SIZE   => 'Ukuran file melebihi upload_max_filesize di php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'Ukuran file melebihi MAX_FILE_SIZE di form HTML.',
        UPLOAD_ERR_PARTIAL    => 'File hanya terupload sebagian.',
        UPLOAD_ERR_NO_FILE    => 'Tidak ada file yang diupload.',
        UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary hilang.',
        UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
        UPLOAD_ERR_EXTENSION  => 'Upload dihentikan oleh ekstensi PHP.',
    ];

    $errorCode = $_FILES[$fileInputName]['error'] ?? null;
    return $errors[$errorCode] ?? "Error tidak diketahui (code: $errorCode)";
}

$username   = $_SESSION['username'];
$branch_id  = $_SESSION['branch_id'];
$role_login = $_SESSION['role'];

$nama      = $_POST['nama'];
$alamat    = $_POST['address'];
$telephone = $_POST['phone'];
$email     = $_POST['email'];
$logoFile  = "";

// cek apakah branch sudah ada
$sql = "SELECT * FROM branches WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$result  = $stmt->get_result();
$setting = $result->fetch_assoc();

$oldLogo = $setting['logo'] ?? "";

// folder upload
$targetDir = __DIR__ . "/images/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

// cek upload file
if (!empty($_FILES['logo']['name'])) {
    $fileName   = time() . "_" . basename($_FILES['logo']['name']);
    $targetFile = $targetDir . $fileName;

    if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetFile)) {
        $logoFile = $fileName;
    } else {
        die("Upload gagal! Error: " . debugUploadError('logo'));
    }
}

// jika tidak upload baru → pakai logo lama
if (empty($logoFile)) {
    $logoFile = $oldLogo;
}

if ($setting) {
    // update data branch
    $sql = "UPDATE branches SET nama_branch=?, address=?, phone=?, email=?, logo=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $nama, $alamat, $telephone, $email, $logoFile, $branch_id);

    if ($stmt->execute()) {
        header("Location: general.php?msg=update&token=" . urlencode($_SESSION['csrf_token']));        
        exit;
    } else {
        die("Update gagal: " . $stmt->error);
    }
} else {
    // insert data baru
    $sql = "INSERT INTO branches (id, nama_branch, address, phone, email, logo) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $branch_id, $nama, $alamat, $telephone, $email, $logoFile);

    if ($stmt->execute()) {
        header("Location: general.php?msg=success");
        exit;
    } else {
        die("Insert gagal: " . $stmt->error);
    }
}

$conn->close();
?>
