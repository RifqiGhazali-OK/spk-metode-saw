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
        <h3>Input Penilaian Alternatif</h3>
        <p class="text-subtitle text-muted">Isi matriks nilai (Skala: <strong>0.1 - 100</strong>). Nilai otomatis tersimpan saat Anda berpindah kolom.</p>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0">Matriks Penilaian</h5>
                    <div class="d-flex align-items-center">
                        <label class="form-label me-2 mb-0 fw-semibold text-muted">Periode:</label>
                        <select id="periode_id" class="form-select form-select-sm shadow-none" style="width: 160px; cursor: pointer;">
                            <?php foreach ($periode_list as $p): ?>
                                <?php $p_id = isset($p['id']) ? $p['id'] : ''; ?>
                                <option value="<?= $p_id ?>" <?= ($p_id == $periode_id_selected) ? 'selected' : '' ?>>
                                    <?= isset($p['nama']) ? htmlspecialchars($p['nama']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="card-body pt-4">
                    <?php if (isset($this->session) && $this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show border-0">
                            <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($this->session) && $this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show border-0">
                            <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light text-center">
                                <tr>
                                    <th rowspan="2" class="align-middle text-start ps-3" style="min-width: 180px; width: 220px;">Alternatif</th>
                                    <th colspan="<?= count($kriteria) ?>" class="text-center py-2">Kriteria Penilaian (Min: 0.1)</th>
                                </tr>
                                <tr>
                                    <?php foreach ($kriteria as $k): ?>
                                        <th style="min-width: 120px;">
                                            <span class="fw-bold text-dark"><?= isset($k['kode']) ? htmlspecialchars($k['kode']) : '' ?></span><br>
                                            <span class="fw-normal text-muted" style="font-size: 0.85rem;"><?= isset($k['nama']) ? htmlspecialchars($k['nama']) : '' ?></span><br>
                                            <small class="fw-semibold text-secondary" style="font-size: 0.8rem;">Bobot: <?= number_format((isset($k['bobot']) ? $k['bobot'] : 0) * 100, 0) ?>%</small>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($alternatif)): ?>
                                    <?php foreach ($alternatif as $alt): ?>
                                        <?php $alt_id = isset($alt['id']) ? $alt['id'] : ''; ?>
                                        <tr>
                                            <td class="text-start ps-3">
                                                <div class="fw-bold text-dark"><?= isset($alt['kode']) ? htmlspecialchars($alt['kode']) : '' ?> - <?= isset($alt['nama']) ? htmlspecialchars($alt['nama']) : '' ?></div>
                                                <?php if (!empty($alt['jabatan'])): ?>
                                                    <div class="text-muted" style="font-size: 0.8rem;"><?= htmlspecialchars($alt['jabatan']) ?></div>
                                                <?php endif; ?>
                                            </td>

                                            <?php foreach ($kriteria as $k): ?>
                                                <?php
                                                $k_id = isset($k['id']) ? $k['id'] : '';
                                                $nilai = isset($nilai_map[$alt_id][$k_id]) ? $nilai_map[$alt_id][$k_id] : '';
                                                $nilai_formatted = (is_numeric($nilai)) ? number_format($nilai, 2, '.', '') : '';
                                                ?>
                                                <td class="text-center">
                                                    <input type="number" step="0.01" min="0.1" max="100"
                                                        class="form-control text-center mx-auto shadow-none nilai-input"
                                                        data-alternatif="<?= $alt_id ?>" data-kriteria="<?= $k_id ?>"
                                                        value="<?= $nilai_formatted ?>" placeholder="0.1"
                                                        style="width: 90px; border-radius: 6px;">
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="<?= count($kriteria) + 1 ?>" class="text-center text-muted py-5">
                                            Belum ada data alternatif untuk periode ini.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top py-3 text-end">
                    <button id="btnProsesSAW" class="btn btn-primary px-4">
                        <i class="bi bi-calculator me-1"></i> Proses hitung SAW
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Dropdown Ganti Periode
        $('#periode_id').on('change', function() {
            window.location.href = '<?= base_url("saw/penilaian") ?>?periode_id=' + $(this).val();
        });

        // Fitur Auto-save & Validasi Minimal 0.1
        $('.nilai-input').on('change', function() {
            var input = $(this);
            var val = input.val();

            // Validasi: Jika diisi, tidak boleh kurang dari 0.1 atau lebih dari 100
            if (val !== '') {
                var floatVal = parseFloat(val);
                if (floatVal < 0.1 || floatVal > 100) {
                    alert("⚠️ NILAI TIDAK VALID!\n\nSyarat pengisian:\n- Nilai minimal adalah 0.1 (Rumus SAW tidak mendukung angka 0).\n- Nilai maksimal adalah 100.\n\nSilakan isi kembali dengan benar.");
                    input.val('');
                    input.css('border-color', '#dc3545');
                    setTimeout(() => input.css('border-color', ''), 2000);
                    return;
                }
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
                    // Feedback Hijau tanda berhasil simpan
                    input.css('border-color', '#198754');
                    setTimeout(() => input.css('border-color', ''), 500);
                },
                error: function() {
                    alert('Gagal menyimpan nilai. Cek koneksi Anda.');
                }
            });
        });

        // Validasi tombol Proses SAW
        $('#btnProsesSAW').click(function() {
            var periode_id = $('#periode_id').val();
            var isLengkap = true;

            // Pengecekan apakah ada input yang kosong atau di bawah 0.1
            $('.nilai-input').each(function() {
                var v = $(this).val();
                if (v === '' || parseFloat(v) < 0.1) {
                    isLengkap = false;
                    $(this).css('border-color', '#dc3545');
                } else {
                    $(this).css('border-color', '');
                }
            });

            if (!isLengkap) {
                alert("⚠️ PERINGATAN!\n\nAda nilai yang KOSONG atau bernilai 0.\nHarap lengkapi semua data dengan nilai minimal 0.1 sebelum memproses perhitungan.");
                return false;
            }

            if (confirm('Lanjutkan proses perhitungan SAW?')) {
                window.location.href = '<?= base_url("saw/proses_saw") ?>?periode_id=' + periode_id;
            }
        });
    });
</script>