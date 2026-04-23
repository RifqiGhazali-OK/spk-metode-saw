<div class="page-heading">
    <div class="page-title mb-4">
        <div class="row">
            <div class="col-12">
                <h3>Kriteria & Bobot</h3>
                <p class="text-subtitle text-muted">Kelola kriteria penilaian dan bobotnya (total bobot harus = 100)</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                    <h4 class="card-title mb-0">Daftar Kriteria</h4>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalKriteria">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Kriteria
                    </button>
                </div>
                <div class="card-body">
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php
                    $total_bobot = 0;
                    foreach ($list as $k) {
                        $total_bobot += (float)$k['bobot'] * 100; 
                    }
                    if (abs($total_bobot - 100) > 0.01) {
                        echo '<div class="alert alert-warning">⚠️ Total bobot saat ini: ' . number_format($total_bobot, 2) . '. Idealnya total bobot = 100.</div>';
                    }
                    ?>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">ID</th>
                                    <th width="10%">Kode</th>
                                    <th width="25%">Nama Kriteria</th>
                                    <th width="10%">Tipe</th>
                                    <th width="15%">Bobot</th>
                                    <th width="20%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($list)): ?>
                                    <?php foreach ($list as $row): ?>
                                        <?php $bobot_tampil = $row['bobot'] * 100; ?>
                                        <tr>
                                            <td><?= $row['id'] ?></td>
                                            <td><?= htmlspecialchars($row['kode']) ?></td>
                                            <td><?= htmlspecialchars($row['nama']) ?></td>
                                            <td>
                                                <?php if ($row['tipe'] == 'benefit'): ?>
                                                    <span class="badge bg-success">Benefit</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Cost</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= number_format($bobot_tampil, 2) ?> (<?= number_format($bobot_tampil, 0) ?>%)</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalKriteriaEdit"
                                                        data-id="<?= $row['id'] ?>"
                                                        data-kode="<?= htmlspecialchars($row['kode']) ?>"
                                                        data-nama="<?= htmlspecialchars($row['nama']) ?>"
                                                        data-tipe="<?= $row['tipe'] ?>"
                                                        data-bobot="<?= $bobot_tampil ?>">
                                                        <i class="bi bi-pencil-square"></i> Edit
                                                    </button>
                                                    <a href="<?= base_url('admin/kriteria_delete/' . $row['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus kriteria ini?')">
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
                                            Belum ada data kriteria. Silakan tambah.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Kriteria -->
<div class="modal fade" id="modalKriteria" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahLabel">Tambah Kriteria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('admin/kriteria_store') ?>" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="kode" class="form-label">Kode</label>
                        <input type="text" class="form-control" id="kode" name="kode" required>
                    </div>
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Kriteria</label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="tipe" class="form-label">Tipe</label>
                        <select class="form-select" id="tipe" name="tipe" required>
                            <option value="benefit">Benefit (semakin besar semakin baik)</option>
                            <option value="cost">Cost (semakin kecil semakin baik)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="bobot" class="form-label">Bobot (0 - 100)</label>
                        <input type="number" step="1" class="form-control" id="bobot" name="bobot" min="0" max="100" required>
                        <small class="text-muted">Contoh: 30 (berarti 30%)</small>
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

<!-- Modal Edit Kriteria -->
<div class="modal fade" id="modalKriteriaEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditLabel">Edit Kriteria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="post" id="formEditKriteria">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_kode" class="form-label">Kode</label>
                        <input type="text" class="form-control" id="edit_kode" name="kode" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_nama" class="form-label">Nama Kriteria</label>
                        <input type="text" class="form-control" id="edit_nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_tipe" class="form-label">Tipe</label>
                        <select class="form-select" id="edit_tipe" name="tipe" required>
                            <option value="benefit">Benefit</option>
                            <option value="cost">Cost</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_bobot" class="form-label">Bobot (0 - 100)</label>
                        <input type="number" step="1" class="form-control" id="edit_bobot" name="bobot" min="0" max="100" required>
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
    var modalEdit = document.getElementById('modalKriteriaEdit');
    modalEdit.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var kode = button.getAttribute('data-kode');
        var nama = button.getAttribute('data-nama');
        var tipe = button.getAttribute('data-tipe');
        var bobot = button.getAttribute('data-bobot')

        var form = modalEdit.querySelector('#formEditKriteria');
        form.action = '<?= base_url('admin/kriteria_update') ?>/' + id;
        form.querySelector('#edit_kode').value = kode;
        form.querySelector('#edit_nama').value = nama;
        form.querySelector('#edit_tipe').value = tipe;
        form.querySelector('#edit_bobot').value = bobot;
    });
</script>