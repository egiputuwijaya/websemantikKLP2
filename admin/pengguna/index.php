<?php
/**
 * admin/pengguna/index.php — Daftar Pengguna
 */
require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/helper.php';
require_once dirname(__DIR__, 2) . '/app/auth.php';

require_role([1]);

$filterRole = (int)($_GET['role'] ?? 0);

try {
    $stmtRoles = $pdo->query("SELECT id_role, nama_role FROM roles ORDER BY id_role ASC");
    $listRoles = $stmtRoles->fetchAll();

    $sql = "SELECT p.id_pengguna, p.username, p.nama_lengkap, p.email, p.status_aktif, p.last_login, r.nama_role, r.kode_role 
            FROM pengguna p 
            JOIN roles r ON p.id_role = r.id_role ";
    
    $params = [];
    if ($filterRole > 0) {
        $sql .= " WHERE p.id_role = :id_role ";
        $params[':id_role'] = $filterRole;
    }
    
    $sql .= " ORDER BY p.id_role ASC, p.nama_lengkap ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $penggunaData = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $listRoles = [];
    $penggunaData = [];
}

$pageTitle  = 'Manajemen User';
$activeMenu = 'pengguna';
?>
<?php require_once dirname(__DIR__, 2) . '/layouts/admin/header.php'; ?>

<section class="mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="font-heading font-bold text-2xl text-slate-900">Manajemen Pengguna</h2>
            <p class="text-sm text-slate-500 mt-1">Kelola akun dan hak akses pengguna sistem.</p>
        </div>
        <a href="<?= url('admin/pengguna/create.php') ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-academic-600 text-white text-sm font-semibold rounded-md hover:bg-academic-700 transition-colors focus:ring-4 focus:ring-academic-100">
            <i class="fa-solid fa-user-plus"></i> Tambah Pengguna
        </a>
    </div>

    <?= render_flash_alert() ?>

    <div class="bg-white rounded-md shadow-sm border border-slate-200 mb-6">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 rounded-t-md flex items-center justify-between flex-wrap gap-4">
            <form action="" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                <label for="role" class="text-sm font-semibold text-slate-700 hidden sm:block">Filter Role:</label>
                <select name="role" id="role" onchange="this.form.submit()" class="w-full sm:w-64 px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-academic-100 focus:border-academic-500">
                    <option value="0">-- Semua Role --</option>
                    <?php foreach ($listRoles as $r): ?>
                        <option value="<?= $r['id_role'] ?>" <?= $filterRole == $r['id_role'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r['nama_role'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($filterRole > 0): ?>
                    <a href="<?= url('admin/pengguna/index.php') ?>" class="px-3 py-2 text-sm text-slate-500 hover:text-slate-700" title="Reset Filter"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm table-hover">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-100">
                    <tr>
                        <th class="text-center font-semibold px-6 py-3 w-16">No</th>
                        <th class="text-left font-semibold px-6 py-3">User & Email</th>
                        <th class="text-left font-semibold px-6 py-3">Role / Akses</th>
                        <th class="text-center font-semibold px-6 py-3 w-32">Status</th>
                        <th class="text-right font-semibold px-6 py-3 w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($penggunaData)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                                    <i class="fa-solid fa-users text-xl text-slate-400"></i>
                                </div>
                                Belum ada data pengguna yang sesuai.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($penggunaData as $row): 
                            $status = $row['status_aktif'];
                            $badge  = $status === 'Aktif' ? 'bg-elegant-100 text-elegant-800' : 'bg-slate-200 text-slate-600';
                            
                            $roleColors = [
                                'ADMIN' => 'bg-purple-100 text-purple-700 border-purple-200',
                                'OPERATOR_PRODI' => 'bg-blue-100 text-blue-700 border-blue-200',
                                'DEKANAT' => 'bg-amber-100 text-amber-700 border-amber-200',
                                'REKTORAT' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                'MAHASISWA' => 'bg-slate-100 text-slate-700 border-slate-200',
                            ];
                            $rc = $roleColors[$row['kode_role']] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                        ?>
                            <tr>
                                <td class="px-6 py-4 text-center text-slate-500"><?= $no++ ?></td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900"><?= htmlspecialchars($row['nama_lengkap'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="text-xs text-slate-500 flex items-center gap-2 mt-1">
                                        <span class="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-slate-600">@<?= htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php if($row['email']): ?>
                                            <span>&bull; <?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-bold border <?= $rc ?>">
                                        <?= htmlspecialchars($row['nama_role'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg text-[11px] font-bold <?= $badge ?>">
                                        <?= $status ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="<?= url('admin/pengguna/edit.php?id=' . $row['id_pengguna']) ?>" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-100 transition-colors" title="Edit">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </a>
                                        <?php if ($row['kode_role'] !== 'ADMIN' || $row['id_pengguna'] != $_SESSION['user']['id_pengguna']): ?>
                                        <form action="<?= url('admin/pengguna/process.php') ?>" method="POST" class="inline-block">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id_pengguna" value="<?= $row['id_pengguna'] ?>">
                                            <button type="submit" data-confirm="Apakah Anda yakin ingin menghapus pengguna ini?" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition-colors" title="Hapus">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require_once dirname(__DIR__, 2) . '/layouts/admin/footer.php'; ?>
