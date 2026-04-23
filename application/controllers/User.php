<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Alternatif_model');
        $this->load->model('Kriteria_model');
        $this->load->model('Hasil_model');
        $this->load->model('User_model');
        $this->load->model('Periode_model');

        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'user') {
            redirect('auth');
        }

        if (empty($this->session->userdata('nama'))) {
            $user = $this->User_model->get_by_id($this->session->userdata('id'));
            if ($user) {
                $nama = !empty($user->username) ? $user->username : $user->email;
                $this->session->set_userdata('nama', $nama);
            }
        }

        $this->_ensure_current_year_periodes();
        $this->_set_active_periode_by_current_month();
    }

    private function _get_nama_user()
    {
        $nama = $this->session->userdata('nama');
        if (empty($nama)) {
            $nama = $this->session->userdata('username') ?? $this->session->userdata('email') ?? 'User';
        }
        return $nama;
    }

    private function _ensure_current_year_periodes()
    {
        $current_year = date('Y');
        $start_date = $current_year . '-01-01';
        $end_date = $current_year . '-12-31';

        $this->db->where('tanggal_mulai >=', $start_date);
        $this->db->where('tanggal_mulai <=', $end_date);
        $exist = $this->db->get('periode')->num_rows();

        if ($exist == 0) {
            for ($month = 1; $month <= 12; $month++) {
                $month_name = date('F', mktime(0, 0, 0, $month, 1));
                $nama_periode = $month_name . ' ' . $current_year;
                $tanggal_mulai = $current_year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
                $is_active = ($month == date('n')) ? 1 : 0;
                $this->db->insert('periode', [
                    'nama' => $nama_periode,
                    'tanggal_mulai' => $tanggal_mulai,
                    'is_active' => $is_active
                ]);
            }
        }
    }

    // ============================================
    // PROFIL USER
    // ============================================
    public function profil()
    {
        $user_id = $this->session->userdata('id');
        $user = $this->User_model->get_by_id($user_id);

        $data = [
            'title'       => 'Profil Saya',
            'active_menu' => '',
            'user'        => $user,
            'role'        => $this->session->userdata('role'),
            'nama_user'   => $this->_get_nama_user()
        ];

        $data['content'] = $this->load->view('profil', $data, TRUE);
        $this->load->view('layout/template', $data);
    }

    public function profil_update()
    {
        $user_id = $this->session->userdata('id');
        $old_password = $this->input->post('old_password');
        $new_password = $this->input->post('new_password');
        $confirm = $this->input->post('confirm_password');

        if ($new_password !== $confirm) {
            $this->session->set_flashdata('error', 'Konfirmasi password baru tidak cocok.');
            redirect('user/profil');
        }

        if (strlen($new_password) < 4) {
            $this->session->set_flashdata('error', 'Password baru minimal 4 karakter.');
            redirect('user/profil');
        }

        $user = $this->User_model->get_by_id($user_id);
        if (md5($old_password) !== $user->password) {
            $this->session->set_flashdata('error', 'Password lama salah.');
            redirect('user/profil');
        }

        $this->User_model->update($user_id, ['password' => md5($new_password)]);
        $this->session->set_flashdata('success', 'Password berhasil diubah.');
        redirect('user/profil');
    }

    private function _set_active_periode_by_current_month()
    {
        $current_year = date('Y');
        $current_month = date('m');
        $this->db->where('YEAR(tanggal_mulai)', $current_year);
        $this->db->where('MONTH(tanggal_mulai)', $current_month);
        $active = $this->db->get('periode')->row();
        if ($active && $active->is_active != 1) {
            $this->db->where('YEAR(tanggal_mulai)', $current_year);
            $this->db->update('periode', ['is_active' => 0]);
            $this->db->where('YEAR(tanggal_mulai)', $current_year);
            $this->db->where('MONTH(tanggal_mulai)', $current_month);
            $this->db->update('periode', ['is_active' => 1]);
        }
    }

    private function _get_active_periode()
    {
        $current_year = date('Y');
        $current_month = date('m');
        $this->db->where('YEAR(tanggal_mulai)', $current_year);
        $this->db->where('MONTH(tanggal_mulai)', $current_month);
        $periode = $this->db->get('periode')->row();
        if ($periode) return $periode->id;

        $this->db->where('YEAR(tanggal_mulai)', $current_year);
        $this->db->order_by('tanggal_mulai', 'ASC');
        $periode = $this->db->get('periode')->row();
        return $periode ? $periode->id : 1;
    }

    public function dashboard()
    {
        $user_id = $this->session->userdata('id');
        $periode_id = $this->_get_active_periode();

        $data = [
            'title'            => 'Dashboard User',
            'active_menu'      => 'dashboard',
            'role'             => $this->session->userdata('role'),
            'nama_user'        => $this->_get_nama_user(),
            'total_alternatif' => $this->Alternatif_model->count_all_by_periode($periode_id),
            'total_hasil'      => $this->Hasil_model->count_all($user_id, $periode_id),
            'total_kriteria'   => $this->Kriteria_model->count_all(),
            'hasil_ranking'    => $this->Hasil_model->get_ranking(10, $user_id, $periode_id),
            'top_nilai'        => $this->Hasil_model->get_top_nilai($user_id, $periode_id),
        ];

        $data['content'] = $this->load->view('user/dashboard', $data, TRUE);
        $this->load->view('layout/template', $data);
    }

    public function kriteria()
    {
        $data = [
            'title'       => 'Kriteria & Bobot',
            'active_menu' => 'kriteria',
            'list'        => $this->Kriteria_model->get_all(),
            'role'        => $this->session->userdata('role'),
            'nama_user'   => $this->_get_nama_user()
        ];

        $data['content'] = $this->load->view('user/kriteria', $data, TRUE);
        $this->load->view('layout/template', $data);
    }

    public function penilaian()
    {
        $user_id = $this->session->userdata('id');
        $periode_id = $this->input->get('periode_id');
        if (!$periode_id) {
            $periode_id = $this->_get_active_periode();
        }

        $alternatif = $this->Alternatif_model->get_all_by_periode($periode_id);
        $kriteria   = $this->Kriteria_model->get_all();

        $this->db->where('user_id', $user_id);
        $this->db->where('periode_id', $periode_id);
        $nilai_existing = $this->db->get('saw_penilaian')->result_array();

        $nilai_map = [];
        foreach ($nilai_existing as $n) {
            $nilai_map[$n['alternatif_id']][$n['kriteria_id']] = $n['nilai'];
        }

        $this->db->order_by('tanggal_mulai', 'ASC');
        $periode_list = $this->db->get('periode')->result_array();

        $data = [
            'title'          => 'Input Penilaian',
            'active_menu'    => 'penilaian',
            'alternatif'     => $alternatif,
            'kriteria'       => $kriteria,
            'nilai_map'      => $nilai_map,
            'periode_list'   => $periode_list,
            'periode_id_selected' => $periode_id,
            'role'           => $this->session->userdata('role'),
            'nama_user'      => $this->_get_nama_user()
        ];

        $data['content'] = $this->load->view('user/penilaian', $data, TRUE);
        $this->load->view('layout/template', $data);
    }

    public function penilaian_save()
    {
        $user_id = $this->session->userdata('id');
        $alternatif_id = $this->input->post('alternatif_id');
        $kriteria_id   = $this->input->post('kriteria_id');
        $nilai         = $this->input->post('nilai');
        $periode_id    = $this->input->post('periode_id');
        if (!$periode_id) {
            $periode_id = $this->_get_active_periode();
        }

        $this->db->where('user_id', $user_id);
        $this->db->where('periode_id', $periode_id);
        $this->db->where('alternatif_id', $alternatif_id);
        $this->db->where('kriteria_id', $kriteria_id);
        $exists = $this->db->get('saw_penilaian')->row();

        if ($exists) {
            $this->db->where('id', $exists->id);
            $this->db->update('saw_penilaian', ['nilai' => $nilai]);
        } else {
            $this->db->insert('saw_penilaian', [
                'user_id'       => $user_id,
                'periode_id'    => $periode_id,
                'alternatif_id' => $alternatif_id,
                'kriteria_id'   => $kriteria_id,
                'nilai'         => $nilai
            ]);
        }

        echo json_encode(['status' => 'success']);
    }

    public function proses_saw()
    {
        $user_id = $this->session->userdata('id');
        $periode_id = $this->input->get('periode_id');
        if (!$periode_id) {
            $periode_id = $this->_get_active_periode();
        }

        $kriteria = $this->Kriteria_model->get_all();
        if (empty($kriteria)) {
            $this->session->set_flashdata('error', 'Belum ada data kriteria. Hubungi admin.');
            redirect('user/penilaian?periode_id=' . $periode_id);
        }

        $alternatif = $this->Alternatif_model->get_all_by_periode($periode_id);
        if (empty($alternatif)) {
            $this->session->set_flashdata('error', 'Belum ada data alternatif untuk periode ini. Hubungi admin.');
            redirect('user/penilaian?periode_id=' . $periode_id);
        }

        $total_required = count($alternatif) * count($kriteria);
        $this->db->where('user_id', $user_id);
        $this->db->where('periode_id', $periode_id);
        $jumlah_penilaian = $this->db->count_all_results('saw_penilaian');
        if ($jumlah_penilaian < $total_required) {
            $this->session->set_flashdata('error', 'Penilaian belum lengkap! Harap isi semua nilai (0-100).');
            redirect('user/penilaian?periode_id=' . $periode_id);
        }

        $this->db->where('user_id', $user_id);
        $this->db->where('periode_id', $periode_id);
        $penilaian = $this->db->get('saw_penilaian')->result_array();

        $matrix = [];
        foreach ($alternatif as $alt) {
            foreach ($kriteria as $krit) {
                $matrix[$alt['id']][$krit['id']] = 0;
                foreach ($penilaian as $p) {
                    if ($p['alternatif_id'] == $alt['id'] && $p['kriteria_id'] == $krit['id']) {
                        $matrix[$alt['id']][$krit['id']] = $p['nilai'];
                        break;
                    }
                }
            }
        }

        $normalized = [];
        $weighted = [];
        $final = [];

        foreach ($kriteria as $krit) {
            $krit_id = $krit['id'];
            $tipe = $krit['tipe'];
            $nilai_kolom = array_column($matrix, $krit_id);
            if ($tipe == 'benefit') {
                $max = max($nilai_kolom);
                if ($max == 0) $max = 1;
                foreach ($alternatif as $alt) {
                    $norm = $matrix[$alt['id']][$krit_id] / $max;
                    $normalized[$alt['id']][$krit_id] = $norm;
                    $weighted[$alt['id']][$krit_id] = $norm * $krit['bobot'];
                }
            } else {
                $min = min($nilai_kolom);
                if ($min == 0) $min = 1;
                foreach ($alternatif as $alt) {
                    $val = $matrix[$alt['id']][$krit_id];
                    if ($val == 0) $val = 1;
                    $norm = $min / $val;
                    $normalized[$alt['id']][$krit_id] = $norm;
                    $weighted[$alt['id']][$krit_id] = $norm * $krit['bobot'];
                }
            }
        }

        foreach ($alternatif as $alt) {
            $total = 0;
            foreach ($kriteria as $krit) {
                $total += $weighted[$alt['id']][$krit['id']];
            }
            $final[$alt['id']] = $total;
        }

        $data = [
            'title'       => 'Proses Perhitungan SAW',
            'active_menu' => 'hitung',
            'alternatif'  => $alternatif,
            'kriteria'    => $kriteria,
            'matrix'      => $matrix,
            'normalized'  => $normalized,
            'weighted'    => $weighted,
            'final'       => $final,
            'periode_id'  => $periode_id,
            'role'        => $this->session->userdata('role'),
            'nama_user'   => $this->_get_nama_user()
        ];

        $data['content'] = $this->load->view('user/proses_saw', $data, TRUE);
        $this->load->view('layout/template', $data);
    }

    public function simpan_hasil()
    {
        $user_id = $this->session->userdata('id');
        $final_json = $this->input->post('final');
        $periode_id = $this->input->post('periode_id');
        if (!$final_json || !$periode_id) {
            $this->session->set_flashdata('error', 'Data tidak lengkap.');
            redirect('user/penilaian');
        }
        $final = json_decode($final_json, true);

        arsort($final);
        $ranking = 1;
        $data_hasil = [];
        foreach ($final as $alt_id => $nilai_akhir) {
            $status = ($nilai_akhir >= 0.7) ? 'Layak' : 'Tidak Layak';
            $data_hasil[] = [
                'user_id'       => $user_id,
                'periode_id'    => $periode_id,
                'alternatif_id' => $alt_id,
                'nilai_akhir'   => $nilai_akhir,
                'ranking'       => $ranking++,
                'status'        => $status
            ];
        }

        $this->db->delete('saw_hasil', ['user_id' => $user_id, 'periode_id' => $periode_id]);
        if (!empty($data_hasil)) {
            $this->db->insert_batch('saw_hasil', $data_hasil);
            $this->session->set_flashdata('success', 'Perhitungan SAW berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Tidak ada data yang diproses.');
        }
        redirect('user/hasil?periode_id=' . $periode_id);
    }

    public function hasil()
    {
        $user_id = $this->session->userdata('id');
        $periode_id = $this->input->get('periode_id');
        if (!$periode_id) {
            $periode_id = $this->_get_active_periode();
        }

        $this->db->order_by('tanggal_mulai', 'ASC');
        $periode_list = $this->db->get('periode')->result_array();

        $data = [
            'title'       => 'Hasil SAW',
            'active_menu' => 'hasil',
            'hasil'       => $this->Hasil_model->get_ranking(100, $user_id, $periode_id),
            'periode_list' => $periode_list,
            'periode_id_selected' => $periode_id,
            'role'        => $this->session->userdata('role'),
            'nama_user'   => $this->_get_nama_user()
        ];

        $data['content'] = $this->load->view('user/hasil', $data, TRUE);
        $this->load->view('layout/template', $data);
    }
}