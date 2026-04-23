<div class="page-heading">
    <div class="page-title mb-4">
        <h3>Kriteria & Bobot</h3>
        <p class="text-subtitle text-muted">Bobot kriteria yang telah ditentukan oleh admin</p>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent py-3">
                    <h4 class="card-title mb-0">Daftar Kriteria Penilaian</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="10%">Kode</th>
                                    <th width="40%">Nama Kriteria</th>
                                    <th width="20%">Tipe</th>
                                    <th width="30%">Bobot</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($list as $row): ?>
                                    <?php
                                    $bobot_formatted = number_format($row['bobot'],2);
                                    $bobot_persen = round($row['bobot'] * 100, 2);
                                    ?>
                                    <tr>
                                        <td><span class="fw-semibold"><?= htmlspecialchars($row['kode']) ?></span></td>
                                        <td><?= htmlspecialchars($row['nama']) ?></td>
                                        <td>
                                            <?php if ($row['tipe'] == 'benefit'): ?>
                                                <span class="badge bg-success">Benefit</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Cost</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-semibold"><?= $bobot_formatted ?></span>
                                                <span class="text-muted">(<?= $bobot_persen ?>%)</span>
                                                <div class="progress flex-grow-1" style="height: 6px; max-width: 100px;">
                                                    <div class="progress-bar bg-primary" style="width: <?= $bobot_persen ?>%"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Total bobot info -->
                    <?php
                    $total_bobot = 0;
                    foreach ($list as $row) $total_bobot += $row['bobot'];
                    $diff = abs($total_bobot - 1.00);
                    ?>
                    <div class="alert alert-light mt-3 mb-0">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Total bobot: <strong><?= number_format($total_bobot,2) ?></strong>
                        <?php if ($diff > 0.0001): ?>
                            <span class="text-warning">(Belum 1.00, harap hubungi admin)</span>
                        <?php else: ?>
                            <span class="text-success">(Sudah 1.00 ✓)</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>