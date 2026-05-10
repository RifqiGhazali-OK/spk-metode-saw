<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Saw extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->library('form_validation');

        $this->load->model([
            'Alternatif_model',
            'Kriteria_model',
            'Hasil_model',
            'User_model',
            'Periode_model',
            'Nilai_model'
        ]);

        // AUTH
        if (
            !$this->session->userdata('logged_in') ||
            $this->session->userdata('role') !== 'admin'
        ) {
            redirect('auth');
        }

        // SET NAMA
        if (!$this->session->userdata('nama')) {
            $user = $this->User_model->get_by_id(
                (int)$this->session->userdata('id')
            );
            if ($user) {
                $this->session->set_userdata(
                    'nama',
                    $user->username ?? $user->email
                );
            }
        }
    }

    // ======================================================
    // HELPER
    // ======================================================

    private function _get_nama_user(): string
    {
        return
            $this->session->userdata('nama')
            ?? $this->session->userdata('username')
            ?? $this->session->userdata('email')
            ?? 'Administrator';
    }

    private function _get_active_periode(): int
    {
        $year  = date('Y');
        $month = date('m');

        $this->db->where('YEAR(tanggal_mulai)', $year);
        $this->db->where('MONTH(tanggal_mulai)', $month);
        $periode = $this->db->get('periode')->row();

        if ($periode) return (int)$periode->id;

        $this->db->where('YEAR(tanggal_mulai)', $year);
        $this->db->order_by('tanggal_mulai', 'ASC');
        $periode = $this->db->get('periode')->row();

        return $periode ? (int)$periode->id : 1;
    }

    // ======================================================
    // HITUNG SAW
    // ======================================================

    private function _calculate_saw(int $user_id, int $periode_id): array
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
                    $final[$aid] = ($final[$aid] ?? 0) + $weighted[$aid][$kid];
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
                    $final[$aid] = ($final[$aid] ?? 0) + $weighted[$aid][$kid];
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

    // ======================================================
    // PENILAIAN + PROSES SAW (DIGABUNG 1 HALAMAN)
    // ======================================================

    public function penilaian(): void
    {
        $user_id = (int)$this->session->userdata('id');

        $periode_id = (int)(
            $this->input->post('periode_id')
            ?: $this->input->get('periode_id')
            ?: $this->_get_active_periode()
        );

        $alternatif     = $this->Alternatif_model->get_all_by_periode($periode_id);
        $kriteria       = $this->Kriteria_model->get_all();
        $nilai_existing = $this->Nilai_model->get_by_user_and_periode($user_id, $periode_id);
        $periode_list   = $this->db->order_by('tanggal_mulai', 'ASC')->get('periode')->result_array();

        $nilai_map = [];
        foreach ($nilai_existing as $n) {
            $nilai_map[$n['alternatif_id']][$n['kriteria_id']] = $n['nilai'];
        }

        // VALIDASI KELENGKAPAN
        $total_required   = count($alternatif) * count($kriteria);
        $jumlah_penilaian = $this->Nilai_model->count_by_user_and_periode($user_id, $periode_id);
        $penilaian_lengkap = ($total_required > 0 && $jumlah_penilaian >= $total_required);

        // PARAMETER KONTROL
        $force_edit    = (bool)$this->input->get('force_edit');
        $tombol_proses = (bool)$this->input->post('proses_hitung');

        // CEK HASIL SUDAH PERNAH DISIMPAN DI DB
        $cek_hasil = $this->db
            ->where('user_id', $user_id)
            ->where('periode_id', $periode_id)
            ->get('saw_hasil')
            ->num_rows();

        // DEFAULT
        $show_hasil = false;
        $saw = [
            'matrix'     => [],
            'normalized' => [],
            'weighted'   => [],
            'final'      => [],
            'kriteria'   => $kriteria,
            'alternatif' => $alternatif,
        ];

        // → langsung tampilkan tabel detail tanpa perlu klik apapun
        if ($cek_hasil > 0 && $penilaian_lengkap && !$force_edit && !$tombol_proses) {
            $saw        = $this->_calculate_saw($user_id, $periode_id);
            $show_hasil = true;
        }

        // KONDISI 2: Tombol "Proses Hitung" ditekan dari form input
        elseif ($tombol_proses) {
            if (!$penilaian_lengkap) {
                $this->session->set_flashdata(
                    'error',
                    'Penilaian belum lengkap! Semua nilai harus diisi terlebih dahulu.'
                );
                redirect('saw/penilaian?periode_id=' . $periode_id . '&force_edit=1');
                return;
            }
            $saw        = $this->_calculate_saw($user_id, $periode_id);
            $show_hasil = true;
        }

        // KONDISI 3: force_edit=1 atau belum ada hasil sama sekali
        // → tampil form input saja, $show_hasil tetap false
        $data = [
            // PAGE
            'title'       => 'Penilaian & Proses SAW',
            'active_menu' => 'penilaian',

            // USER
            'role'      => $this->session->userdata('role'),
            'nama_user' => $this->_get_nama_user(),

            // MASTER
            'alternatif'   => $alternatif,
            'kriteria'     => $kriteria,
            'periode_list' => $periode_list,

            // PENILAIAN
            'nilai_map'           => $nilai_map,
            'periode_id_selected' => $periode_id,
            'penilaian_lengkap'   => $penilaian_lengkap,
            'total_required'      => $total_required,
            'jumlah_penilaian'    => $jumlah_penilaian,

            // SAW
            'show_hasil' => $show_hasil,
            'saw'        => $saw,
            'matrix'     => $saw['matrix'],
            'normalized' => $saw['normalized'],
            'weighted'   => $saw['weighted'],
            'final'      => $saw['final'],
        ];

        $data['content'] = $this->load->view('saw/penilaian_saw', $data, true);
        $this->load->view('layout/template', $data);
    }

    // ======================================================
    // SAVE NILAI AJAX
    // ======================================================

    public function penilaian_save(): void
    {
        $user_id       = (int)$this->session->userdata('id');
        $alternatif_id = (int)$this->input->post('alternatif_id');
        $kriteria_id   = (int)$this->input->post('kriteria_id');
        $nilai         = (float)$this->input->post('nilai');
        $periode_id    = (int)($this->input->post('periode_id') ?: $this->_get_active_periode());

        // VALIDASI INPUT
        if (!$alternatif_id || !$kriteria_id || $nilai < 0.1) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak valid.']);
            return;
        }

        $exists = $this->Nilai_model->exists($user_id, $alternatif_id, $kriteria_id, $periode_id);

        if ($exists) {
            $this->Nilai_model->update_nilai((int)$exists->id, $nilai);
        } else {
            $this->Nilai_model->insert_nilai([
                'user_id'       => $user_id,
                'periode_id'    => $periode_id,
                'alternatif_id' => $alternatif_id,
                'kriteria_id'   => $kriteria_id,
                'nilai'         => $nilai,
            ]);
        }

        // Response AJAX sekarang menyertakan info kelengkapan.
        $alternatif       = $this->Alternatif_model->get_all_by_periode($periode_id);
        $kriteria         = $this->Kriteria_model->get_all();
        $total_required   = count($alternatif) * count($kriteria);
        $jumlah_penilaian = $this->Nilai_model->count_by_user_and_periode($user_id, $periode_id);

        echo json_encode([
            'status'            => 'success',
            'penilaian_lengkap' => ($total_required > 0 && $jumlah_penilaian >= $total_required),
            'jumlah'            => $jumlah_penilaian,
            'total'             => $total_required,
        ]);
    }

    // ======================================================
    // SIMPAN HASIL
    // ======================================================

    public function simpan_hasil(): void
    {
        $user_id    = (int)$this->session->userdata('id');
        $periode_id = (int)$this->input->post('periode_id');
        $final      = json_decode((string)$this->input->post('final'), true);

        if (empty($final) || !$periode_id) {
            $this->session->set_flashdata('error', 'Data tidak valid.');
            redirect('saw/penilaian');
            return;
        }

        arsort($final);

        $ranking    = 1;
        $data_hasil = [];
        foreach ($final as $alt_id => $nilai_akhir) {
            $data_hasil[] = [
                'user_id'       => $user_id,
                'periode_id'    => $periode_id,
                'alternatif_id' => (int)$alt_id,
                'nilai_akhir'   => (float)$nilai_akhir,
                'ranking'       => $ranking++,
                'status'        => $nilai_akhir >= 0.70 ? 'Layak' : 'Pertimbangkan',
            ];
        }
        $this->db->delete('saw_hasil', ['user_id' => $user_id, 'periode_id' => $periode_id]);
        if (!empty($data_hasil)) {
            $this->db->insert_batch('saw_hasil', $data_hasil);
            $this->session->set_flashdata('success', 'Hasil SAW berhasil disimpan.');
        }
        redirect('saw/hasil?periode_id=' . $periode_id);
    }

    // ======================================================
    // HASIL
    // ======================================================

    public function hasil(): void
    {
        $user_id    = (int)$this->session->userdata('id');
        $periode_id = (int)($this->input->get('periode_id') ?: $this->_get_active_periode());

        $periode_list = $this->db->order_by('tanggal_mulai', 'ASC')->get('periode')->result_array();
        $hasil        = $this->Hasil_model->get_ranking(100, $user_id, $periode_id);

        $data = [
            'title'               => 'Hasil SAW',
            'active_menu'         => 'hasil',
            'periode_list'        => $periode_list,
            'periode_id_selected' => $periode_id,
            'hasil'               => $hasil,
            'role'                => $this->session->userdata('role'),
            'nama_user'           => $this->_get_nama_user(),
        ];
        $data['content'] = $this->load->view('saw/hasil', $data, true);
        $this->load->view('layout/template', $data);
    }
}