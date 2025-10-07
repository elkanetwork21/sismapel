 <?php
session_start();
include __DIR__ . "../../../config.php";
// include BASE_PATH . "includes/sidebar.php";

$invoice_id = $_GET['invoice'] ?? "";
$branch_id = $_SESSION['branch_id'];

 $sql = "SELECT * FROM bank_accounts WHERE status='aktif' AND branch_id=$branch_id";
              $stmt = $conn->prepare($sql);
              $stmt->execute();
              $res = $stmt->get_result();
              

              if ($res->num_rows > 0) {
                while ($acc = $res->fetch_assoc()) {
                  echo "<option value='{$acc['id']}'>
                  {$acc['bank_name']} - {$acc['account_number']} a.n {$acc['account_holder']}
                  </option>";
                }
              } else {
                echo "<option value=''>Tidak ada rekening aktif</option>";
              }

?>