<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class Export_excel extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Alternatif_model');
        $this->load->model('Kriteria_model');
        $this->load->model('Nilai_model');

        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
    }

    private function _get_active_periode()
    {
        return 1;
    }

    private function _calculate_saw($user_id, $periode_id)
    {
        $kriteria   = $this->Kriteria_model->get_all();
        $alternatif = $this->Alternatif_model->get_all_by_periode($periode_id);
        $penilaian  = $this->Nilai_model->get_by_user_and_periode($user_id, $periode_id);

        $matrix = [];

        foreach ($alternatif as $alt) {
            foreach ($kriteria as $krit) {
                $matrix[$alt['id']][$krit['id']] = 0;
            }
        }

        foreach ($penilaian as $p) {
            $matrix[$p['alternatif_id']][$p['kriteria_id']] = (float)$p['nilai'];
        }

        $normalized = [];
        $weighted   = [];
        $final      = [];

        foreach ($kriteria as $krit) {
            $krit_id = $krit['id'];
            $bobot   = (float)$krit['bobot'];

            $nilai_kolom = array_column($matrix, $krit_id);

            if ($krit['tipe'] == 'benefit') {
                $max = max($nilai_kolom);
                foreach ($alternatif as $alt) {
                    $alt_id = $alt['id'];
                    $norm = ($max > 0) ? ($matrix[$alt_id][$krit_id] / $max) : 0;

                    $normalized[$alt_id][$krit_id] = (float)$norm;
                    $weighted[$alt_id][$krit_id] = (float)($norm * $bobot);
                    $final[$alt_id] = (float)(($final[$alt_id] ?? 0) + $weighted[$alt_id][$krit_id]);
                }
            } else {
                $nilai_valid = array_filter($nilai_kolom, function ($v) {
                    return $v > 0;
                });
                $min = !empty($nilai_valid) ? min($nilai_valid) : 1;

                foreach ($alternatif as $alt) {
                    $alt_id = $alt['id'];
                    $val = $matrix[$alt_id][$krit_id];
                    $norm = ($val > 0) ? ($min / $val) : 0;

                    $normalized[$alt_id][$krit_id] = (float)$norm;
                    $weighted[$alt_id][$krit_id] = (float)($norm * $bobot);
                    $final[$alt_id] = (float)(($final[$alt_id] ?? 0) + $weighted[$alt_id][$krit_id]);
                }
            }
        }

        return [
            'matrix'      => $matrix,
            'normalized'  => $normalized,
            'weighted'    => $weighted,
            'final'       => $final,
            'kriteria'    => $kriteria,
            'alternatif'  => $alternatif
        ];
    }

    public function index()
    {
        $this->download();
    }

    public function download()
    {
        $user_id    = (int)$this->session->userdata('id');
        $periode_id = (int)($this->input->get('periode_id') ?: $this->_get_active_periode());

        $periode = $this->db->get_where('periode', ['id' => $periode_id])->row();
        $saw = $this->_calculate_saw($user_id, $periode_id);

        $final_sorted = $saw['final'];
        arsort($final_sorted);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan SAW');

        // ======================================================
        // PRESET STYLES (Sesuai Desain HTML Anda)
        // ======================================================
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(11);

        $styleHeader = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2F5597'] // Biru Tua Header
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $styleSubHeader = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9E1F2'] // Biru Muda Sub-Header
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $styleAltHeader = [ // Style untuk baris "Alternatif: KRY-01..."
            'font' => ['bold' => true, 'color' => ['rgb' => '375623']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2EFDA'] // Hijau Muda
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['vertical'   => Alignment::VERTICAL_CENTER]
        ];

        // ======================================================
        // JUDUL LAPORAN
        // ======================================================
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'LAPORAN PERHITUNGAN SIMPLE ADDITIVE WEIGHTING (SAW)');
        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', 'Sistem Pendukung Keputusan Perpanjangan Kontrak Karyawan');
        $sheet->mergeCells('A3:F3');
        $sheet->setCellValue('A3', 'Periode : ' . ($periode->nama ?? 'Semua Periode'));

        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14);
        $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 5;

        // ======================================================
        // 1. DATA BOBOT KRITERIA
        // ======================================================
        $sheet->mergeCells("A$row:D$row");
        $sheet->setCellValue("A$row", '1. Data Bobot Kriteria');
        $sheet->getStyle("A$row:D$row")->applyFromArray($styleSubHeader);
        $row++;

        $sheet->fromArray(['No', 'Kode - Nama Kriteria', 'Tipe', 'Bobot'], NULL, "A$row");
        $sheet->getStyle("A$row:D$row")->applyFromArray($styleHeader);
        $row++;

        $no = 1;
        foreach ($saw['kriteria'] as $krit) {
            $sheet->setCellValue("A$row", $no++);
            $sheet->setCellValue("B$row", $krit['kode'] . ' - ' . $krit['nama']);
            $sheet->setCellValue("C$row", strtoupper($krit['tipe']));
            $sheet->setCellValue("D$row", (float)$krit['bobot']);

            $sheet->getStyle("A$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C$row:D$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D$row")->getNumberFormat()->setFormatCode('0.00');

            // Warna teks Benefit/Cost
            if ($krit['tipe'] == 'benefit') {
                $sheet->getStyle("C$row")->getFont()->getColor()->setRGB('00B050');
                $sheet->getStyle("C$row")->getFont()->setBold(true);
            } else {
                $sheet->getStyle("C$row")->getFont()->getColor()->setRGB('C00000');
                $sheet->getStyle("C$row")->getFont()->setBold(true);
            }
            $row++;
        }
        $sheet->getStyle("A6:D" . ($row - 1))->applyFromArray($styleBorder);
        $row += 2;

        // ======================================================
        // 2. MATRIKS PENILAIAN (NILAI MENTAH)
        // ======================================================
        $lastCol = chr(64 + count($saw['kriteria']) + 2); // Dinamis mengikuti jumlah kriteria
        $sheet->mergeCells("A$row:$lastCol$row");
        $sheet->setCellValue("A$row", '2. Matriks Penilaian (Nilai Mentah)');
        $sheet->getStyle("A$row:$lastCol$row")->applyFromArray($styleSubHeader);
        $row++;

        $col = 'A';
        $sheet->setCellValue($col++ . $row, 'No');
        $sheet->setCellValue($col++ . $row, 'Nama Alternatif');
        foreach ($saw['kriteria'] as $krit) {
            $sheet->setCellValue($col++ . $row, $krit['kode']);
        }
        $sheet->getStyle("A$row:$lastCol$row")->applyFromArray($styleHeader);
        $row++;

        $no = 1;
        foreach ($saw['alternatif'] as $alt) {
            $col = 'A';
            $sheet->setCellValue($col++ . $row, $no++);
            $sheet->setCellValue($col++ . $row, $alt['kode'] . ' - ' . $alt['nama']);

            foreach ($saw['kriteria'] as $krit) {
                $value = $saw['matrix'][$alt['id']][$krit['id']] ?? 0;
                $sheet->setCellValue($col . $row, (float)$value);
                $sheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('0.00');
                $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $col++;
            }
            $sheet->getStyle("A$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }
        $sheet->getStyle("A" . ($row - count($saw['alternatif']) - 1) . ":$lastCol" . ($row - 1))->applyFromArray($styleBorder);
        $row += 2;

        // ======================================================
        // 3. DETAIL PERHITUNGAN SAW
        // ======================================================
        $sheet->mergeCells("A$row:E$row");
        $sheet->setCellValue("A$row", '3. Detail Perhitungan SAW');
        $sheet->getStyle("A$row:E$row")->applyFromArray($styleSubHeader);
        $row++;

        foreach ($final_sorted as $alt_id => $score) {
            $alt = null;
            foreach ($saw['alternatif'] as $a) {
                if ($a['id'] == $alt_id) {
                    $alt = $a;
                    break;
                }
            }
            if (!$alt) continue;

            // Baris Hijau Nama Alternatif
            $sheet->mergeCells("A$row:E$row");
            $sheet->setCellValue("A$row", '▶ Alternatif : ' . $alt['nama'] . ' (' . $alt['kode'] . ')');
            $sheet->getStyle("A$row:E$row")->applyFromArray($styleAltHeader);
            $row++;

            // Sub-Header Tabel Detail
            $sheet->fromArray(['Kriteria', 'Xij (Mentah)', 'Rij (Normalisasi)', 'Wj (Bobot)', 'Wj × Rij'], NULL, "A$row");
            $sheet->getStyle("A$row:E$row")->applyFromArray($styleSubHeader);
            $sheet->getStyle("A$row:E$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;

            // Isi Detail Per Kriteria
            foreach ($saw['kriteria'] as $krit) {
                $sheet->setCellValue("A$row", $krit['kode'] . ' - ' . $krit['nama']);
                $sheet->setCellValue("B$row", (float)($saw['matrix'][$alt_id][$krit['id']] ?? 0));
                $sheet->setCellValue("C$row", (float)($saw['normalized'][$alt_id][$krit['id']] ?? 0));
                $sheet->setCellValue("D$row", (float)$krit['bobot']);
                $sheet->setCellValue("E$row", (float)($saw['weighted'][$alt_id][$krit['id']] ?? 0));

                $sheet->getStyle("B$row")->getNumberFormat()->setFormatCode('0.00');
                $sheet->getStyle("C$row")->getNumberFormat()->setFormatCode('0.0000');
                $sheet->getStyle("D$row")->getNumberFormat()->setFormatCode('0.00');
                $sheet->getStyle("E$row")->getNumberFormat()->setFormatCode('0.0000');

                $sheet->getStyle("B$row:E$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;
            }

            // Total Vi
            $sheet->mergeCells("A$row:D$row");
            $sheet->setCellValue("A$row", 'TOTAL NILAI AKHIR (Vi)');
            $sheet->setCellValue("E$row", (float)$score);

            $sheet->getStyle("A$row:E$row")->applyFromArray($styleBorder);
            $sheet->getStyle("A$row:E$row")->getFont()->setBold(true);
            $sheet->getStyle("A$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("E$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Highlight Kuning Total
            $sheet->getStyle("E$row")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF2CC');
            $sheet->getStyle("E$row")->getFont()->getColor()->setRGB('C00000');
            $sheet->getStyle("E$row")->getNumberFormat()->setFormatCode('0.0000');
            $row += 2;
        }

        // ======================================================
        // 4. HASIL AKHIR & RANKING
        // ======================================================
        $sheet->mergeCells("A$row:F$row");
        $sheet->setCellValue("A$row", '4. Ringkasan Hasil Akhir & Ranking');
        $sheet->getStyle("A$row:F$row")->applyFromArray($styleSubHeader);
        $row++;

        $sheet->fromArray(['Rank', 'Kode', 'Nama', 'Jabatan', 'Nilai Akhir (Vi)', 'Status'], NULL, "A$row");
        $sheet->getStyle("A$row:F$row")->applyFromArray($styleHeader);
        $row++;

        $rank = 1;
        $startRowHasil = $row;
        foreach ($final_sorted as $alt_id => $score) {
            $alt = null;
            foreach ($saw['alternatif'] as $a) {
                if ($a['id'] == $alt_id) {
                    $alt = $a;
                    break;
                }
            }
            if (!$alt) continue;

            // Threshold 0.70
            $status = ($score >= 0.70) ? 'LAYAK' : 'PERTIMBANGKAN';

            $sheet->setCellValue("A$row", $rank++);
            $sheet->setCellValue("B$row", $alt['kode']);
            $sheet->setCellValue("C$row", $alt['nama']);
            $sheet->setCellValue("D$row", $alt['jabatan'] ?? '-');
            $sheet->setCellValue("E$row", (float)$score);
            $sheet->setCellValue("F$row", $status);

            $sheet->getStyle("A$row:B$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E$row:F$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E$row")->getNumberFormat()->setFormatCode('0.0000');

            // Highlight sel Nilai Akhir
            $sheet->getStyle("E$row")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF2CC');
            $sheet->getStyle("E$row")->getFont()->setBold(true);

            // Warna Status
            $sheet->getStyle("F$row")->getFont()->setBold(true);
            if ($status == 'LAYAK') {
                $sheet->getStyle("F$row")->getFont()->getColor()->setRGB('00B050');
            } else {
                $sheet->getStyle("F$row")->getFont()->getColor()->setRGB('C00000');
            }
            $row++;
        }
        $sheet->getStyle("A$startRowHasil:F" . ($row - 1))->applyFromArray($styleBorder);
        $row += 2;

        // ======================================================
        // KETERANGAN (LEGEND) 
        // ======================================================
        $sheet->setCellValue("A$row", "Keterangan Status:");
        $sheet->getStyle("A$row")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("A$row", "■ LAYAK");
        $sheet->getStyle("A$row")->getFont()->getColor()->setRGB('00B050');
        $sheet->getStyle("A$row")->getFont()->setBold(true);

        $sheet->setCellValue("B$row", "Nilai Akhir (Vi) ≥ 0.70");
        $sheet->mergeCells("B$row:F$row"); 
        $row++;

        $sheet->setCellValue("A$row", "■ PERTIMBANGKAN");
        $sheet->getStyle("A$row")->getFont()->getColor()->setRGB('C00000');
        $sheet->getStyle("A$row")->getFont()->setBold(true);

        $sheet->setCellValue("B$row", "Nilai Akhir (Vi) ≤ 0.70");
        $sheet->mergeCells("B$row:F$row");
        $row += 2; 

        $sheet->mergeCells("A$row:F$row");
        $sheet->setCellValue("A$row", "* Laporan ini dihasilkan secara otomatis oleh Sistem Pendukung Keputusan menggunakan metode SAW.");
        $sheet->getStyle("A$row")->getFont()->setItalic(true)->getColor()->setRGB('777777');

        // ======================================================
        // AUTO SIZE COLUMNS
        // ======================================================
        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // ======================================================
        // OUTPUT DOWNLOAD
        // ======================================================
        $nama_periode = $periode->nama ?? 'Semua_Periode';
        $nama_periode = preg_replace('/[^A-Za-z0-9\-]/', '_', $nama_periode);
        $filename = 'Laporan_SAW_' . $nama_periode . '.xlsx';
        
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}