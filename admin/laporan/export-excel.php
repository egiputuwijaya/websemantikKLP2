<?php
/**
 * admin/laporan/export-excel.php — Ekspor data ke CSV
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
    $sqlData = "SELECT m.npm, m.nama_mahasiswa, m.jenis_kelamin, m.tempat_lahir, m.tanggal_lahir, 
                       m.tanggal_masuk, pr.nama_program_studi, f.nama_fakultas, m.status_mahasiswa 
                FROM mahasiswa m 
                JOIN program_studi pr ON m.id_program_studi = pr.id_program_studi 
                JOIN fakultas f ON pr.id_fakultas = f.id_fakultas 
                $whereSql 
                ORDER BY m.tanggal_masuk DESC, m.nama_mahasiswa ASC";
    $stmtData = $pdo->prepare($sqlData);
    $stmtData->execute($params);
    $mahasiswa = $stmtData->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mahasiswa = [];
}

// Set header CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Laporan_Mahasiswa_UMB_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');
// Output UTF-8 BOM agar rapi di Excel
fputs($output, "\xEF\xBB\xBF");

fputcsv($output, ['NPM', 'Nama Mahasiswa', 'L/P', 'Tempat Lahir', 'Tgl Lahir', 'Tgl Masuk', 'Program Studi', 'Fakultas', 'Status']);

foreach ($mahasiswa as $m) {
    fputcsv($output, [
        $m['npm'],
        $m['nama_mahasiswa'],
        $m['jenis_kelamin'],
        $m['tempat_lahir'],
        $m['tanggal_lahir'],
        $m['tanggal_masuk'],
        $m['nama_program_studi'],
        $m['nama_fakultas'],
        $m['status_mahasiswa']
    ]);
}
fclose($output);
exit;
