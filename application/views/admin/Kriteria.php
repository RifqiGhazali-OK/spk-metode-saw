<?php
$list = $list ?? [];

// Kalkulasi total bobot
$total_bobot = 0;
foreach ($list as $k) {
    $total_bobot += (float)($k['bobot'] ?? 0);
}

// Toleransi float point untuk pengecekan bobot == 1 (100%)
$is_bobot_valid = abs($total_bobot - 1) <= 0.0001;
$total_persen   = number_format($total_bobot * 100, 0);
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="page-heading">
    <div class="page-title">
        <div class="row mb-3">
            <div class="col-12">
                <h3>Kriteria & Bobot</h3>
                <p class="text-subtitle text-muted">Kelola data kriteria penilaian beserta persentase bobotnya.</p>
            </div>
        </div>
    </div>

    <section class="section">

        <?php if (!$is_bobot_valid): ?>
            <div class="alert alert-light-warning color-warning alert-dismissible show fade d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
                <div>
                    <strong>Perhatian:</strong> Total bobot kriteria saat ini adalah <b><?= $total_persen ?>%</b>.
                    Sistem membutuhkan total bobot persis <b>100%</b> agar perhitungan dapat berjalan akurat.
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center py-3">
                <h5 class="card-title mb-0">Daftar Kriteria</h5>
                <button type="button" class="btn btn-primary btn-sm px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalKriteria">
                    Tambah Kriteria
                </button>
            </div>
            <div class="card-body mt-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="text-secondary border-bottom">
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">Kode</th>
                                <th>Nama Kriteria</th>
                                <th width="15%">Tipe</th>
                                <th width="20%">Bobot (%)</th>
                                <th class="text-center" width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($list)): ?>
                                <?php $no = 1; // Inisialisasi nomor urut berurutan 
                                ?>
                                <?php foreach ($list as $row): ?>
                                    <?php $bobot_tampil = (float)$row['bobot'] * 100; ?>
                                    <tr>
                                        <td class="text-muted fw-bold"><?= $no++ ?></td>

                                        <td><span class="badge bg-primary"><?= htmlspecialchars($row['kode']) ?></span></td>
                                        <td class="fw-semibold"><?= htmlspecialchars($row['nama']) ?></td>
                                        <td>
                                            <?php if ($row['tipe'] === 'benefit'): ?>
                                                <span class="badge bg-light-success text-success px-2 py-1">Benefit</span>
                                            <?php else: ?>
                                                <span class="badge bg-light-danger text-danger px-2 py-1">Cost</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="me-3 fw-semibold"><?= number_format($bobot_tampil, 0) ?>%</span>
                                                <div class="progress progress-sm flex-grow-1" style="height: 6px;">
                                                    <div class="progress-bar <?= $is_bobot_valid ? 'bg-primary' : 'bg-warning' ?>"
                                                        style="width: <?= $bobot_tampil ?>%">
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <button type="button" class="btn btn-sm btn-warning text-dark text-nowrap fw-bold px-3"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalKriteriaEdit"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-kode="<?= htmlspecialchars($row['kode']) ?>"
                                                    data-nama="<?= htmlspecialchars($row['nama']) ?>"
                                                    data-tipe="<?= htmlspecialchars($row['tipe']) ?>"
                                                    data-bobot="<?= number_format($bobot_tampil, 0) ?>">
                                                    Edit
                                                </button>

                                                <button type="button" class="btn btn-sm btn-danger text-nowrap btn-hapus fw-bold px-3"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-nama="<?= htmlspecialchars($row['nama']) ?>">
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        Belum ada data kriteria. Silakan tambahkan kriteria baru.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<?php foreach (['modalKriteria' => 'Tambah', 'modalKriteriaEdit' => 'Edit'] as $id => $title): ?>
    <div class="modal fade" id="<?= $id ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom pb-3">
                    <h5 class="modal-title <?= $id == 'modalKriteria' ? 'text-primary' : 'text-warning text-dark' ?>">
                        <?= $title ?> Kriteria
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="<?= $id == 'modalKriteria' ? base_url('admin/kriteria_store') : '' ?>"
                    method="post" id="<?= $id == 'modalKriteriaEdit' ? 'formEditKriteria' : '' ?>">

                    <div class="modal-body py-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted fw-semibold">Kode</label>
                                <input type="text" class="form-control border border-secondary" name="kode" id="<?= $id == 'modalKriteriaEdit' ? 'edit_kode' : 'kode' ?>" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label text-muted fw-semibold">Nama Kriteria</label>
                                <input type="text" class="form-control border border-secondary" name="nama" id="<?= $id == 'modalKriteriaEdit' ? 'edit_nama' : 'nama' ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted fw-semibold">Tipe</label>
                                <select class="form-select border border-secondary" name="tipe" id="<?= $id == 'modalKriteriaEdit' ? 'edit_tipe' : 'tipe' ?>" required>
                                    <option value="benefit">Benefit (Makin besar makin baik)</option>
                                    <option value="cost">Cost (Makin kecil makin baik)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted fw-semibold">Nilai Bobot</label>
                                <div class="input-group">
                                    <input type="number" class="form-control border border-secondary" name="bobot" id="<?= $id == 'modalKriteriaEdit' ? 'edit_bobot' : 'bobot' ?>" min="0" max="100" required>
                                    <span class="input-group-text bg-light border border-secondary fw-bold">%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top pt-3">
                        <button type="button" class="btn btn-light-secondary px-4 fw-bold" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-<?= $id == 'modalKriteria' ? 'primary' : 'warning text-dark' ?> px-4 ms-2 fw-bold">
                            <?= $id == 'modalKriteria' ? 'Simpan' : 'Update Data' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        // =====================
        // SWEETALERT CONFIG
        // =====================
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1200,
            timerProgressBar: true,
            customClass: {
                popup: 'shadow-sm border-0'
            }
        });

        const ConfirmDialog = {
            customClass: {
                confirmButton: 'btn btn-danger px-4 ms-2 fw-bold',
                cancelButton: 'btn btn-light-secondary px-4 fw-bold'
            },
            buttonsStyling: false,
            reverseButtons: true
        };

        <?php if ($this->session->flashdata('success')): ?>
            Toast.fire({
                icon: 'success',
                title: '<?= addslashes($this->session->flashdata('success')) ?>'
            });
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            Toast.fire({
                icon: 'error',
                title: '<?= addslashes($this->session->flashdata('error')) ?>'
            });
        <?php endif; ?>

        // Hapus Kriteria (Tanpa Loading Animation)
        document.querySelectorAll('.btn-hapus').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const {
                    id,
                    nama
                } = this.dataset;

                Swal.fire({
                    ...ConfirmDialog,
                    title: 'Konfirmasi Hapus',
                    html: `Apakah Anda yakin ingin menghapus kriteria <b>${nama}</b>?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Langsung redirect ke fungsi controller, tanpa animasi memproses
                        window.location.href = `<?= base_url('admin/kriteria_delete/') ?>${id}`;
                    }
                });
            });
        });

        // Tampilkan Data di Modal Edit
        const modalEdit = document.getElementById('modalKriteriaEdit');
        if (modalEdit) {
            modalEdit.addEventListener('show.bs.modal', function(e) {
                const btn = e.relatedTarget;
                const form = modalEdit.querySelector('#formEditKriteria');

                form.action = `<?= base_url('admin/kriteria_update/') ?>${btn.dataset.id}`;
                form.querySelector('#edit_kode').value = btn.dataset.kode;
                form.querySelector('#edit_nama').value = btn.dataset.nama;
                form.querySelector('#edit_tipe').value = btn.dataset.tipe;
                form.querySelector('#edit_bobot').value = btn.dataset.bobot;
            });
        }
    });
</script>