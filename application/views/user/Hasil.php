<div class="page-heading">
    <div class="page-title mb-4">
        <h3>Hasil Perhitungan SAW</h3>
        <p class="text-subtitle text-muted">Ranking alternatif berdasarkan nilai akhir</p>
    </div>

    <!-- Dropdown Periode -->
    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">Periode Penilaian</label>
            <select id="periode_id" class="form-select" onchange="location.href='?periode_id='+this.value">
                <?php foreach ($periode_list as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ($p['id'] == $periode_id_selected) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

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
                                    <th>Rank</th>
                                    <th>Kode</th>
                                    <th>Nama Alternatif</th>
                                    <th>Jabatan</th>
                                    <th>Nilai Akhir</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($hasil)): ?>
                                    <?php foreach ($hasil as $row): ?>
                                        <tr>
                                            <td><span class="badge bg-primary">#<?= $row['ranking'] ?></span></td>
                                            <td><?= htmlspecialchars($row['kode']) ?></td>
                                            <td><?= htmlspecialchars($row['nama_alternatif']) ?></td>
                                            <td><?= htmlspecialchars($row['jabatan'] ?? '-') ?></td>
                                            <td><?= number_format($row['nilai_akhir'], 4) ?></td>
                                            <td>
                                                <span class="badge <?= $row['status'] == 'Layak' ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= $row['status'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                            <p class="text-muted mb-0">Belum ada hasil perhitungan untuk periode ini. Silakan input penilaian dan proses hitung SAW.</p>
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