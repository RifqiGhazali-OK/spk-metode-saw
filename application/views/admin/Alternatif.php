<?php
// Inisialisasi data dengan null coalescing operator
$periode_list     = $periode_list ?? [];
$periode_selected = $periode_selected ?? '';
$list             = $list ?? [];

// Mapping periode_id ke nama_periode
$periode_map = [];
foreach ($periode_list as $p) {
    $periode_map[$p['id']] = $p['nama'] ?? '';
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="page-heading">
    <div class="page-title">
        <div class="row mb-3">
            <div class="col-12">
                <h3>Data Karyawan</h3>
                <p class="text-subtitle text-muted">Kelola data karyawan yang akan dinilai berdasarkan periode.</p>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="row mb-3 align-items-end">

            <div class="col-md-4 col-lg-3">
                <label class="form-label text-muted fw-semibold mb-1" style="font-size: 0.85rem;">Filter Periode</label>
                <select id="filter_periode" class="form-select form-select-sm border-secondary" onchange="location.href='?periode_id='+this.value">
                    <option value="">Semua Periode</option>
                    <?php foreach ($periode_list as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($periode_selected == $p['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-8 col-lg-9 mt-3 mt-md-0">
                <div class="d-flex justify-content-md-end gap-2 flex-wrap">
                    <button type="button" class="btn btn-primary btn-sm px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalAlternatif">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Alternatif
                    </button>

                <button type="button"
                    class="btn btn-success btn-sm px-3 fw-bold"
                    data-bs-toggle="modal"
                    data-bs-target="#modalImport">
                <i class="bi bi-upload me-1"></i>Upload Data
                </button>

                    <button type="button" class="btn btn-outline-danger btn-sm px-3 fw-bold" id="btnHapusMassal">
                        <i class="bi bi-trash me-1"></i>Hapus Terpilih
                    </button>
                </div>
            </div>

        </div>
    </section>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0 mt-3">
            <form id="formHapusMassal" action="<?= base_url('admin/alternatif_delete_massal') ?>" method="post">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="text-secondary border-bottom">
                            <tr>
                                <th class="ps-4" width="40">
                                    <div class="form-check">
                                        <input class="form-check-input border border-secondary" type="checkbox" id="select_all">
                                    </div>
                                </th>
                                <th width="10%">Kode</th>
                                <th width="20%">Nama Karyawan</th>
                                <th width="25%">Jabatan</th>
                                <th width="20%">Periode</th>
                                <th class="text-center" width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($list)): ?>
                                <?php foreach ($list as $row): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="form-check">
                                                <input class="form-check-input checkbox-item border border-secondary" type="checkbox" name="ids[]" value="<?= $row['id'] ?>">
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light-primary text-primary px-2 py-1"><?= htmlspecialchars($row['kode']) ?></span></td>
                                        <td class="fw-semibold"><?= htmlspecialchars($row['nama']) ?></td>
                                        <td class="text-muted"><?= htmlspecialchars($row['jabatan'] ?: '-') ?></td>
                                        <td>
                                            <span class="text-muted fw-semibold">
                                                <?= htmlspecialchars($periode_map[$row['periode_id']] ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <button type="button" class="btn btn-sm btn-warning text-dark text-nowrap fw-bold px-3"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalAlternatifEdit"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-kode="<?= htmlspecialchars($row['kode']) ?>"
                                                    data-nama="<?= htmlspecialchars($row['nama']) ?>"
                                                    data-jabatan="<?= htmlspecialchars($row['jabatan']) ?>"
                                                    data-periode_id="<?= $row['periode_id'] ?>">
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
                                        Belum terdapat data alternatif untuk periode ini. Silakan tambahkan data alternatif terlebih dahulu.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
    </section>
</div>

<?php foreach (['modalAlternatif' => 'Tambah', 'modalAlternatifEdit' => 'Edit'] as $id => $title): ?>
    <div class="modal fade" id="<?= $id ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom pb-3">
                    <h5 class="modal-title <?= $id == 'modalAlternatif' ? 'text-primary' : 'text-warning text-dark' ?>">
                        <?= $title ?> Alternatif
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?= $id == 'modalAlternatif' ? base_url('admin/alternatif_store') : '' ?>"
                    method="post" id="<?= $id == 'modalAlternatifEdit' ? 'formEditAlternatif' : '' ?>">
                    <div class="modal-body py-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted fw-semibold mb-1" style="font-size: 0.85rem;">Kode</label>
                                <input type="text" class="form-control border border-secondary" name="kode" id="<?= $id == 'modalAlternatifEdit' ? 'edit_kode' : 'kode' ?>" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label text-muted fw-semibold mb-1" style="font-size: 0.85rem;">Nama Lengkap</label>
                                <input type="text" class="form-control border border-secondary" name="nama" id="<?= $id == 'modalAlternatifEdit' ? 'edit_nama' : 'nama' ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-muted fw-semibold mb-1" style="font-size: 0.85rem;">Jabatan</label>
                                <input type="text" class="form-control border border-secondary" name="jabatan" id="<?= $id == 'modalAlternatifEdit' ? 'edit_jabatan' : 'jabatan' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted fw-semibold mb-1" style="font-size: 0.85rem;">Periode Penilaian</label>
                                <select class="form-select border border-secondary" name="periode_id" id="<?= $id == 'modalAlternatifEdit' ? 'edit_periode_id' : 'periode_id' ?>" required>
                                    <option value="">Pilih Periode...</option>
                                    <?php foreach ($periode_list as $p): ?>
                                        <option value="<?= $p['id'] ?>" <?= ($periode_selected == $p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['nama']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top pt-3">
                        <button type="button" class="btn btn-light-secondary px-4 fw-bold" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-<?= $id == 'modalAlternatif' ? 'primary' : 'warning text-dark' ?> px-4 ms-2 fw-bold">
                            <?= $id == 'modalAlternatif' ? 'Simpan' : 'Simpan Perubahan' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- ================= Upload Data ================= -->
<div class="modal fade" id="modalImport" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow">

            <div class="modal-header">
                <h5 class="modal-title text-success fw-bold">
                    Upload Data Karyawan
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>
            </div>

            <form action="<?= base_url('admin/alternatif_upload') ?>"
                  method="post"
                  enctype="multipart/form-data">

                <div class="modal-body py-3">

                    <div class="mb-3">

                        <label class="form-label fw-semibold mb-1">
                            Filter Periode
                        </label>

                        <select class="form-select border-secondary"
                                name="periode_id"
                                required>

                            <option value="">Pilih Periode...</option>

                            <?php foreach ($periode_list as $p): ?>
                                <option value="<?= $p['id'] ?>"
                                    <?= ($periode_selected == $p['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nama']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div>

                        <label class="form-label fw-semibold mb-1">
                            File Excel
                        </label>

                        <input type="file"
                               class="form-control border-secondary"
                               name="file_excel"
                               accept=".xlsx,.xls"
                               required>

                        <small class="text-muted d-block mt-2">
                            Format file harus<strong> (.xlsx atau .xls)</strong>
                        </small>

                    </div>

                </div>

                <div class="modal-footer py-2">

                    <button type="button"
                            class="btn btn-light-secondary fw-bold px-4"
                            data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit"
                            class="btn btn-success fw-bold px-4">
                        Upload
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        // SWEETALERT CONFIG //
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
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

        // --- Flash Messages ---
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

        // --- Single Delete Action ---
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
                    html: `Apakah Anda yakin ingin menghapus <b>${nama}</b>?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((res) => {
                    if (res.isConfirmed) {
                        window.location.href = `<?= base_url('admin/alternatif_delete/') ?>${id}`;
                    }
                });
            });
        });

        // --- Mass Delete Action ---
        const btnMassal = document.getElementById('btnHapusMassal');
        if (btnMassal) {
            btnMassal.addEventListener('click', () => {
                const total = document.querySelectorAll('.checkbox-item:checked').length;
                if (!total) return Toast.fire({
                    icon: 'warning',
                    title: 'Pilih minimal satu data'
                });

                Swal.fire({
                    ...ConfirmDialog,
                    title: `Hapus ${total} Data?`,
                    text: "Data yang terpilih akan dihapus secara permanen.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Hapus Semua',
                    cancelButtonText: 'Batal'
                }).then((res) => {
                    if (res.isConfirmed) {
                        document.getElementById('formHapusMassal').submit();
                    }
                });
            });
        }

        // --- Selection Logic ---
        const selectAll = document.getElementById('select_all');
        if (selectAll) {
            selectAll.addEventListener('change', (e) => {
                document.querySelectorAll('.checkbox-item').forEach(cb => cb.checked = e.target.checked);
            });
        }

        // --- Validasi Nama Sebelum Submit ---
        function validasiNamaForm(form) {
            const inputNama = form.querySelector('input[name="nama"]');
            if (inputNama && !/^[A-Za-z\s]+$/.test(inputNama.value.trim())) {
                Toast.fire({
                    icon: 'error',
                    title: 'Nama tidak boleh mengandung angka atau simbol'
                });
                inputNama.focus();
                return false;
            }
            return true;
        }

        document.querySelectorAll('#modalAlternatif form, #modalAlternatifEdit form').forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!validasiNamaForm(form)) {
                    e.preventDefault(); // tidak simpan data submit kalau nama masih ada angka
                }
            });
        });

        // --- Modal Edit Logic ---
        const modalEdit = document.getElementById('modalAlternatifEdit');
        if (modalEdit) {
            modalEdit.addEventListener('show.bs.modal', (e) => {
                const btn = e.relatedTarget;
                const form = modalEdit.querySelector('#formEditAlternatif');

                form.action = `<?= base_url('admin/alternatif_update/') ?>${btn.dataset.id}`;
                form.querySelector('#edit_kode').value = btn.dataset.kode;
                form.querySelector('#edit_nama').value = btn.dataset.nama;
                form.querySelector('#edit_jabatan').value = btn.dataset.jabatan;
                form.querySelector('#edit_periode_id').value = btn.dataset.periode_id;
            });
        }
    });
</script>