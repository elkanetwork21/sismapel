<?php

include __DIR__ . "../../../config.php";

// Ambil parameter
$query = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page-1)*$limit;

// Filter global (cari di beberapa kolom)
$where = "";
if($query != ''){
    $where = "WHERE (d.name LIKE '%$query%' 
              OR d.code LIKE '%$query%'
              OR d.type LIKE '%$query%'
              OR d.full_address LIKE '%$query%'
              OR p.name LIKE '%$query%')";
}

// Query data
$sql = "SELECT 
            d.id, d.name, d.code, d.type, d.port, d.available_port,
            d.full_address, d.from_id, d.description,
            p.name AS from_name,

            CASE 
              WHEN d.type = 'POP' THEN 
                (SELECT GROUP_CONCAT(o.name SEPARATOR ', ') 
                 FROM distribusi o WHERE o.from_id = d.id AND o.type = 'ODC')

              WHEN d.type = 'ODC' THEN 
                (SELECT GROUP_CONCAT(o.name SEPARATOR ', ') 
                 FROM distribusi o WHERE o.from_id = d.id AND o.type = 'ODP')

              WHEN d.type = 'ODP' THEN 
                (SELECT GROUP_CONCAT(c.fullname SEPARATOR ', ') 
                 FROM customers c WHERE c.odp_id = d.id)

              ELSE NULL
            END AS children

        FROM distribusi d
        LEFT JOIN distribusi p ON d.from_id = p.id
        $where
        ORDER BY d.type, d.name ASC
        LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);

// Hitung total untuk pagination
$count_sql = "SELECT COUNT(*) as total FROM distribusi d 
              LEFT JOIN distribusi p ON d.from_id = p.id
              $where";
$count_res = $conn->query($count_sql);
$total_rows = $count_res->fetch_assoc()['total'];
$total_pages = ceil($total_rows/$limit);

// Tabel hasil
?>
<div class="accordion" id="accordionTable">
            <table class="table table-hover ">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Nama</th>
                  <th>Lokasi</th>
                  <th>Port</th>
                  <th>From</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                $no = 0;
                while($row = $result->fetch_assoc()): 
                  $no++;


    if ($row['available_port'] == 0) {
        $rowClass = "table-danger"; // merah
    } elseif ($row['available_port'] > 4) {
        $rowClass = "table-success"; // hijau
    } else {
        $rowClass = "table-warning"; // oranye
    }
 
                  ?>
                  <tr 
                  data-bs-toggle="collapse" 
                  data-bs-target="#collapse<?= $no ?>" 
                  aria-expanded="false" 
                  aria-controls="collapse<?= $no ?>" 
                  style="cursor:pointer;" 
                  class="<?= $rowClass ?>"?>
                    <td><?= $no?></td>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['full_address'] ?></td>
                    <td><?= $row['available_port'],'/', $row['port'] ?></td>
                    <td><?= $row['from_name'] ?? '-' ?></td>
                    <td></td>
                  </tr>
                  <tr>
                    <td colspan="6" class="p-0">
                      <div id="collapse<?= $no ?>" class="accordion-collapse collapse" data-bs-parent="#accordionTable">
                        <div class="p-3 bg-light">
                          <strong>Detail Data:</strong><br>
                          Kode: <?= $row['code'] ?><br>
                          Deskripsi: <?= $row['description'] ?><br>
                          Used: 
                          <span class="badge bg-danger"><?= $row['children'] ?? '-' ?></span><br>
                        </div>
                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>

 <!-- Pagination -->
<nav>
  <ul class="pagination">
    <?php for ($i=1; $i<=$total_pages; $i++): ?>
      <li class="page-item <?= ($i==$page)?'active':'' ?>">
        <a class="page-link" href="?page=<?= $i ?>&type=<?= $filter_type ?>&name=<?= $filter_name ?>&code=<?= $filter_code ?>&limit=<?= $limit ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>
          </div>