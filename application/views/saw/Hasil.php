<?php
// Inisialisasi Variabel
$periode_list = isset($periode_list) ? $periode_list : array();
$periode_id_selected = isset($periode_id_selected) ? $periode_id_selected : '';
$hasil = isset($hasil) ? $hasil : array();
?>

<div class="page-heading">
    <div class="page-title mb-4">
        <h3>Hasil Perhitungan SAW</h3>
        <p class="text-subtitle text-muted">Ranking alternatif berdasarkan nilai akhir</p>
    </div>

    <!-- Periode & Tombol Export -->
    <div class="row mb-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Periode Penilaian</label>
            <select id="periode_id" class="form-select" onchange="location.href='?periode_id='+this.value">
                <?php foreach ($periode_list as $p): ?>
                    <option value="<?= isset($p['id']) ? $p['id'] : '' ?>" <?= ((isset($p['id']) ? $p['id'] : '') == $periode_id_selected) ? 'selected' : '' ?>>
                        <?= isset($p['nama']) ? htmlspecialchars($p['nama']) : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-8 text-end">
            <a href="<?= base_url('saw/Export_excel?periode_id=' . $periode_id_selected) ?>" class="btn btn-success">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Tabel Ranking -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent">
                    <h4 class="card-title mb-0">Ranking Alternatif</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-start ps-4" style="width: 11%;">Rank</th>
                                    <th class="text-start ps-4" style="width: 12%;">Kode</th>
                                    <th class="text-start ps-4" style="width: 22%;">Nama Alternatif</th>
                                    <th class="text-start ps-4"style="width: 20%;">Jabatan</th>
                                    <th class="text-start ps-4" style="width: 20%;">Nilai Akhir</th>
                                    <th class="text-start ps-4" style="width: 20%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($hasil)): ?>
                                    <?php foreach ($hasil as $row): ?>
                                        <tr>
                                            <td class="text-start ps-4">
                                                <?php if ($row['ranking'] == 1): ?>
                                                    <div class="d-flex align-items-center justify-content-center bg-primary text-white rounded-circle shadow-sm" style="width: 32px; height: 32px; font-weight: bold; font-size: 0.8rem;">1</div>
                                                <?php elseif ($row['ranking'] <= 3): ?>
                                                    <div class="d-flex align-items-center justify-content-center bg-light-primary text-primary rounded-circle border border-primary border-opacity-25" style="width: 32px; height: 32px; font-weight: bold; font-size: 0.8rem;"><?= $row['ranking'] ?></div>
                                                <?php else: ?>
                                                    <div class="d-flex align-items-center justify-content-center text-muted fw-bold" style="width: 32px; height: 32px; font-size: 0.85rem;"><?= $row['ranking'] ?></div>
                                                <?php endif; ?>
                                            </td>

                                            <td class="text-start ps-4">
                                                <code class="fw-bold small text-primary"><?= htmlspecialchars($row['kode']) ?>
                                                </code>
                                            </td>

                                            <td class="text-start fw-semibold text-dark text-truncate ps-4"><?= htmlspecialchars($row['nama_alternatif']) ?></td>

                                            <td class="text-start text-muted ps-4 small"><?= htmlspecialchars($row['jabatan'] ?? '-') ?></td>

                                            <td class="text-start ps-4">
                                                <div class="d-flex align-items-center gap-2" style="max-width: 160px;">
                                                    <span class="fw-bold text-primary" style="width: 45px; flex-shrink: 0;">
                                                        <?= number_format($row['nilai_akhir'], 3) ?>
                                                    </span>
                                                    <div class="progress flex-grow-1" style="height: 5px; background-color: #eee;">
                                                        <div class="progress-bar bg-primary" style="width: <?= $row['nilai_akhir'] * 100 ?>%"></div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="text-start ps-4" style="white-space: nowrap;">
                                                <?php if ($row['status'] == 'Layak'): ?>
                                                    <span class="badge bg-light-success text-success px-3 py-2 kotak" style="font-size: 0.8rem;">
                                                        <i class="bi bi-patch-check-fill me-1"></i> LAYAK
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-light-danger text-danger px-3 py-2 kotak" style="font-size: 0.8rem;">
                                                        <i class="bi bi-exclamation-circle-fill me-1"></i> PERTIMBANGKAN
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="py-5">
                                                <div class="d-flex flex-column align-items-center justify-content-center py-4">
                                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 80px; height: 80px;">
                                                        <i class="bi bi-search text-muted" style="font-size: 2rem;"></i>
                                                    </div>
                                                    <h5 class="text-dark fw-bold mb-1">Data Tidak Ditemukan</h5>
                                                    <p class="text-muted mb-0">Belum ada hasil perhitungan untuk periode <strong><?= htmlspecialchars($periode_id_selected) ?></strong>.</p>
                                                    <small class="text-muted mt-2">Silakan <a href="<?= base_url('saw/penilaian') ?>" class="text-decoration-none fw-bold">Input Penilaian</a> terlebih dahulu.</small>
                                                </div>
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