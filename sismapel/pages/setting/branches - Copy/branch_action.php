<?php
session_start();

$username  = $_SESSION['username'];
$branch_id = $_SESSION['branch_id'];


include __DIR__ . "/../../../config.php";// Simpan


if(isset($_POST['action'])){
    $action = $_POST['action'];

    if($action == 'fetch'){
        $result = $conn->query("SELECT * FROM branches ORDER BY id DESC");
        $data = [];
        while($row = $result->fetch_assoc()){
            $data[] = $row;
        }
        echo json_encode($data);
    }

    if($action == 'add' || $action == 'update'){
        $id = $_POST['id'] ?? '';
        $nama_branch = $_POST['nama_branch'];
        $address = $_POST['address'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];

        $logo_name = '';
        if(isset($_FILES['logo']) && $_FILES['logo']['name'] != ''){
            $logo_name = time().'_'.$_FILES['logo']['name'];
            move_uploaded_file($_FILES['logo']['tmp_name'], 'uploads/'.$logo_name);
        }

        if($action == 'add'){
            $sql = "INSERT INTO branches (nama_branch, address, phone, email, logo) VALUES ('$nama_branch', '$address', '$phone', '$email', '$logo_name')";
            $conn->query($sql);
        } else {
            if($logo_name != ''){
                $sql = "UPDATE branches SET nama_branch='$nama_branch', address='$address', phone='$phone', email='$email', logo='$logo_name' WHERE id='$id'";
            } else {
                $sql = "UPDATE branches SET nama_branch='$nama_branch', address='$address', phone='$phone', email='$email' WHERE id='$id'";
            }
            $conn->query($sql);
        }
        echo json_encode(['status'=>'success']);
    }

    if($action == 'delete'){
        $id = $_POST['id'];
        $conn->query("DELETE FROM branches WHERE id='$id'");
        echo json_encode(['status'=>'success']);
    }

    if($action == 'edit'){
        $id = $_POST['id'];
        $res = $conn->query("SELECT * FROM branches WHERE id='$id'");
        echo json_encode($res->fetch_assoc());
    }
}
