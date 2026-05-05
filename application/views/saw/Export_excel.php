<?php
// 1. Inisialisasi Variabel agar aman dari error null
$nama_periode = isset($nama_periode) ? $nama_periode : 'Tidak Diketahui';
$kriteria     = isset($kriteria) ? $kriteria : [];
$alternatif   = isset($alternatif) ? $alternatif : [];
$matrix       = isset($matrix) ? $matrix : [];
$normalized   = isset($normalized) ? $normalized : [];
$weighted     = isset($weighted) ? $weighted : [];
$final        = isset($final) ? $final : [];

// 2. Styling CSS Inline Khusus Excel
$color_primary_bg = '#2F5597'; 
$color_primary_text = '#FFFFFF'; 
$color_secondary_bg = '#D9E1F2'; 
$color_highlight_bg = '#FFF2CC'; 

$style_title      = 'style="font-size: 16pt; font-weight: bold; text-align: center;"';
$style_header     = 'style="background-color: ' . $color_primary_bg . '; color: ' . $color_primary_text . '; font-weight: bold; text-align: center; border: 1px solid #000000; padding: 8px; vertical-align: middle;"';
$style_header_sub = 'style="background-color: #EDEDED; color: #000000; font-weight: bold; text-align: center; border: 1px solid #000000; padding: 6px;"';
$style_td         = 'style="border: 1px solid #000000; padding: 6px; vertical-align: middle;"';
$style_td_center  = 'style="border: 1px solid #000000; padding: 6px; text-align: center; vertical-align: middle;"';
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        /*Excel menggunakan font default yang rapih */
        body {
            font-family: Calibri, Arial, sans-serif;
            font-size: 11pt;
        }

        table {
            border-collapse: collapse;
        }
    </style>
</head>

<body>

    <!-- JUDUL LAPORAN -->
    <table border="0">
        <tr>
            <td colspan="<?= count($kriteria) + 2 ?>" <?= $style_title ?>>LAPORAN PERHITUNGAN SIMPLE ADDITIVE WEIGHTING (SAW)</td>
        </tr>
        <tr>
            <td colspan="<?= count($kriteria) + 2 ?>" style="text-align: center; font-size: 12pt; padding-bottom: 20px;">
                Periode: <strong><?= htmlspecialchars($nama_periode) ?></strong>
            </td>
        </tr>
    </table>

    <!-- SECTION 1: MATRIKS PENILAIAN -->
    <table border="1">
        <thead>
            <tr>
                <th colspan="<?= count($kriteria) + 2 ?>" style="text-align: left; background-color: <?= $color_secondary_bg ?>; border: 1px solid #000; padding: 10px; font-weight: bold; font-size: 12pt;">
                    1. Matriks Penilaian (Nilai Mentah)
                </th>
            </tr>
            <tr>
                <th <?= $style_header ?> width="50">No</th>
                <th <?= $style_header ?> width="250">Nama Alternatif</th>
                <?php foreach ($kriteria as $krit): ?>
                    <th <?= $style_header ?> width="100"><?= $krit['kode'] ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;
            foreach ($alternatif as $alt): ?>
                <tr>
                    <td <?= $style_td_center ?>><?= $no++ ?></td>
                    <td <?= $style_td ?>><?= htmlspecialchars($alt['nama']) ?></td>
                    <?php foreach ($kriteria as $krit): ?>
                        <td <?= $style_td_center ?>><?= number_format($matrix[$alt['id']][$krit['id']] ?? 0, 2) ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Jarak Antar Tabel (2 Baris Kosong) -->
    <table>
        <tr>
            <td height="30"></td>
        </tr>
    </table>

    <!-- SECTION 2: DETAIL PERHITUNGAN -->
    <table border="1">
        <thead>
            <tr>
                <th colspan="5" style="text-align: left; background-color: <?= $color_secondary_bg ?>; border: 1px solid #000; padding: 10px; font-weight: bold; font-size: 12pt;">
                    2. Detail Perhitungan SAW
                </th>
            </tr>
        </thead>
        <?php
        $alt_map = [];
        foreach ($alternatif as $a) {
            $alt_map[$a['id']] = $a;
        }

        foreach ($final as $alt_id => $score):
            $detail_alt = isset($alt_map[$alt_id]) ? $alt_map[$alt_id] : null;
            if ($detail_alt):
        ?>
                <!-- Pemisah per Karyawan agar mudah dibaca -->
                <tr>
                    <td colspan="5" style="background-color: #E2EFDA; color: #385723; font-weight: bold; border: 1px solid #000; padding: 8px;">
                        ▶ Alternatif: <?= htmlspecialchars($detail_alt['nama']) ?> (<?= htmlspecialchars($detail_alt['kode']) ?>)
                    </td>
                </tr>
                <tr>
                    <th <?= $style_header_sub ?> width="250">Kriteria</th>
                    <th <?= $style_header_sub ?> width="100">Nilai Mentah</th>
                    <th <?= $style_header_sub ?> width="100">Normalisasi (R)</th>
                    <th <?= $style_header_sub ?> width="100">Bobot (W)</th>
                    <th <?= $style_header_sub ?> width="120">Terbobot (V)</th>
                </tr>
                <?php foreach ($kriteria as $krit): ?>
                    <tr>
                        <td <?= $style_td ?>><?= $krit['kode'] ?> - <?= $krit['nama'] ?></td>
                        <td <?= $style_td_center ?>><?= number_format($matrix[$alt_id][$krit['id']] ?? 0, 2) ?></td>
                        <td <?= $style_td_center ?>><?= number_format($normalized[$alt_id][$krit['id']] ?? 0, 3) ?></td>
                        <td <?= $style_td_center ?>><?= number_format($krit['bobot'], 2) ?></td>
                        <td <?= $style_td_center ?>><?= number_format($weighted[$alt_id][$krit['id']] ?? 0, 3) ?></td>
                    </tr>
                <?php endforeach; ?>

                <!-- Baris Total Skor Highlight -->
                <tr>
                    <td colspan="4" style="text-align: right; border: 1px solid #000; padding: 8px; font-weight: bold;">TOTAL SKOR AKHIR (Vi)</td>
                    <td style="border: 1px solid #000; text-align: center; font-weight: bold; font-size: 12pt; background-color: <?= $color_highlight_bg ?>; color: #C00000;">
                        <?= number_format($score, 3) ?>
                    </td>
                </tr>
                <!-- Jarak antar blok karyawan -->
                <tr style="height: 15px;">
                    <td colspan="5" style="border: none;"></td>
                </tr>
        <?php endif;
        endforeach; ?>
    </table>

    <!-- Jarak Antar Tabel (2 Baris Kosong) -->
    <table>
        <tr>
            <td height="30"></td>
        </tr>
    </table>

    <!-- SECTION 3: RANKING -->
    <table border="1">
        <thead>
            <tr>
                <th colspan="6" style="text-align: left; background-color: <?= $color_secondary_bg ?>; border: 1px solid #000; padding: 10px; font-weight: bold; font-size: 12pt;">
                    3. Ringkasan Hasil Akhir & Ranking
                </th>
            </tr>
            <tr>
                <th <?= $style_header ?> width="60">Rank</th>
                <th <?= $style_header ?> width="100">Kode</th>
                <th <?= $style_header ?> width="250">Nama Alternatif</th>
                <th <?= $style_header ?> width="150">Jabatan</th>
                <th <?= $style_header ?> width="120">Nilai Akhir</th>
                <th <?= $style_header ?> width="150">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $rank = 1;
            foreach ($final as $alt_id => $score):
                $detail_alt = isset($alt_map[$alt_id]) ? $alt_map[$alt_id] : null;
                if ($detail_alt):
                    $status = ($score >= 0.7) ? 'LAYAK' : 'PERTIMBANGKAN';
                    // Warna font untuk status
                    $color = ($status == 'LAYAK') ? '#00B050' : '#FF0000';
            ?>
                    <tr>
                        <td <?= $style_td_center ?> style="font-weight: bold; font-size: 12pt;"><?= $rank++ ?></td>
                        <td <?= $style_td_center ?>><?= htmlspecialchars($detail_alt['kode']) ?></td>
                        <td <?= $style_td ?> style="font-weight: bold;"><?= htmlspecialchars($detail_alt['nama']) ?></td>
                        <td <?= $style_td ?>><?= htmlspecialchars($detail_alt['jabatan'] ?? '-') ?></td>
                        <td style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: <?= $color_highlight_bg ?>;">
                            <?= number_format($score, 3) ?>
                        </td>
                        <td style="border: 1px solid #000; text-align: center; color: <?= $color ?>; font-weight: bold;">
                            <?= $status ?>
                        </td>
                    </tr>
            <?php endif;
            endforeach; ?>
        </tbody>
    </table>

</body>

</html>