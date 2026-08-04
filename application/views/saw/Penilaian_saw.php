<?php

/**
 * @var array $periode_list
 * @var int $periode_id_selected
 * @var array $kriteria
 * @var array $alternatif
 * @var array $nilai_map
 * @var bool $show_hasil
 * @var array $saw
 * @var bool $penilaian_lengkap
 * @var int $jumlah_penilaian
 * @var int $total_required
 */

$upload_errors  = $this->session->flashdata('upload_errors');
$flash_success  = $this->session->flashdata('success');

// Kelompokkan error per kategori supaya modal detail lebih mudah dibaca
$upload_errors_grouped = [];
$alasan_label = [
    'tidak_ditemukan' => 'Karyawan tidak ditemukan di periode ini',
    'ambigu'          => 'Nama ganda / ambigu',
    'nilai_invalid'   => 'Nilai tidak valid',
];
if (!empty($upload_errors)) {
    foreach ($upload_errors as $err) {
        $key = $err['alasan'] ?? 'lainnya';
        $upload_errors_grouped[$key][] = $err;
    }
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (!$show_hasil): ?>
    <div class="page-title mb-4">
        <h3>Penilaian & Proses SAW</h3>
        <p class="text-subtitle text-muted">
            Masukkan nilai karyawan untuk diproses menggunakan metode SAW.
        </p>
    </div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-light-danger d-flex align-items-center flex-wrap gap-2">
        <span class="me-auto"><?= $this->session->flashdata('error') ?></span>

        <?php if (!empty($upload_errors)): ?>
            <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modalUploadErrors">
                Lihat Detail (<?= count($upload_errors) ?>)
            </button>
        <?php endif; ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!$show_hasil): ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0">Matriks Penilaian Karyawan</h5>
        </div>

        <div class="card-body">
            <!-- Filter Periode & Aksi -->
            <div class="row mb-3 align-items-end">
                <div class="col-md-4 col-lg-3">
                    <label class="form-label text-muted fw-semibold mb-1" style="font-size: 0.85rem;">Filter Periode</label>
                    <select id="periode_id" class="form-select form-select-sm border-secondary">
                        <?php foreach ($periode_list as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $p['id'] == $periode_id_selected ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-8 col-lg-9 mt-3 mt-md-0">
                    <div class="d-flex justify-content-md-end gap-2 flex-wrap">
                        <button type="button"
                            class="btn btn-success btn-sm px-3 fw-bold"
                            data-bs-toggle="modal"
                            data-bs-target="#modalImportPenilaian">
                            <i class="bi bi-upload me-1"></i>Upload Data
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive" style="overflow-x: auto;">
                <?php
                $jumlah_kriteria = count($kriteria);
                $lebar_kolom_kriteria = $jumlah_kriteria > 0 ? floor(82 / $jumlah_kriteria) : 0;
                ?>
                <table class="table table-bordered table-hover align-middle"
                    style="min-width: 700px; width: 100%; border-collapse: collapse; font-size: 0.875rem; background-color: #fff; border-color: #dee2e6;">
                    <thead class="table-light">
                        <tr>
                            <th rowspan="2" style="width: 18%; vertical-align: middle; background-color: #f8f9fa;">Karyawan / Jabatan</th>
                            <th colspan="<?= $jumlah_kriteria ?>" class="text-center" style="background-color: #f8f9fa;">Kriteria & Bobot</th>
                        </tr>
                        <tr>
                            <?php foreach ($kriteria as $k): ?>
                                <th class="text-center" style="width: <?= $lebar_kolom_kriteria ?>%; word-wrap: break-word; white-space: normal; padding: 8px 6px; min-width: 100px;">
                                    <div class="fw-bold"><?= $k['kode'] ?></div>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;"><?= $k['nama'] ?></small>
                                    <span class="badge bg-primary mt-1" style="font-size: 0.7rem;"><?= number_format($k['bobot'] * 100, 2, ',', '.') ?>%</span>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($alternatif)): ?>
                            <tr>
                                <td colspan="<?= $jumlah_kriteria + 1 ?>" class="py-5 text-center">
                                    <div class="d-flex flex-column align-items-center justify-content-center py-4">
                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 80px; height: 80px;">
                                            <i class="bi bi-search text-muted" style="font-size: 2rem;"></i>
                                        </div>
                                        <h5 class="text-dark fw-bold mb-1">Data Tidak Ditemukan</h5>
                                        <p class="text-muted mb-0">Belum ada data alternatif untuk periode <strong><?= htmlspecialchars($periode_id_selected) ?></strong>.</p>
                                        <small class="text-muted mt-2">Silakan <a href="<?= base_url('admin/alternatif?periode_id=' . $periode_id_selected) ?>" class="text-decoration-none fw-bold">Masukkan Data</a> terlebih dahulu.</small>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($alternatif as $alt): ?>
                                <tr>
                                    <td class="text-start" style="padding: 10px 8px; background-color: #fff;">
                                        <div class="fw-bold" style="font-size: 0.9rem;"><?= $alt['kode'] ?> - <?= $alt['nama'] ?></div>
                                        <small class="text-muted" style="font-size: 0.75rem;"><?= $alt['jabatan'] ?></small>
                                    </td>
                                    <?php foreach ($kriteria as $k): ?>
                                        <?php
                                        $nilai_db     = $nilai_map[$alt['id']][$k['id']] ?? '';
                                        $nilai_tampil = $nilai_db !== '' ? number_format((float)$nilai_db, 2, '.', '') : '';
                                        ?>
                                        <td class="text-center" style="padding: 8px 4px;">
                                            <input type="number" min="0.1" max="100" step="0.01"
                                                class="form-control form-control-sm text-center nilai-input"
                                                style="width: 95px; margin: 0 auto; font-size: 0.8rem; padding: 5px 0;"
                                                data-alternatif="<?= $alt['id'] ?>"
                                                data-kriteria="<?= $k['id'] ?>"
                                                value="<?= $nilai_tampil ?>">
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tombol Proses Hitung SAW -->
            <div class="mt-3 text-end">
                <form id="formProses" action="<?= base_url('saw/penilaian') ?>" method="post">
                    <input type="hidden" name="periode_id" value="<?= $periode_id_selected ?>">
                    <button type="submit" name="proses_hitung" value="1" id="btnProses"
                        class="btn btn-primary" <?= empty($alternatif) ? 'disabled' : '' ?>>
                        Proses Hitung SAW
                    </button>
                </form>
            </div>
        </div>
    </div>

<?php endif; ?>

<?php if ($show_hasil && !empty($saw)): ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0">Detail Perhitungan SAW</h5>
        </div>

        <div class="card-body">
            <div class="row mb-3 align-items-end">
                <div class="col-md-4 col-lg-3">
                    <label class="form-label text-muted fw-semibold mb-1" style="font-size: 0.85rem;">Filter Periode</label>
                    <select id="periode_id_hasil" class="form-select form-select-sm border-secondary">
                        <?php foreach ($periode_list as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $p['id'] == $periode_id_selected ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <?php foreach ($saw['alternatif'] as $alt): ?>
                <?php $aid = $alt['id']; ?>
                <div class="table-responsive mb-4" style="overflow-x: auto;">
                    <table class="table table-bordered table-hover align-middle"
                        style="min-width: 650px; border-collapse: collapse; font-size: 0.875rem; background-color: #fff; border-color: #dee2e6;">
                        <thead class="table-light">
                            <tr>
                                <th colspan="6" class="bg-light py-2" style="font-size: 0.9rem;">Kode: <?= $alt['kode'] ?> | Nama: <?= $alt['nama'] ?> | Jabatan: <?= $alt['jabatan'] ?></th>
                            </tr>
                            <tr>
                                <th style="width: 15%;">Kode</th>
                                <th style="width: 25%;">Nama Kriteria</th>
                                <th style="width: 15%;">Nilai</th>
                                <th style="width: 15%;">Normalisasi</th>
                                <th style="width: 15%;">Bobot</th>
                                <th style="width: 15%;">Terbobot</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($saw['kriteria'] as $k): ?>
                                <?php $kid = $k['id']; ?>
                                <tr>
                                    <td class="text-center fw-semibold"><?= $k['kode'] ?></td>
                                    <td><?= $k['nama'] ?></td>
                                    <td class="text-end pe-3"><?= number_format($saw['matrix'][$aid][$kid] ?? 0, 2, ',', '.') ?></td>
                                    <td class="text-end pe-3"><?= number_format($saw['normalized'][$aid][$kid] ?? 0, 4, ',', '.') ?></td>
                                    <td class="text-end pe-3"><?= number_format($k['bobot'], 4, ',', '.') ?></td>
                                    <td class="text-end pe-3"><?= number_format($saw['weighted'][$aid][$kid] ?? 0, 4, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-secondary fw-bold">
                                <td colspan="5" class="text-end pe-3">Nilai Akhir</td>
                                <td class="text-end pe-3"><?= number_format($saw['final'][$aid] ?? 0, 4, ',', '.') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            <?php endforeach; ?>
            <div class="text-center" style="margin-top: -22px;">
                <a href="<?= base_url('saw/penilaian?periode_id=' . $periode_id_selected . '&force_edit=1') ?>" class="btn btn-secondary">
                    Edit Nilai
                </a>
                <form action="<?= base_url('saw/simpan_hasil') ?>" method="post" class="d-inline">
                    <input type="hidden" name="final" value='<?= json_encode($saw['final']) ?>'>
                    <input type="hidden" name="periode_id" value="<?= $periode_id_selected ?>">
                    <button type="submit" class="btn btn-success ms-2">
                        Lihat Ranking
                    </button>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- ================= Modal Upload Data Penilaian ================= -->
<div class="modal fade" id="modalImportPenilaian" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow">

            <div class="modal-header">
                <h5 class="modal-title text-success fw-bold">
                    Upload Data Penilaian
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('saw/penilaian_upload') ?>"
                  method="post"
                  enctype="multipart/form-data">

                <div class="modal-body py-3">

                    <div class="mb-3">
                        <label class="form-label fw-semibold mb-1">Periode Penilaian</label>
                        <select class="form-select border-secondary" name="periode_id" id="modal_periode_id" required>
                            <option value="">Pilih Periode...</option>
                            <?php foreach ($periode_list as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= ($periode_id_selected == $p['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="form-label fw-semibold mb-1">File Excel</label>
                        <input type="file"
                               class="form-control border-secondary"
                               name="file_excel"
                               accept=".xlsx,.xls,.csv"
                               required>
                        <small class="text-muted d-block mt-2">
                            Format file harus <strong>(.xlsx atau .xls)</strong>
                        </small>
                    </div>

                </div>

                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-light-secondary fw-bold px-4" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-success fw-bold px-4">
                        Upload
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<!-- ================= Modal Detail Error Upload ================= -->
<?php if (!empty($upload_errors_grouped)): ?>
<div class="modal fade" id="modalUploadErrors" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Baris yang Dilewati</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php foreach ($upload_errors_grouped as $key => $items): ?>
                    <h6 class="fw-bold mt-3 mb-2">
                        <?= htmlspecialchars($alasan_label[$key] ?? $key) ?>
                        <span class="text-muted fw-normal">(<?= count($items) ?>)</span>
                    </h6>
                    <ul class="list-group list-group-flush mb-2">
                        <?php foreach ($items as $err): ?>
                            <li class="list-group-item small">
                                Baris <?= (int)$err['baris'] ?>: <?= htmlspecialchars($err['pesan']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endforeach; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            customClass: { popup: 'shadow-sm border-0' }
        });

        function showToast(msg, type = 'error') {
            Toast.fire({ icon: type, title: msg });
        }

        <?php if (!empty($flash_success)): ?>
            Toast.fire({
                icon: 'success',
                title: <?= json_encode($flash_success) ?>
            });
        <?php endif; ?>

        $(function() {
            // Ganti periode
            $('#periode_id, #periode_id_hasil').change(function() {
                window.location.href = '<?= base_url("saw/penilaian") ?>?periode_id=' + $(this).val();
            });

            // Sinkronkan select periode di modal saat filter periode utama berubah
            $('#periode_id').on('change', function() {
                $('#modal_periode_id').val($(this).val());
            });

            // Simpan nilai AJAX
            $('.nilai-input').change(function() {
                let input = $(this);
                let nilai = parseFloat(input.val());

                if (input.val() === '' || isNaN(nilai) || nilai < 0.1 || nilai > 100) {
                    showToast('Nilai harus diisi antara 0.1 sampai 100', 'error');
                    input.val('');
                    input.focus();
                    return;
                }

                $.post('<?= base_url("saw/penilaian_save") ?>', {
                    alternatif_id: input.data('alternatif'),
                    kriteria_id: input.data('kriteria'),
                    nilai: nilai,
                    periode_id: $('#periode_id').val()
                }, function(res) {
                    input.css('border-color', '#198754');
                    setTimeout(() => input.css('border-color', ''), 800);
                }, 'json');
            });

            // Validasi sebelum proses SAW
            $('#formProses').submit(function(e) {
                let valid = true;
                $('.nilai-input').each(function() {
                    let nilai = parseFloat($(this).val());
                    if ($(this).val() === '' || isNaN(nilai) || nilai < 0.1 || nilai > 100) {
                        valid = false;
                        $(this).css('border-color', '#dc3545').focus();
                        return false;
                    }
                });
                if (!valid) {
                    e.preventDefault();
                    showToast('Semua nilai wajib diisi dan harus bernilai 0.1 - 100', 'error');
                }
            });
        });
    });
</script>