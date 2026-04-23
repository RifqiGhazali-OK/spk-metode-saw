<div class="page-heading">
    <div class="page-title mb-4">
        <div class="row">
            <div class="col-12">
                <h3>Data Alternatif</h3>
                <p class="text-subtitle text-muted">Kelola data alternatif (karyawan) yang akan dinilai per periode.</p>
            </div>
        </div>
    </div>

    <!-- Filter Periode -->
    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">Filter Periode</label>
            <select id="filter_periode" class="form-select" onchange="location.href='?periode_id='+this.value">
                <option value="">Semua Periode</option>
                <?php foreach ($periode_list as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ($periode_selected == $p['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                    <h4 class="card-title mb-0">Daftar Alternatif</h4>
                    <div>
                        <button type="button" class="btn btn-danger btn-sm me-2" id="btnHapusMassal">
                            <i class="bi bi-trash me-1"></i> Hapus Terpilih
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAlternatif">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Alternatif
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form id="formHapusMassal" action="<?= base_url('admin/alternatif_delete_massal') ?>" method="post">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">
                                            <input type="checkbox" id="select_all">
                                        </th>
                                        <th width="10%">Kode</th>
                                        <th width="25%">Nama Alternatif</th>
                                        <th width="25%">Jabatan</th>
                                        <th width="15%">Periode</th>
                                        <th width="20%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($list)): ?>
                                        <?php foreach ($list as $row): ?>
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" name="ids[]" value="<?= $row['id'] ?>" class="checkbox-item">
                                                </td>
                                                <td><?= htmlspecialchars($row['kode']) ?></td>
                                                <td class="fw-semibold"><?= htmlspecialchars($row['nama']) ?></td>
                                                <td><?= htmlspecialchars($row['jabatan'] ?? '-') ?></td>
                                                <td>
                                                    <?php
                                                    $periode_nama = '';
                                                    foreach ($periode_list as $p) {
                                                        if ($p['id'] == $row['periode_id']) {
                                                            $periode_nama = $p['nama'];
                                                            break;
                                                        }
                                                    }
                                                    echo htmlspecialchars($periode_nama);
                                                    ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalAlternatifEdit"
                                                            data-id="<?= $row['id'] ?>"
                                                            data-kode="<?= htmlspecialchars($row['kode']) ?>"
                                                            data-nama="<?= htmlspecialchars($row['nama']) ?>"
                                                            data-jabatan="<?= htmlspecialchars($row['jabatan'] ?? '') ?>"
                                                            data-periode_id="<?= $row['periode_id'] ?>">
                                                            <i class="bi bi-pencil-square"></i> Edit
                                                        </button>
                                                        <a href="<?= base_url('admin/alternatif_delete/' . $row['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus data ini?')">
                                                            <i class="bi bi-trash"></i> Hapus
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                Belum ada data alternatif. Silakan tambah.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Alternatif -->
<div class="modal fade" id="modalAlternatif" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahLabel">Tambah Alternatif</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('admin/alternatif_store') ?>" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="kode" class="form-label">Kode</label>
                        <input type="text" class="form-control" id="kode" name="kode" required>
                    </div>
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Alternatif</label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="jabatan" class="form-label">Jabatan</label>
                        <input type="text" class="form-control" id="jabatan" name="jabatan">
                    </div>
                    <div class="mb-3">
                        <label for="periode_id" class="form-label">Periode</label>
                        <select class="form-select" id="periode_id" name="periode_id" required>
                            <option value="">Pilih Periode</option>
                            <?php foreach ($periode_list as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= ($periode_selected == $p['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Alternatif -->
<div class="modal fade" id="modalAlternatifEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditLabel">Edit Alternatif</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="post" id="formEditAlternatif">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_kode" class="form-label">Kode</label>
                        <input type="text" class="form-control" id="edit_kode" name="kode" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_nama" class="form-label">Nama Alternatif</label>
                        <input type="text" class="form-control" id="edit_nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_jabatan" class="form-label">Jabatan</label>
                        <input type="text" class="form-control" id="edit_jabatan" name="jabatan">
                    </div>
                    <div class="mb-3">
                        <label for="edit_periode_id" class="form-label">Periode</label>
                        <select class="form-select" id="edit_periode_id" name="periode_id" required>
                            <option value="">Pilih Periode</option>
                            <?php foreach ($periode_list as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Modal edit: isi data dari tombol
    var modalEdit = document.getElementById('modalAlternatifEdit');
    modalEdit.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var kode = button.getAttribute('data-kode');
        var nama = button.getAttribute('data-nama');
        var jabatan = button.getAttribute('data-jabatan');
        var periode_id = button.getAttribute('data-periode_id');

        var form = modalEdit.querySelector('#formEditAlternatif');
        form.action = '<?= base_url('admin/alternatif_update') ?>/' + id;
        form.querySelector('#edit_kode').value = kode;
        form.querySelector('#edit_nama').value = nama;
        form.querySelector('#edit_jabatan').value = jabatan;
        form.querySelector('#edit_periode_id').value = periode_id;
    });

    // Select all checkbox
    document.getElementById('select_all').addEventListener('change', function(e) {
        var checkboxes = document.querySelectorAll('.checkbox-item');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = e.target.checked;
        }
    });

    // Hapus massal
    document.getElementById('btnHapusMassal').addEventListener('click', function() {
        var checked = document.querySelectorAll('.checkbox-item:checked');
        if (checked.length === 0) {
            alert('Pilih minimal satu data untuk dihapus.');
            return;
        }
        if (confirm('Yakin ingin menghapus ' + checked.length + ' data alternatif? Tindakan ini tidak dapat dibatalkan.')) {
            document.getElementById('formHapusMassal').submit();
        }
    });
</script>