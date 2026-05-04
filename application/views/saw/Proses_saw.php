<div class="page-heading">
    <div class="page-title mb-4">
        <h3>Detail Perhitungan SAW</h3>
        <p class="text-subtitle text-muted">Normalisasi dan pembobotan nilai</p>
    </div>

    <?php foreach ($alternatif as $alt): ?>
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-transparent">
                <h5 class="mb-0">Alternatif: <?= htmlspecialchars($alt['kode']) ?> - <?= htmlspecialchars($alt['nama']) ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 30%">Kriteria</th>
                                <th style="width: 15%">Nilai Mentah</th>
                                <th style="width: 20%">Normalisasi</th>
                                <th style="width: 20%">Bobot</th>
                                <th style="width: 15%">Nilai Terbobot</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kriteria as $krit): ?>
                                <tr>
                                    <td><?= htmlspecialchars($krit['kode']) ?> - <?= htmlspecialchars($krit['nama']) ?></td>
                                    <td class="text-center"><?= number_format($matrix[$alt['id']][$krit['id']], 2) ?></td>
                                    <td class="text-center"><?= number_format($normalized[$alt['id']][$krit['id']], 4) ?></td>
                                    <td class="text-center"><?= number_format($krit['bobot'], 4) ?> (<?= $krit['bobot'] * 100 ?>%)</td>
                                    <td class="text-center fw-semibold"><?= number_format($weighted[$alt['id']][$krit['id']], 4) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="4" class="text-end">Total Nilai Akhir (Vi)</th>
                                <th class="text-center fs-5"><?= number_format($final[$alt['id']], 4) ?></th>
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
                    <i class="bi bi-save me-1"></i> Simpan Hasil & Lihat Ranking
                </button>
            </form>
            <a href="<?= base_url('saw/penilaian?periode_id=' . $periode_id) ?>" class="btn btn-secondary btn-lg">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Input
            </a>
        </div>
    </div>
</div>