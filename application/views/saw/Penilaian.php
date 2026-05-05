<?php
// Inisialisasi Variabel
$periode_list = isset($periode_list) ? $periode_list : array();
$periode_id_selected = isset($periode_id_selected) ? $periode_id_selected : '';
$kriteria = isset($kriteria) ? $kriteria : array();
$alternatif = isset($alternatif) ? $alternatif : array();
$nilai_map = isset($nilai_map) ? $nilai_map : array();
?>

<div class="page-heading">
    <div class="page-title mb-4">
        <h3>Input Penilaian</h3>
        <p class="text-subtitle text-muted">Isi nilai setiap karyawan untuk setiap kriteria (skala 0-100). Setelah semua di isi, klik proses perhitungan.</p>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                    <h4 class="card-title mb-0">Matriks Penilaian</h4>
                    <div>
                        <label class="form-label me-2">Periode:</label>
                        <select id="periode_id" class="form-select d-inline-block w-auto">
                            <?php foreach ($periode_list as $p): ?>
                                <?php $p_id = isset($p['id']) ? $p['id'] : ''; ?>
                                <option value="<?= $p_id ?>" <?= ($p_id == $periode_id_selected) ? 'selected' : '' ?>>
                                    <?= isset($p['nama']) ? htmlspecialchars($p['nama']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (isset($this->session) && $this->session->flashdata('success')): ?>
                        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
                    <?php endif; ?>
                    <?php if (isset($this->session) && $this->session->flashdata('error')): ?>
                        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th rowspan="2" class="text-center" style="width: 180px; vertical-align: middle;">Alternatif</th>
                                    <th colspan="<?= count($kriteria) ?>" class="text-center">Kriteria</th>
                                </tr>
                                <tr>
                                    <?php foreach ($kriteria as $k): ?>
                                        <th class="text-center" style="min-width: 110px;">
                                            <strong><?= isset($k['kode']) ? htmlspecialchars($k['kode']) : '' ?></strong><br>
                                            <small class="text-muted"><?= isset($k['nama']) ? htmlspecialchars($k['nama']) : '' ?></small><br>
                                            <small class="fw-bold">Bobot: <?= number_format((isset($k['bobot']) ? $k['bobot'] : 0) * 100, 0) ?>%</small>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($alternatif)): ?>
                                    <?php foreach ($alternatif as $alt): ?>
                                        <?php $alt_id = isset($alt['id']) ? $alt['id'] : ''; ?>
                                        <tr>
                                            <td class="fw-semibold">
                                                <?= isset($alt['kode']) ? htmlspecialchars($alt['kode']) : '' ?> - <?= isset($alt['nama']) ? htmlspecialchars($alt['nama']) : '' ?>
                                                <?php if (!empty($alt['jabatan'])): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($alt['jabatan']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <?php foreach ($kriteria as $k): ?>
                                                <?php
                                                $k_id = isset($k['id']) ? $k['id'] : '';
                                                $nilai = isset($nilai_map[$alt_id][$k_id]) ? $nilai_map[$alt_id][$k_id] : '';
                                                $nilai_formatted = (is_numeric($nilai)) ? number_format($nilai, 2, '.', '') : '';
                                                ?>
                                                <td class="text-center">
                                                    <input type="number" step="0.01" class="form-control form-control-sm text-center nilai-input"
                                                        data-alternatif="<?= $alt_id ?>" data-kriteria="<?= $k_id ?>"
                                                        value="<?= $nilai_formatted ?>" placeholder="0-100" style="width: 90px; margin: 0 auto;">
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="<?= count($kriteria) + 1 ?>" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            Belum ada data alternatif. Silakan tambah data alternatif terlebih dahulu.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-transparent text-end">
                    <button id="btnProsesSAW" class="btn btn-primary">
                        <i class="bi bi-calculator-fill me-1"></i> Proses Perhitungan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Ganti periode
        $('#periode_id').on('change', function() {
            window.location.href = '<?= base_url("saw/penilaian") ?>?periode_id=' + $(this).val();
        });

        // Auto-save
        $('.nilai-input').on('change', function() {
            var input = $(this);
            var val = input.val();
            if (val !== '' && (val < 0 || val > 100)) {
                alert('Nilai harus antara 0-100');
                input.val('');
                return;
            }
            var periode_id = $('#periode_id').val();
            var sendVal = val === '' ? 0 : parseFloat(val);
            $.ajax({
                url: '<?= base_url("saw/penilaian_save") ?>',
                type: 'POST',
                data: {
                    alternatif_id: input.data('alternatif'),
                    kriteria_id: input.data('kriteria'),
                    nilai: sendVal,
                    periode_id: periode_id
                },
                success: function() {
                    input.css('border-color', '#28a745');
                    setTimeout(() => input.css('border-color', ''), 500);
                },
                error: function() {
                    alert('Gagal menyimpan');
                }
            });
        });

        // Tombol proses
        $('#btnProsesSAW').click(function() {
            var periode_id = $('#periode_id').val();
            if (confirm('Proses perhitungan SAW? Pastikan semua nilai sudah diisi.')) {
                window.location.href = '<?= base_url("saw/proses_saw") ?>?periode_id=' + periode_id;
            }
        });
    });
</script>