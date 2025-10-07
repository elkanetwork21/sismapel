<?php
// helper untuk baca kode error upload
function debugUploadErrorMessage($errorCode) {
    $errors = [
        UPLOAD_ERR_OK         => '✅ Tidak ada error, file berhasil diupload.',
        UPLOAD_ERR_INI_SIZE   => '❌ Ukuran file melebihi upload_max_filesize di php.ini.',
        UPLOAD_ERR_FORM_SIZE  => '❌ Ukuran file melebihi MAX_FILE_SIZE di form HTML.',
        UPLOAD_ERR_PARTIAL    => '❌ File hanya terupload sebagian.',
        UPLOAD_ERR_NO_FILE    => '❌ Tidak ada file yang diupload.',
        UPLOAD_ERR_NO_TMP_DIR => '❌ Folder temporary hilang.',
        UPLOAD_ERR_CANT_WRITE => '❌ Gagal menulis file ke disk.',
        UPLOAD_ERR_EXTENSION  => '❌ Upload dihentikan oleh ekstensi PHP.',
    ];

    return $errors[$errorCode] ?? "❌ Error tidak diketahui (code: $errorCode)";
}

// kalau form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>DEBUG POST</h3><pre>";
    print_r($_POST);
    echo "</pre>";

    echo "<h3>DEBUG FILES</h3><pre>";
    print_r($_FILES);
    echo "</pre>";

    if (isset($_FILES['logo'])) {
        $errCode = $_FILES['logo']['error'];
        echo "<h3>HASIL CHECK</h3>";
        echo debugUploadErrorMessage($errCode);
    }

    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Upload Debug</title>
</head>
<body>
    <h2>Tester Upload Logo</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <label>Pilih File Logo:</label><br>
        <input type="file" name="logo"><br><br>
        <button type="submit">Upload Test</button>
    </form>
</body>
</html>
