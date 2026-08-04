<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Saw_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Kriteria_model', 'Alternatif_model', 'Nilai_model']);
    }

    /**
     * Cari user_id pemilik data alternatif/penilaian pada periode tertentu.
     * Berguna untuk role 'user' (Direktur) yang tidak punya data penilaian sendiri,
     * tapi perlu melihat hasil perhitungan milik admin/HR yang menginput data.
     */
    public function get_owner_user_id(int $periode_id): int
    {
        $row = $this->db
            ->select('user_id')
            ->where('periode_id', $periode_id)
            ->limit(1)
            ->get('alternatif')
            ->row();

        return $row ? (int)$row->user_id : 1;
    }

    /* PROSES PERHITUNGAN METODE SAW
     * Dipindahkan dari Saw::_calculate_saw() supaya bisa dipakai bersama
     * oleh controller Saw (admin) dan controller User (direktur, read-only),
     * sehingga angka yang ditampilkan ke kedua role dijamin identik.*/
    
    public function calculate_saw(int $user_id, int $periode_id): array
    {
        // 1. Ambil data master dan nilai
        $kriteria   = $this->Kriteria_model->get_all();
        $alternatif = $this->Alternatif_model->get_all_by_periode($periode_id);
        $penilaian  = $this->Nilai_model->get_by_user_and_periode($user_id, $periode_id);

        // 2. Bentuk Matriks Keputusan (X)
        $matrix = [];
        foreach ($alternatif as $alt) {
            foreach ($kriteria as $krit) {
                $matrix[$alt['id']][$krit['id']] = 0;
            }
        }

        foreach ($penilaian as $p) {
            $matrix[$p['alternatif_id']][$p['kriteria_id']] = (float)$p['nilai'];
        }

        // 3. Proses Normalisasi (R) dan Pembobotan (V)
        $normalized = [];
        $weighted   = [];
        $final      = [];

        foreach ($kriteria as $krit) {
            $kid   = (int)$krit['id'];
            $bobot = (float)$krit['bobot'];
            $tipe  = $krit['tipe'];
            $kolom = array_column($matrix, $kid);

            if ($tipe === 'benefit') {
                $max = !empty($kolom) ? max($kolom) : 1;
                if ($max <= 0) $max = 1;

                foreach ($alternatif as $alt) {
                    $aid  = (int)$alt['id'];
                    $norm = (float)$matrix[$aid][$kid] / $max;

                    $normalized[$aid][$kid] = $norm;
                    $weighted[$aid][$kid]   = $norm * $bobot;
                    $final[$aid]            = ($final[$aid] ?? 0) + $weighted[$aid][$kid];
                }
            } else {
                $valid = array_filter($kolom, fn($v) => $v > 0);
                $min   = !empty($valid) ? min($valid) : 1;

                foreach ($alternatif as $alt) {
                    $aid   = (int)$alt['id'];
                    $nilai = (float)$matrix[$aid][$kid];
                    $norm  = $nilai > 0 ? $min / $nilai : 0;

                    $normalized[$aid][$kid] = $norm;
                    $weighted[$aid][$kid]   = $norm * $bobot;
                    $final[$aid]            = ($final[$aid] ?? 0) + $weighted[$aid][$kid];
                }
            }
        }

        return [
            'matrix'     => $matrix,
            'normalized' => $normalized,
            'weighted'   => $weighted,
            'final'      => $final,
            'kriteria'   => $kriteria,
            'alternatif' => $alternatif,
        ];
    }
}