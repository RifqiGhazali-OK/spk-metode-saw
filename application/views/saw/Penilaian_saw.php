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
?>

<?php if (!$show_hasil): ?>
    <div class="page-title mb-4">
        <h3>Penilaian & Proses SAW</h3>
        <p class="text-subtitle text-muted">
            Input nilai alternatif dan lakukan proses perhitungan SAW.
        </p>
    </div>
<?php endif; ?>

<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success">
        <?= $this->session->flashdata('success') ?>
    </div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger">
        <?= $this->session->flashdata('error') ?>
    </div>
<?php endif; ?>

<?php if (!$show_hasil): ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent">
            <h5 class="mb-0">Matriks Penilaian Alternatif</h5>
        </div>

        <div class="card-body">
            <div class="mb-3 d-flex align-items-center gap-2">
                <label class="fw-semibold mb-0">Periode:</label>
                <select id="periode_id" class="form-select form-select-sm" style="width:220px;">
                    <?php foreach ($periode_list as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $p['id'] == $periode_id_selected ? 'selected' : '' ?>>
                            <?= $p['nama'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center" style="table-layout:fixed; width:100%;">
                    <thead class="table-light">
                        <tr>
                            <th rowspan="2" style="width:25%; vertical-align:middle;">
                                Alternatif / Jabatan
                            </th>
                            <th colspan="<?= count($kriteria) ?>">
                                Kriteria Penilaian
                            </th>
                        </tr>
                        <tr>
                            <?php foreach ($kriteria as $k): ?>
                                <th style="vertical-align:middle;">
                                    <div class="fw-bold"><?= $k['kode'] ?></div>
                                    <small><?= $k['nama'] ?></small><br>
                                    <span class="badge bg-primary mt-1">
                                        <?= number_format($k['bobot'] * 100, 2, ',', '.') ?>%
                                    </span>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($alternatif)): ?>
                            <tr>
                                <td colspan="<?= count($kriteria) + 1 ?>" class="text-center py-5">
                                    <i class="bi bi-inboxes text-muted" style="font-size:4rem;"></i>
                                    <h5 class="mt-3 text-secondary">Data Alternatif Belum Tersedia</h5>
                                    <p class="text-muted mb-3">Belum ada karyawan yang terdaftar untuk periode ini.</p>
                                    <a href="<?= base_url('alternatif') ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-plus-circle me-1"></i> Tambah Data Alternatif
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($alternatif as $alt): ?>
                                <tr>
                                    <td class="text-start">
                                        <div class="fw-bold"><?= $alt['kode'] ?> - <?= $alt['nama'] ?></div>
                                        <small class="text-muted"><?= $alt['jabatan'] ?></small>
                                    </td>
                                    <?php foreach ($kriteria as $k): ?>
                                        <td class="text-center">
                                            <?php
                                            $nilai_db     = $nilai_map[$alt['id']][$k['id']] ?? '';
                                            $nilai_tampil = $nilai_db !== '' ? number_format((float)$nilai_db, 2, '.', '') : '';
                                            ?>
                                            <input
                                                type="number"
                                                min="0.1"
                                                max="100"
                                                step="0.01"
                                                class="form-control form-control-sm text-center nilai-input"
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
        </div>

        <div class="card-footer text-end bg-transparent">
            <form id="formProses" action="<?= base_url('saw/penilaian') ?>" method="post">
                <input type="hidden" name="periode_id" value="<?= $periode_id_selected ?>">
                <button
                    type="submit"
                    name="proses_hitung"
                    value="1"
                    id="btnProses"
                    class="btn btn-primary"
                    <?= empty($alternatif) ? 'disabled' : '' ?>>
                    <i class="bi bi-calculator"></i> Proses Hitung SAW
                </button>
            </form>
        </div>
    </div>

<?php endif; ?>

<?php if ($show_hasil && !empty($saw)): ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent">
            <h5 class="mb-0">Detail Perhitungan SAW</h5>
        </div>

        <div class="card-body">
            <div class="mb-3 d-flex align-items-center gap-2">
                <label class="fw-semibold mb-0">Periode:</label>
                <select id="periode_id_hasil" class="form-select form-select-sm" style="width:220px;">
                    <?php foreach ($periode_list as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $p['id'] == $periode_id_selected ? 'selected' : '' ?>>
                            <?= $p['nama'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php foreach ($saw['alternatif'] as $alt): ?>
                <?php $aid = $alt['id']; ?>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th colspan="6">
                                    Kode: <?= $alt['kode'] ?> | Nama: <?= $alt['nama'] ?> | Jabatan: <?= $alt['jabatan'] ?>
                                </th>
                            </tr>
                            <tr class="text-center">
                                <th>Kode</th>
                                <th>Nama Kriteria</th>
                                <th>Nilai</th>
                                <th>Normalisasi</th>
                                <th>Bobot</th>
                                <th>Terbobot</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($saw['kriteria'] as $k): ?>
                                <?php $kid = $k['id']; ?>
                                <tr>
                                    <td class="text-center"><?= $k['kode'] ?></td>
                                    <td><?= $k['nama'] ?></td>
                                    <td class="text-center">
                                        <?= number_format($saw['matrix'][$aid][$kid] ?? 0, 2, ',', '.') ?>
                                    </td>
                                    <td class="text-center">
                                        <?= number_format($saw['normalized'][$aid][$kid] ?? 0, 4, ',', '.') ?>
                                    </td>
                                    <td class="text-center">
                                        <?= number_format($k['bobot'], 4, ',', '.') ?>
                                    </td>
                                    <td class="text-center">
                                        <?= number_format($saw['weighted'][$aid][$kid] ?? 0, 4, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-secondary">
                                <th colspan="5" class="text-end">Nilai Akhir</th>
                                <th class="text-center">
                                    <?= number_format($saw['final'][$aid] ?? 0, 4, ',', '.') ?>
                                </th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card-footer text-center bg-transparent">
            <form action="<?= base_url('saw/simpan_hasil') ?>" method="post" class="d-inline">
                <input type="hidden" name="final" value='<?= json_encode($saw['final']) ?>'>
                <input type="hidden" name="periode_id" value="<?= $periode_id_selected ?>">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-bar-chart"></i> Lihat Ranking
                </button>
            </form>
            <a href="<?= base_url('saw/penilaian?periode_id=' . $periode_id_selected . '&force_edit=1') ?>" class="btn btn-secondary">
                <i class="bi bi-pencil"></i> Input Ulang / Edit Nilai
            </a>
        </div>
    </div>

<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Toast Container -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="toastBox" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="toastMsg"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
    function showToast(msg, type = 'danger') {
        let toast = $('#toastBox');
        toast.removeClass('text-bg-danger text-bg-warning text-bg-success');
        toast.addClass('text-bg-' + type);
        $('#toastMsg').text(msg);
        new bootstrap.Toast(toast[0], {
            delay: 3000
        }).show();
    }

    $(function() {

        // GANTI PERIODE
        $('#periode_id, #periode_id_hasil').change(function() {
            window.location.href = '<?= base_url("saw/penilaian") ?>?periode_id=' + $(this).val();
        });

        // SAVE NILAI AJAX
        $('.nilai-input').change(function() {
            let input = $(this);
            let nilai = parseFloat(input.val());

            if (input.val() === '' || isNaN(nilai) || nilai < 0.1 || nilai > 100) {
                showToast('Nilai harus diisi antara 0.1 sampai 100', 'danger');
                input.val('');
                input.focus();
                return;
            }

            $.post(
                '<?= base_url("saw/penilaian_save") ?>', {
                    alternatif_id: input.data('alternatif'),
                    kriteria_id: input.data('kriteria'),
                    nilai: nilai,
                    periode_id: $('#periode_id').val()
                },
                function(res) {
                    // Feedback border hijau setelah berhasil simpan
                    input.css('border-color', '#198754');
                    setTimeout(() => input.css('border-color', ''), 800);
                },
                'json' // parse response sebagai JSON
            );
        });

        // VALIDASI SEBELUM SUBMIT PROSES SAW
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
                showToast('Semua nilai wajib diisi dan harus bernilai 0.1 - 100', 'danger');
            }
        });
    });
</script>