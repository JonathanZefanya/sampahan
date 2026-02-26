<?php
$roleBadge = ['admin'=>['dark','Pemerintah'],'dinas'=>['success','Dinas'],'masyarakat'=>['primary','Masyarakat']];
$totalUsers = count($users ?? []);
?>

<!-- Header -->
<div class="d-flex flex-wrap align-items-center gap-2 mb-4">
    <div class="me-auto">
        <h2 class="fw-bold mb-0"><i class="bi bi-people text-primary"></i> Manajemen User</h2>
        <p class="text-muted small mb-0"><?= $totalUsers ?> akun terdaftar</p>
    </div>
    <a href="<?= base_url('admin/users/new') ?>" class="btn btn-success">
        <i class="bi bi-person-plus-fill me-1"></i> Tambah User
    </a>
</div>

<!-- Search + Filter -->
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2 px-3">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control border-start-0 ps-0"
                           placeholder="Cari nama atau email" oninput="filterTable()">
                </div>
            </div>
            <div class="col-12 col-md-7">
                <div class="d-flex gap-1 flex-wrap">
                    <?php
                    $roles = ['' => 'Semua', 'admin' => 'Pemerintah', 'dinas' => 'Dinas', 'masyarakat' => 'Masyarakat'];
                    foreach ($roles as $val => $lbl):
                        $active = ($filterRole ?? '') === $val;
                    ?>
                    <a href="<?= base_url('admin/users' . ($val ? '?role=' . $val : '')) ?>"
                       class="btn btn-sm <?= $active ? 'btn-success' : 'btn-outline-secondary' ?>">
                        <?= $lbl ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="usersTable">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th class="ps-3" style="width:40px">#</th>
                        <th>Nama</th>
                        <th class="d-none d-md-table-cell">Email</th>
                        <th>Peran</th>
                        <th class="d-none d-sm-table-cell">Status</th>
                        <th class="d-none d-lg-table-cell">Bergabung</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-inbox display-6 d-block mb-2 opacity-25"></i>Tidak ada user.
                    </td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u):
                        $initial = mb_strtoupper(mb_substr($u['name'], 0, 1));
                        $colors  = ['#198754','#0d6efd','#6f42c1','#fd7e14','#0dcaf0','#d63384'];
                        $avatarBg = $colors[crc32($u['name']) % count($colors)];
                    ?>
                    <tr id="row-<?= $u['id'] ?>" data-name="<?= strtolower(esc($u['name'])) ?>" data-email="<?= strtolower(esc($u['email'])) ?>">
                        <td class="ps-3 text-muted small"><?= $u['id'] ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:36px;height:36px;background:<?= $avatarBg ?>;color:#fff;font-weight:700;font-size:.85rem;">
                                    <?= $initial ?>
                                </div>
                                <div>
                                    <div class="fw-semibold lh-sm"><?= esc($u['name']) ?></div>
                                    <div class="text-muted small d-md-none"><?= esc($u['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="d-none d-md-table-cell text-muted small"><?= esc($u['email']) ?></td>
                        <td>
                            <?php $rb = $roleBadge[$u['role']] ?? ['secondary','?']; ?>
                            <span class="badge bg-<?= $rb[0] ?>"><?= $rb[1] ?></span>
                        </td>
                        <td class="d-none d-sm-table-cell">
                            <span id="status-badge-<?= $u['id'] ?>"
                                  class="badge rounded-pill <?= $u['is_active'] ? 'bg-success' : 'bg-danger' ?>">
                                <?= $u['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </td>
                        <td class="d-none d-lg-table-cell text-muted small">
                            <?= date('d M Y', strtotime($u['created_at'])) ?>
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="<?= base_url('admin/users/' . $u['id'] . '/edit') ?>"
                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ((int)$u['id'] !== (int)($authUser['id'] ?? 0)): ?>
                                <button class="btn btn-sm btn-outline-<?= $u['is_active'] ? 'danger' : 'success' ?>"
                                        onclick="toggleUser(<?= $u['id'] ?>, this)"
                                        data-active="<?= (int)$u['is_active'] ?>"
                                        title="<?= $u['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                    <i class="bi bi-person-<?= $u['is_active'] ? 'x' : 'check' ?>"></i>
                                    <span class="d-none d-xl-inline"><?= $u['is_active'] ? ' Nonaktifkan' : ' Aktifkan' ?></span>
                                </button>
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
</div>

<?php ob_start(); ?>
<script>
function filterTable() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#usersTable tbody tr[data-name]').forEach(row => {
        const match = row.dataset.name.includes(q) || row.dataset.email.includes(q);
        row.style.display = match ? '' : 'none';
    });
}

function toggleUser(id, btn) {
    const isActive = parseInt(btn.dataset.active);
    Swal.fire({
        icon: 'question',
        title: isActive ? 'Nonaktifkan Akun?' : 'Aktifkan Akun?',
        text: isActive ? 'Pengguna tidak dapat login setelah dinonaktifkan.' : 'Pengguna akan bisa login kembali.',
        showCancelButton: true,
        confirmButtonColor: isActive ? '#dc3545' : '#198754',
        confirmButtonText: isActive ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan',
        cancelButtonText: 'Batal',
    }).then(result => {
        if (!result.isConfirmed) return;
        btn.disabled = true;

        fetch(`/admin/users/${id}/toggle`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ '<?= csrf_token() ?>': '<?= csrf_hash() ?>' })
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                const newActive = data.data.is_active;
                const badge = document.getElementById(`status-badge-${id}`);
                if (badge) {
                    badge.className = `badge rounded-pill ${newActive ? 'bg-success' : 'bg-danger'}`;
                    badge.textContent = newActive ? 'Aktif' : 'Nonaktif';
                }
                btn.className = `btn btn-sm btn-outline-${newActive ? 'danger' : 'success'}`;
                btn.innerHTML = `<i class="bi bi-person-${newActive ? 'x' : 'check'}"></i><span class="d-none d-xl-inline">${newActive ? ' Nonaktifkan' : ' Aktifkan'}</span>`;
                btn.title = newActive ? 'Nonaktifkan' : 'Aktifkan';
                btn.dataset.active = newActive ? 1 : 0;
                Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message ?? 'Status akun diperbarui.', timer: 1500, showConfirmButton: false });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: data.message ?? 'Gagal mengubah status.' });
            }
            btn.disabled = false;
        })
        .catch(() => {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan.' });
            btn.disabled = false;
        });
    });
}
</script>
<?php $extraScripts = ob_get_clean(); ?>