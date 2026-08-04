<?php
$periode = $periode ?? null;
$hasil   = $hasil ?? [];
?>

<div class="page-heading mb-2">
    <div class="page-title">
        <div class="row align-items-center">
            <div class="col-12 col-md-8">
                <h3 class="mb-1">Hasil Rekomendasi</h3>
                <p class="text-subtitle text-muted mb-0">Periode: <strong><?= htmlspecialchars($periode->nama ?? '-') ?></strong></p>
            </div>
            <div class="col-12 col-md-4 text-md-end">
                <a href="<?= base_url('user/periode') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar Periode
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>

    <!-- Tabel Ranking Hasil Rekomendasi -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent">
            <h4 class="card-title mb-0">Ranking Rekomendasi Perpanjangan Kontrak</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-start ps-4" style="width: 11%;">Rank</th>
                            <th class="text-start ps-4" style="width: 12%;">Kode</th>
                            <th class="text-start ps-4" style="width: 22%;">Nama Karyawan</th>
                            <th class="text-start ps-4" style="width: 20%;">Jabatan</th>
                            <th class="text-start ps-4" style="width: 20%;">Nilai Akhir</th>
                            <th class="text-start ps-4" style="width: 20%;">Rekomendasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($hasil)): ?>
                            <?php foreach ($hasil as $h): ?>
                                <tr>
                                    <td class="text-start ps-4">
                                        <?php if ($h['ranking'] == 1): ?>
                                            <div class="d-flex align-items-center justify-content-center bg-primary text-white rounded-circle shadow-sm" style="width: 32px; height: 32px; font-weight: bold; font-size: 0.8rem;">1</div>
                                        <?php elseif ($h['ranking'] <= 3): ?>
                                            <div class="d-flex align-items-center justify-content-center bg-light-primary text-primary rounded-circle border border-primary border-opacity-25" style="width: 32px; height: 32px; font-weight: bold; font-size: 0.8rem;"><?= $h['ranking'] ?></div>
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center text-muted fw-bold" style="width: 32px; height: 32px; font-size: 0.85rem;"><?= $h['ranking'] ?></div>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-start ps-4">
                                        <code class="fw-bold small text-primary"><?= htmlspecialchars($h['kode']) ?></code>
                                    </td>

                                    <td class="text-start fw-semibold text-dark text-truncate ps-4"><?= htmlspecialchars($h['nama_alternatif']) ?></td>

                                    <td class="text-start text-muted ps-4 small"><?= htmlspecialchars($h['jabatan'] ?? '-') ?></td>

                                    <td class="text-start ps-4">
                                        <div class="d-flex align-items-center gap-2" style="max-width: 180px;">
                                            <span class="fw-bold text-primary text-nowrap" style="width: 60px; flex-shrink: 0;">
                                                <?= number_format((float)$h['nilai_akhir'], 4) ?>
                                            </span>
                                            <div class="progress flex-grow-1" style="height: 5px; background-color: #eee;">
                                                <div class="progress-bar bg-primary" style="width: <?= (float)$h['nilai_akhir'] * 100 ?>%"></div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="text-start ps-4" style="white-space: nowrap;">
                                        <?php if ($h['status'] === 'Layak'): ?>
                                            <span class="badge bg-light-success text-success px-3 py-2 kotak" style="font-size: 0.8rem;">
                                                <i class="bi bi-patch-check-fill me-1"></i> LAYAK
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light-danger text-danger px-3 py-2 kotak" style="font-size: 0.8rem;">
                                                <i class="bi bi-exclamation-circle-fill me-1"></i> PERTIMBANGKAN
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center py-4">
                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 80px; height: 80px;">
                                            <i class="bi bi-search text-muted" style="font-size: 2rem;"></i>
                                        </div>
                                        <h5 class="text-dark fw-bold mb-1">Data Tidak Ditemukan</h5>
                                        <p class="text-muted mb-0">Belum ada hasil perhitungan untuk periode <strong><?= htmlspecialchars($periode->nama ?? '-') ?></strong>.</p>
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