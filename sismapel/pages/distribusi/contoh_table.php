<table class="table table-bordered">
  <thead>
    <tr>
      <th>Nama</th>
      <th>Kode</th>
      <th>Alamat</th>
      <th>From</th>
      <th>Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php 
    $no = 0;
    while($row = $result->fetch_assoc()): 
      $no++;
    ?>
    <tr data-bs-toggle="collapse" data-bs-target="#collapse<?= $no ?>" style="cursor:pointer;">
      <td><?= $row['name'] ?></td>
      <td><?= $row['code'] ?></td>
      <td><?= $row['full_address'] ?></td>
      <td><?= $row['from_name'] ?? '-' ?></td>
      <td><i class="bi bi-chevron-down"></i></td>
    </tr>
    <tr>
      <td colspan="5" class="p-0">
        <div id="collapse<?= $no ?>" class="collapse">
          <div class="p-3 bg-light">
            <strong>Detail Data:</strong><br>
            ID: <?= $row['id'] ?><br>
            Nama: <?= $row['name'] ?><br>
            Kode: <?= $row['code'] ?><br>
            Alamat Lengkap: <?= $row['full_address'] ?><br>
            Dari: <?= $row['from_name'] ?? '-' ?><br>
          </div>
        </div>
      </td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>
