<?php
/**
 * admin/laporan/cetak-pdf.php — Layout Print Friendly
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

require_role([1, 2, 3, 4]);

$userActive = get_user_login();
$idRole     = (int)$userActive['id_role'];
$userFak    = (int)($userActive['id_fakultas'] ?? 0);
$userProdi  = (int)($userActive['id_program_studi'] ?? 0);

$fStatus = $_GET['status'] ?? '';
$fTahun  = $_GET['tahun'] ?? '';
$fProdi  = $_GET['id_prodi'] ?? '';

$where = ["1=1"];
$params = [];

if ($idRole === 3) {
    $where[] = "pr.id_fakultas = :user_fak";
    $params[':user_fak'] = $userFak;
} elseif ($idRole === 2) {
    $where[] = "m.id_program_studi = :user_prodi";
    $params[':user_prodi'] = $userProdi;
}
if ($fStatus !== '') {
    $where[] = "m.status_mahasiswa = :status";
    $params[':status'] = $fStatus;
}
if ($fTahun !== '') {
    $where[] = "YEAR(m.tanggal_masuk) = :tahun";
    $params[':tahun'] = $fTahun;
}
if ($fProdi !== '' && $idRole !== 2) {
    $where[] = "m.id_program_studi = :f_prodi";
    $params[':f_prodi'] = $fProdi;
}
$whereSql = "WHERE " . implode(" AND ", $where);

try {
    $sqlData = "SELECT m.*, pr.nama_program_studi, f.nama_fakultas 
                FROM mahasiswa m 
                JOIN program_studi pr ON m.id_program_studi = pr.id_program_studi 
                JOIN fakultas f ON pr.id_fakultas = f.id_fakultas 
                $whereSql 
                ORDER BY m.tanggal_masuk DESC, m.nama_mahasiswa ASC";
    $stmtData = $pdo->prepare($sqlData);
    $stmtData->execute($params);
    $mahasiswa = $stmtData->fetchAll();
} catch (PDOException $e) {
    $mahasiswa = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Rekapitulasi Data Mahasiswa</title>
    <style>
        body { font-family: "Times New Roman", Times, serif; font-size: 12px; color: #000; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { margin: 0 0 5px 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 0; font-size: 12px; }
        .meta { margin-bottom: 15px; font-size: 12px; }
        table { w-full: 100%; width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 6px; text-align: left; vertical-align: top; }
        th { background-color: #e5e5e5; font-weight: bold; text-align: center; }
        .text-center { text-align: center; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="no-print" style="margin-bottom: 20px;">
    <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; background: #2563eb; color: #fff; border: none; cursor: pointer; border-radius: 5px;">Cetak Dokumen</button>
</div>

<div class="header">
    <h1>SIM Mahasiswa</h1>
    <p>Universitas Muhammadiyah Bengkulu</p>
    <p>Rekapitulasi Data Mahasiswa</p>
</div>

<div class="meta">
    <table>
        <tr>
            <td style="border:none; width: 100px;"><strong>Tanggal Cetak</strong></td>
            <td style="border:none; width: 10px;">:</td>
            <td style="border:none;"><?= date('d F Y H:i:s') ?></td>
        </tr>
        <tr>
            <td style="border:none;"><strong>Dicetak Oleh</strong></td>
            <td style="border:none;">:</td>
            <td style="border:none;"><?= htmlspecialchars($userActive['nama_lengkap'] ?? 'System') ?> (Role: <?= htmlspecialchars($userActive['nama_role'] ?? '-') ?>)</td>
        </tr>
    </table>
</div>

<table>
    <thead>
        <tr>
            <th style="width: 30px;">No</th>
            <th>NPM</th>
            <th>Nama Lengkap</th>
            <th>L/P</th>
            <th>Program Studi</th>
            <th>Tahun Masuk</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($mahasiswa)): ?>
            <tr><td colspan="7" class="text-center">Tidak ada data ditemukan.</td></tr>
        <?php else: ?>
            <?php $no = 1; foreach ($mahasiswa as $m): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="text-center"><?= htmlspecialchars($m['npm']) ?></td>
                    <td><?= htmlspecialchars($m['nama_mahasiswa']) ?></td>
                    <td class="text-center"><?= $m['jenis_kelamin'] ?></td>
                    <td><?= htmlspecialchars($m['nama_program_studi']) ?></td>
                    <td class="text-center"><?= date('Y', strtotime($m['tanggal_masuk'])) ?></td>
                    <td class="text-center"><?= $m['status_mahasiswa'] ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<div style="margin-top: 50px; text-align: right; font-size: 12px; width: 300px; float: right;">
    <p style="margin-bottom: 60px;">Bengkulu, <?= date('d F Y') ?></p>
    <p><strong>Mengetahui,</strong></p>
</div>

</body>
</html>
