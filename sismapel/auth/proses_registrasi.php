<?php
include __DIR__ . "../../config.php";

$alert = "";

if (isset($_POST['register'])) {
    $username   = trim($_POST['username']);
    $email      = trim($_POST['email']);
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $branch     = trim($_POST['branch']);
    $alamat     = trim($_POST['alamat']);
    $telepon    = trim($_POST['telepon']);

    // cek duplikat username / email
    $checkUser = mysqli_query($conn, "SELECT id FROM users WHERE username='$username' OR email='$email' LIMIT 1");
    if (mysqli_num_rows($checkUser) > 0) {
        $alert = "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Registrasi gagal',
            text: 'Username atau Email sudah terdaftar!'
        });
        </script>";
    } else {
        // cari role_id untuk branch_admin
        $roleRes = mysqli_query($conn, "SELECT id FROM roles WHERE role_name='Admin' LIMIT 1");
        $roleRow = mysqli_fetch_assoc($roleRes);
        $role_id = $roleRow['id'] ?? null;

        if (!$role_id) {
            $alert = "
            <script>
            Swal.fire({
                icon: 'error',
                title: 'Registrasi gagal',
                text: 'Role branch_admin belum ada di database!'
            });
            </script>";
        } else {
            // 1. Insert branch dulu (admin_id null sementara)
            $queryBranch = "INSERT INTO branches (nama_branch, address, phone, email) 
                            VALUES ('$branch','$alamat','$telepon','$email')";
            if (mysqli_query($conn, $queryBranch)) {
                $branch_id = mysqli_insert_id($conn);

                // 2. Insert user dengan branch_id + role_id
                $queryUser = "INSERT INTO users (username, email, password, branch_id, role_id,status) 
                              VALUES ('$username','$email','$password','$branch_id','$role_id',0)";
                if (mysqli_query($conn, $queryUser)) {
                    $user_id = mysqli_insert_id($conn);

                    

                    $alert = "
                    <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Registrasi berhasil',
                        text: 'Menunggu verifikasi Administrator',
                        showConfirmButton: true
                    }).then(() => {
                        window.location = '" . BASE_URL . "login';
                    });
                    </script>";
                } else {
                    $alert = "
                    <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Registrasi gagal',
                        text: 'Gagal menyimpan user: " . mysqli_error($conn) . "'
                    });
                    </script>";
                }
            } else {
                $alert = "
                <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Registrasi gagal',
                    text: 'Gagal menyimpan branch: " . mysqli_error($conn) . "'
                });
                </script>";
            }
        }
    }
}
?>
