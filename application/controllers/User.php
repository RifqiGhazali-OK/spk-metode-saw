<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends User_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Hasil_model');
        $this->load->model('Periode_model');
        $this->load->model('User_model');
        $this->load->model('Kriteria_model');
        $this->load->model('Saw_model');

        if (empty($this->session->userdata('nama'))) {
            $user = $this->User_model->get_by_id($this->session->userdata('id'));
            if ($user) {
                $nama = !empty($user->username) ? $user->username : $user->email;
                $this->session->set_userdata('nama', $nama);
            }
        }
    }

    private function _get_nama_user()
    {
        $nama = $this->session->userdata('nama');
        if (empty($nama)) {
            $nama = $this->session->userdata('username') ?? $this->session->userdata('email') ?? 'Manajer/Direktur';
        }
        return $nama;
    }

    // ============================================
    // PROFIL (lihat & update nama + password)
    // ============================================
    public function profil()
    {
        $user_id = $this->session->userdata('id');
        $user = $this->User_model->get_by_id($user_id);

        $data = [
            'title'       => 'Profil Saya',
            'active_menu' => 'profil',
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
        $user    = $this->User_model->get_by_id($user_id);

        if (!$user) {
            $this->session->set_flashdata('error', 'Data pengguna tidak ditemukan.');
            redirect('user/profil');
        }

        $username      = trim($this->input->post('username'));
        $old_password  = $this->input->post('old_password');
        $new_password  = $this->input->post('new_password');
        $confirm       = $this->input->post('confirm_password');

        $update_data = [];

        // --- Update nama/username (opsional, hanya jika diisi & berubah) ---
        if (!empty($username) && $username !== $user->username) {
            $update_data['username'] = $username;
        }

        // --- Update password (opsional, hanya jika field password diisi) ---
        $mau_ganti_password = !empty($old_password) || !empty($new_password) || !empty($confirm);

        if ($mau_ganti_password) {
            if (empty($old_password) || empty($new_password) || empty($confirm)) {
                $this->session->set_flashdata('error', 'Untuk mengganti password, semua kolom password wajib diisi.');
                redirect('user/profil');
            }

            if (md5($old_password) !== $user->password) {
                $this->session->set_flashdata('error', 'Password lama salah.');
                redirect('user/profil');
            }

            if ($new_password !== $confirm) {
                $this->session->set_flashdata('error', 'Konfirmasi password baru tidak cocok.');
                redirect('user/profil');
            }

            if (strlen($new_password) < 4) {
                $this->session->set_flashdata('error', 'Password baru minimal 4 karakter.');
                redirect('user/profil');
            }

            $update_data['password'] = md5($new_password);
        }

        if (empty($update_data)) {
            $this->session->set_flashdata('error', 'Tidak ada perubahan yang disimpan.');
            redirect('user/profil');
        }

        $this->User_model->update($user_id, $update_data);

        // Sinkronkan nama di session supaya langsung berubah di navbar tanpa perlu login ulang
        if (isset($update_data['username'])) {
            $this->session->set_userdata('nama', $update_data['username']);
        }

        $this->session->set_flashdata('success', 'Profil berhasil diperbarui.');
        redirect('user/profil');
    }

    // ============================================
    // DASHBOARD (read-only, sama struktur data dgn admin)
    // ============================================
    public function dashboard()
    {
        $semua_hasil    = $this->Hasil_model->get_ranking(null);
        $hasil_ranking  = $this->Hasil_model->get_ranking(10);

        $bar_labels = [];
        $bar_values = [];
        $bar_dept   = [];
        $bar_status = [];

        if (!empty($hasil_ranking)) {
            foreach ($hasil_ranking as $row) {
                $bar_labels[] = $row['nama_alternatif'] ?? $row['nama'] ?? '-';
                $nilai        = (float)($row['nilai_akhir'] ?? 0);
                $bar_values[] = $nilai;
                $bar_dept[]   = $row['jabatan'] ?? '-';
                $bar_status[] = ($nilai >= 0.70) ? 'Layak' : 'pertimbangkan';
            }
        }

        $dept_stats = [];
        if (!empty($semua_hasil)) {
            foreach ($semua_hasil as $row) {
                $jabatan  = !empty($row['jabatan']) ? trim($row['jabatan']) : 'Lainnya';
                $is_layak = ((float)$row['nilai_akhir'] >= 0.70);

                if (!isset($dept_stats[$jabatan])) {
                    $dept_stats[$jabatan] = ['total' => 0, 'layak' => 0, 'pertimbangkan' => 0];
                }

                $dept_stats[$jabatan]['total']++;
                if ($is_layak) {
                    $dept_stats[$jabatan]['layak']++;
                } else {
                    $dept_stats[$jabatan]['pertimbangkan']++;
                }
            }
        }

        $chart_labels        = [];
        $chart_data          = [];
        $chart_layak         = [];
        $chart_pertimbangkan = [];

        foreach ($dept_stats as $dept => $stats) {
            $chart_labels[]        = $dept;
            $chart_data[]          = $stats['total'];
            $chart_layak[]         = $stats['layak'];
            $chart_pertimbangkan[] = $stats['pertimbangkan'];
        }

        $periode_list = $this->Periode_model->get_periode_with_hasil();

        $total_pertimbangkan = 0;
        if (!empty($semua_hasil)) {
            foreach ($semua_hasil as $row) {
                if ((float)$row['nilai_akhir'] < 0.70) {
                    $total_pertimbangkan++;
                }
            }
        }

        $data = [
            'title'                => 'Dashboard',
            'active_menu'          => 'dashboard',
            'total_hasil'          => $this->Hasil_model->count_all(),
            'total_periode'        => count($periode_list),
            'total_pertimbangkan'  => $total_pertimbangkan,
            'hasil_ranking'        => $hasil_ranking,
            'chart_labels'         => json_encode($chart_labels),
            'chart_data'           => json_encode($chart_data),
            'chart_layak'          => json_encode($chart_layak),
            'chart_pertimbangkan'  => json_encode($chart_pertimbangkan),
            'bar_labels'           => json_encode($bar_labels),
            'bar_values'           => json_encode($bar_values),
            'bar_dept'             => json_encode($bar_dept),
            'bar_status'           => json_encode($bar_status),
            'role'                 => $this->session->userdata('role'),
            'nama_user'            => $this->_get_nama_user()
        ];

        $data['content'] = $this->load->view('user/dashboard', $data, TRUE);
        $this->load->view('layout/template', $data);
    }

    // ============================================
    // DAFTAR PERIODE
    // ============================================
    public function periode()
    {
        $data = [
            'title'       => 'Periode Penilaian',
            'active_menu' => 'periode',
            'list'        => $this->Periode_model->get_periode_with_hasil(),
            'role'        => $this->session->userdata('role'),
            'nama_user'   => $this->_get_nama_user()
        ];

        $data['content'] = $this->load->view('user/periode', $data, TRUE);
        $this->load->view('layout/template', $data);
    }

    // ============================================
    // DETAIL PERHITUNGAN (read-only, per periode)
    // ============================================
    public function detail_perhitungan($periode_id)
    {
        $periode = $this->Periode_model->get_by_id($periode_id);
        if (!$periode) {
            show_404();
        }

        $owner_user_id = $this->Saw_model->get_owner_user_id($periode_id);
        $saw = $this->Saw_model->calculate_saw($owner_user_id, $periode_id);

        $data = [
            'title'       => 'Detail Perhitungan - ' . $periode->nama,
            'active_menu' => 'periode',
            'periode'     => $periode,
            'saw'         => $saw,
            'role'        => $this->session->userdata('role'),
            'nama_user'   => $this->_get_nama_user()
        ];

        $data['content'] = $this->load->view('user/detail_perhitungan', $data, TRUE);
        $this->load->view('layout/template', $data);
    }

    // ============================================
    // HASIL REKOMENDASI (read-only)
    // ============================================
    public function hasil_rekomendasi($periode_id)
    {
        $periode = $this->Periode_model->get_by_id($periode_id);
        if (!$periode) {
            show_404();
        }

        $data = [
            'title'       => 'Hasil Rekomendasi - ' . $periode->nama,
            'active_menu' => 'periode',
            'periode'     => $periode,
            'hasil'       => $this->Hasil_model->get_ranking(null, null, $periode_id),
            'role'        => $this->session->userdata('role'),
            'nama_user'   => $this->_get_nama_user()
        ];

        $data['content'] = $this->load->view('user/hasil_rekomendasi', $data, TRUE);
        $this->load->view('layout/template', $data);
    }
}