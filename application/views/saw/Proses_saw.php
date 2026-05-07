<?php
// Inisialisasi variabel untuk menghindari error undefined di Text Editor
$alternatif = isset($alternatif) ? $alternatif : array();
$kriteria   = isset($kriteria) ? $kriteria : array();
$matrix     = isset($matrix) ? $matrix : array();
$normalized = isset($normalized) ? $normalized : array();
$weighted   = isset($weighted) ? $weighted : array();
$final      = isset($final) ? $final : array();
$periode_id = isset($periode_id) ? $periode_id : '';
?>

<div class="page-heading">
    <div class="page-title mb-4">
        <h3>Detail Perhitungan SAW</h3>
        <p class="text-subtitle text-muted">Normalisasi dan pembobotan nilai</p>
    </div>

    <?php foreach ($alternatif as $alt): ?>
        <?php $alt_id = isset($alt['id']) ? $alt['id'] : ''; ?>
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-transparent">
                <h5 class="mb-0">Alternatif: <?= isset($alt['kode']) ? htmlspecialchars($alt['kode']) : '' ?> - <?= isset($alt['nama']) ? htmlspecialchars($alt['nama']) : '' ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start" style="width: 30%">Kriteria</th>
                                <th class="text-start" style="width: 15%">Nilai Mentah</th>
                                <th class="text-start" style="width: 20%">Normalisasi</th>
                                <th class="text-start" style="width: 20%">Bobot</th>
                                <th class="text-start" style="width: 15%">Nilai Terbobot</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kriteria as $krit): ?>
                                <?php
                                $krit_id    = $krit['id'] ?? '';
                                $val_matrix = $matrix[$alt_id][$krit_id] ?? 0;
                                $val_norm   = $normalized[$alt_id][$krit_id] ?? 0;
                                $val_weight = $weighted[$alt_id][$krit_id] ?? 0;
                                $bobot      = $krit['bobot'] ?? 0;
                                ?>
                                <tr>
                                    <td class="text-start"><?= isset($krit['kode']) ? htmlspecialchars($krit['kode']) : '' ?> - <?= isset($krit['nama']) ? htmlspecialchars($krit['nama']) : '' ?></td>
                                    <td class="text-start"><?= number_format($val_matrix, 2) ?></td>
                                    <td class="text-start"><?= number_format($val_norm, 4) ?></td>
                                    <td class="text-start"><?= number_format($bobot, 2) ?> (<?= number_format($bobot * 100, 0) ?>%)</td>
                                    <td class="text-start fw-semibold"><?= number_format($val_weight, 4) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="4" class="text-end pe-3">Total Nilai Akhir (Vi)</th>
                                <th class="text-start fs-5"><?= number_format(isset($final[$alt_id]) ? $final[$alt_id] : 0, 4) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="row mt-4">
        <div class="col-12 text-center">
            <form action="<?= base_url('saw/simpan_hasil') ?>" method="post" class="d-inline">
                <input type="hidden" name="final" value='<?= json_encode($final) ?>'>
                <input type="hidden" name="periode_id" value="<?= $periode_id ?>">
                <button type="submit" class="btn btn-primary btn-lg me-2">
                    <i class="bi bi-save me-1"></i> Lihat Ranking
                </button>
            </form>
            <a href="<?= base_url('saw/penilaian?periode_id=' . $periode_id) ?>" class="btn btn-secondary btn-lg">
                <i class="bi bi-arrow-left me-1"></i> Kembali Input
            </a>
        </div>
    </div>
</div