<?php
$periode = $periode ?? null;
$saw     = $saw ?? ['alternatif' => [], 'kriteria' => [], 'matrix' => [], 'normalized' => [], 'weighted' => [], 'final' => []];
?>

<div class="page-heading mb-2">
    <div class="page-title">
        <div class="row align-items-center">
            <div class="col-12 col-md-8">
                <h3 class="mb-1">Detail Perhitungan</h3>
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
    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent border-0">
            <h5 class="mb-0">Detail Perhitungan SAW — Seluruh Karyawan</h5>
            <p class="text-sm text-muted mb-0">Tabel normalisasi &amp; pembobotan masing masing karyawan</p>
        </div>

        <div class="card-body">
            <?php if (empty($saw['alternatif'])): ?>
                <div class="text-center text-muted py-5">
                    Belum ada data perhitungan untuk periode ini.
                </div>
            <?php else: ?>
                <?php foreach ($saw['alternatif'] as $alt): ?>
                    <?php $aid = $alt['id']; ?>
                    <div class="table-responsive mb-4" style="overflow-x: auto;">
                        <table class="table table-bordered table-hover align-middle"
                            style="min-width: 650px; border-collapse: collapse; font-size: 0.875rem; background-color: #fff; border-color: #dee2e6;">
                            <thead class="table-light">
                                <tr>
                                    <th colspan="6" class="bg-light py-2" style="font-size: 0.9rem;">
                                        Kode: <?= htmlspecialchars($alt['kode']) ?> | Nama: <?= htmlspecialchars($alt['nama']) ?> | Jabatan: <?= htmlspecialchars($alt['jabatan'] ?? '-') ?>
                                    </th>
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
                                        <td class="text-center fw-semibold"><?= htmlspecialchars($k['kode']) ?></td>
                                        <td><?= htmlspecialchars($k['nama']) ?></td>
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
            <?php endif; ?>
        </div>
    </div>
</div>