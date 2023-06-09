<?php
session_start();

if (!isset($_SESSION['level'])) {
  echo '<meta http-equiv="refresh" content="0;url=../../logout.php"/>';
  exit;
}
include "../../database/database.php";

date_default_timezone_set("Asia/Kuala_Lumpur");

$database = new Database;
$db = $database->getConnection();

// var_dump($_SESSION['status_kedatangan_obat']);
// die();

$tgl_rekap_awal = date_format($_SESSION['tgl_rekap_awal'], "Y-m-d H:i:s");
$tgl_rekap_akhir = date_format($_SESSION['tgl_rekap_akhir'], "Y-m-d H:i:s");

// var_dump($tgl_rekap_awal);
// var_dump($tgl_rekap_akhir);
// die();

$selectSql = "SELECT * FROM pembelian p inner join obat o on p.id_obat = o.id_obat inner join suplier s on p.id_suplier = s.id_suplier WHERE (tgl_pembelian BETWEEN ? AND ?) ORDER BY tgl_pembelian ASC";
$stmt = $db->prepare($selectSql);
$stmt->bindParam(1, $tgl_rekap_awal);
$stmt->bindParam(2, $tgl_rekap_akhir);
$stmt->execute();
?>
<style>
  table#content {
    font-family: Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    /* table-layout: fixed; */
    width: 100%;
    margin-bottom: 30px;

  }

  table#content th {
    border: 1px solid grey;
    padding: 8px;
    text-align: center;
    width: fit-content;
    background-color: #5a5e5a;
    color: white;

  }

  table#content td {
    border: 1px solid grey;
    padding: 8px;
  }

  table#content tbody tr:nth-child(even) {
    background-color: #e4ede4;
  }

  table#content1 {
    /* width: 100%; */
    border-collapse: collapse;
    margin-bottom: 10px;
  }

  table#content1 tr td:nth-child(n+2) {
    padding-left: 10px;
  }

  table#content1 td {
    /* border: 1px solid black; */
    padding-bottom: 10px;
  }

  table#summary {
    width: 100%;
    border-collapse: collapse;
  }
</style>

<!-- header -->

<table style="width: 100%; margin-bottom: 10px;">
  <tr>
    <td align="center" style="font-weight: bold; padding-bottom: 20px; font-size: x-large;"><u>DATA REKAP PEMBELIAN</u></td>
  </tr>
</table>

<!-- content -->
<table id="content">
  <thead>
    <tr>
      <th>No.</th>
      <th>No Faktur</th>
      <th>Tanggal Faktur</th>
      <th>Nama Obat</th>
      <th>Qty</th>
      <th>Harga Pembelian</th>
      <th>Total</th>
      <th>Expired Obat</th>
      <th>Nama Suplier</th>
      <th>Tanggal Jatuh Tempo</th>
      <th>Jenis Pembelian</th>
    </tr>
  </thead>
  <tbody>
    <?php

    $no = 1;
    $total_pembelian = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $total_pembelian += $row['jumlah'] * $row['harga'];
    ?>
      <tr>
        <td><?= $no++ ?></td>
        <td style="text-transform: uppercase;"><?= $row['no_faktur'] ?></td>
        <td><?= tanggal_indo($row['tgl_pembelian']) ?></td>
        <td style="text-transform: uppercase;"><?= $row['nama_obat'] ?></td>
        <td><?= number_format($row['jumlah'], 0, ",", ".") ?></td>
        <td><?= "Rp. " . number_format($row['harga'], 0, ",", ".") ?></td>
        <td><?= "Rp. " . number_format($row['jumlah'] * $row['harga'], 0, ",", ".") ?></td>
        <td><?= tanggal_indo($row['ex_obat']) ?></td>
        <td><?= $row['nama_suplier'] ?></td>
        <td><?= tanggal_indo($row['tgl_jatuh_tempo']) ?></td>
        <td style="text-transform: uppercase;"><?= $row['jenis_pembelian'] ?></td>
      </tr>
    <?php } ?>
  </tbody>
  <tfoot>
    <tr style="background-color: cornsilk;">
      <td style="font-weight: bold;" colspan="6" align="center">TOTAL PEMBELIAN</td>
      <td style="font-weight: bold;" colspan="5" align="center"><?= "Rp. " . number_format($total_pembelian, 0, ",", ".") ?></td>
    </tr>
  </tfoot>
</table>
<!-- end content -->

<!-- summary -->

<table id="summary" style="page-break-inside: avoid;" autosize="1">
  <tr>
    <td width="70%"></td>
    <td align="center">Martapura, <?= tanggal_indo(date('Y-m-d')) ?></td>
  </tr>
  <tr>
    <td width=" 70%"></td>
    <td><br><br><br><br><br><br><br></td>
  </tr>
  <tr>
    <td width="70%"></td>
    <td align="center"><u><b><?= $_SESSION['nama'] ?></b></u></td>
  </tr>
</table>

<!-- end summary -->

<!-- footer -->
<!-- end footer -->

<?php
function tanggal_indo($date, $cetak_hari = false)
{
  $hari = array(
    1 =>    'Senin',
    'Selasa',
    'Rabu',
    'Kamis',
    'Jumat',
    'Sabtu',
    'Minggu'
  );

  $bulan = array(
    1 =>   'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember'
  );
  $split = explode(' ', $date);
  $split_tanggal = explode('-', $split[0]);
  if (count($split) == 1) {
    $tgl_indo = $split_tanggal[2] . ' ' . $bulan[(int)$split_tanggal[1]] . ' ' . $split_tanggal[0];
  } else {
    $split_waktu = explode(':', $split[1]);
    $tgl_indo = $split_tanggal[2] . ' ' . $bulan[(int)$split_tanggal[1]] . ' ' . $split_tanggal[0] . ' ' . $split_waktu[0] . ':' . $split_waktu[1] . ':' . $split_waktu[2];
  }

  if ($cetak_hari) {
    $num = date('N', strtotime($date));
    return $hari[$num] . ', ' . $tgl_indo;
  }
  return $tgl_indo;
}
?>