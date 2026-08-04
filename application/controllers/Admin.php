<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->set_message(
            'greater_than',
            'Kolom {field} harus berisi angka lebih besar dari {param}.'
        );
        $this->load->model('Alternatif_model');
        $this->load->model('Kriteria_model');
        $this->load->model('Hasil_model');
        $this->load->model('User_model');
        $this->load->model('Periode_model');

        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') != 'admin') {
            redirect('auth');
        }

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
            $nama = $this->session->userdata('username') ?? $this->session->userdata('email') ?? 'Administrator';
        }
        return $nama;
    }

    public function profil()
    {
        $user_id = $this->session->userdata('id');
        $user = $this->User_model->get_by_id($user_id);

        $data = [
            'title'       => 'Profil Saya',
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
            redirect('admin/profil');
        }

        if (strlen($new_password) < 4) {
            $this->session->set_flashdata('error', 'Password baru minimal 4 karakter.');
            redirect('admin/profil');
        }

        $user = $this->User_model->get_by_id($user_id);
        if (md5($old_password) !== $user->password) {
            $this->session->set_flashdata('error', 'Password lama salah.');
            redirect('admin/profil');
        }

        $this->User_model->update($user_id, ['password' => md5($new_password)]);
        $this->session->set_flashdata('success', 'Password berhasil diubah.');
        redirect('admin/profil');
    }

    public function dashboard()
    {
        $semua_hasil = $this->Hasil_model->get_ranking(null);
        $hasil_ranking = $this->Hasil_model->get_ranking(10);

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
        $total_layak_all        = 0;
        $total_tidak_layak_all  = 0;

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
                    $total_layak_all++;
                } else {
                    $dept_stats[$jabatan]['pertimbangkan']++;
                    $total_tidak_layak_all++;
                }
            }
        }

        $chart_labels         = [];
        $chart_data           = [];
        $chart_layak          = [];
        $chart_pertimbangkan  = [];

        foreach ($dept_stats as $dept => $stats) {
            $chart_labels[]        = $dept;
            $chart_data[]          = $stats['total'];
            $chart_layak[]         = $stats['layak'];
            $chart_pertimbangkan[] = $stats['pertimbangkan'];
        }

        $total_dinilai = count($semua_hasil);

        $data = [
            'title'               => 'Dashboard Admin',
            'active_menu'         => 'dashboard',
            'total_kriteria'      => $this->Kriteria_model->count_all(),
            'total_alternatif'    => $this->Alternatif_model->count_all_admin(),
            'total_hasil'         => $this->Hasil_model->count_all(),
            'hasil_ranking'       => $hasil_ranking,
            'chart_labels'        => json_encode($chart_labels),
            'chart_data'          => json_encode($chart_data),
            'chart_layak'         => json_encode($chart_layak),
            'chart_pertimbangkan' => json_encode($chart_pertimbangkan),
            'total_dinilai'       => $total_dinilai,
            'bar_labels'          => json_encode($bar_labels),
            'bar_values'          => json_encode($bar_values),
            'bar_dept'            => json_encode($bar_dept),
            'bar_status'          => json_encode($bar_status),
            'role'                => $this->session->userdata('role'),
            'nama_user'           => $this->_get_nama_user()
        ];

        $data['content'] = $this->load->view('admin/dashboard', $data, TRUE);
        $this->load->view('layout/template', $data);
    }

    // KRITERIA //
    public function kriteria()
    {
        $data = [
            'title'       => 'Kriteria & Bobot',
            'active_menu' => 'kriteria',
            'list'        => $this->Kriteria_model->get_all(),
            'role'        => $this->session->userdata('role'),
            'nama_user'   => $this->_get_nama_user()
        ];
        $data['content'] = $this->load->view('admin/kriteria', $data, TRUE);
        $this->load->view('layout/template', $data);
    }

    public function kriteria_store()
    {
        $this->form_validation->set_rules('kode', 'Kode', 'required|trim');
        $this->form_validation->set_rules('nama', 'Nama Kriteria', 'required|trim');
        $this->form_validation->set_rules('tipe', 'Tipe', 'required|in_list[benefit,cost]');
        $this->form_validation->set_rules('bobot', 'Bobot', 'required|numeric|greater_than[0]|less_than_equal_to[100]');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('admin/kriteria');
        }

        $kode = $this->input->post('kode');
        $nama = $this->input->post('nama');

        if ($this->Kriteria_model->get_by_kode($kode)) {
            $this->session->set_flashdata('error', 'Kode kriteria sudah digunakan.');
            redirect('admin/kriteria');
        }

        if ($this->Kriteria_model->get_by_nama($nama)) {
            $this->session->set_flashdata('error', 'Nama kriteria sudah digunakan.');
            redirect('admin/kriteria');
        }

        $bobot_baru = $this->input->post('bobot');
        $total_existing = $this->Kriteria_model->sum_bobot() * 100;

        if ($total_existing + $bobot_baru > 100) {
            $sisa = 100 - $total_existing;
            $this->session->set_flashdata('error', "Total bobot tidak boleh melebihi 100%. Sisa bobot tersedia: {$sisa}%");
            redirect('admin/kriteria');
        }

        $bobot_desimal = $bobot_baru / 100;
        $this->Kriteria_model->insert([
            'user_id' => $this->session->userdata('id'),
            'kode'    => $kode,
            'nama'    => $nama,
            'tipe'    => $this->input->post('tipe'),
            'bobot'   => $bobot_desimal,
        ]);

        $this->session->set_flashdata('success', 'Kriteria berhasil ditambahkan.');
        redirect('admin/kriteria');
    }

    public function kriteria_update(int $id)
    {
        $this->form_validation->set_rules('kode', 'Kode', 'required|trim');
        $this->form_validation->set_rules('nama', 'Nama Kriteria', 'required|trim');
        $this->form_validation->set_rules('tipe', 'Tipe', 'required|in_list[benefit,cost]');
        $this->form_validation->set_rules('bobot', 'Bobot', 'required|numeric|greater_than[0]|less_than_equal_to[100]');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('admin/kriteria');
        }

        $kode = $this->input->post('kode');
        $nama = $this->input->post('nama');

        if ($this->Kriteria_model->get_by_kode_except_id($kode, $id)) {
            $this->session->set_flashdata('error', 'Kode kriteria sudah digunakan.');
            redirect('admin/kriteria');
        }

        if ($this->Kriteria_model->get_by_nama_except_id($nama, $id)) {
            $this->session->set_flashdata('error', 'Nama kriteria sudah digunakan.');
            redirect('admin/kriteria');
        }

        $bobot_baru = $this->input->post('bobot');
        $old = $this->Kriteria_model->get_by_id($id);
        if (!$old) {
            $this->session->set_flashdata('error', 'Data kriteria tidak ditemukan.');
            redirect('admin/kriteria');
        }

        $bobot_lama = $old->bobot * 100;
        $total_existing = ($this->Kriteria_model->sum_bobot() * 100) - $bobot_lama;

        if ($total_existing + $bobot_baru > 100) {
            $sisa = 100 - $total_existing;
            $this->session->set_flashdata('error', "Total bobot tidak boleh melebihi 100%. Sisa bobot tersedia: {$sisa}%");
            redirect('admin/kriteria');
        }

        $bobot_desimal = $bobot_baru / 100;
        $this->Kriteria_model->update($id, [
            'kode'  => $kode,
            'nama'  => $nama,
            'tipe'  => $this->input->post('tipe'),
            'bobot' => $bobot_desimal,
        ]);

        $this->session->set_flashdata('success', 'Kriteria berhasil diperbarui.');
        redirect('admin/kriteria');
    }

    public function kriteria_delete(int $id)
    {
        $this->Kriteria_model->delete($id);
        $this->session->set_flashdata('success', 'Kriteria berhasil dihapus.');
        redirect('admin/kriteria');
    }

    // ALTERNATIF //
    public function alternatif()
    {
        $periode_id = $this->input->get('periode_id');
        $data['periode_list'] = $this->Periode_model->get_all();
        $data['periode_selected'] = $periode_id;
        $data['list'] = $this->Alternatif_model->get_all_admin($periode_id);
        $data['title'] = 'Data Alternatif';
        $data['active_menu'] = 'alternatif';
        $data['role'] = $this->session->userdata('role');
        $data['nama_user'] = $this->_get_nama_user();

        $data['content'] = $this->load->view('admin/alternatif', $data, TRUE);
        $this->load->view('layout/template', $data);
    }

    public function alternatif_store()
    {
        $this->form_validation->set_rules('kode', 'Kode', 'required|trim');
        $this->form_validation->set_rules('nama', 'Nama Alternatif', 'required|trim');
        $this->form_validation->set_rules('jabatan', 'Jabatan', 'trim');
        $this->form_validation->set_rules('periode_id', 'Periode', 'required|integer');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('admin/alternatif');
        }

        $periode_id = $this->input->post('periode_id');
        $kode = $this->input->post('kode');
        $nama = $this->input->post('nama');
        $jabatan = $this->input->post('jabatan');

        // Kode = identitas tetap karyawan -> harus unik secara GLOBAL, bukan per periode
        $existing_kode = $this->Alternatif_model->get_by_kode($kode);
        if ($existing_kode) {
            if (strcasecmp(trim($existing_kode->nama), trim($nama)) !== 0) {
                // Kode sudah dipakai tapi oleh nama yang BEDA -> jelas salah input, tolak
                $this->session->set_flashdata('error', "Kode \"{$kode}\" sudah terdaftar atas nama \"{$existing_kode->nama}\". Gunakan kode lain untuk karyawan berbeda.");
                redirect('admin/alternatif');
            }

            // Kode sudah ada & namanya sama -> kemungkinan besar karyawan yang sama,
            // tapi tetap harus dicegah kalau dia sudah punya baris di periode yang SAMA
            $existing_di_periode_ini = $this->Alternatif_model->get_by_kode_and_periode($kode, $periode_id);
            if ($existing_di_periode_ini) {
                $this->session->set_flashdata('error', "Karyawan dengan kode \"{$kode}\" sudah dinilai pada periode ini.");
                redirect('admin/alternatif');
            }
        }

        $existing_nama = $this->Alternatif_model->get_by_nama_jabatan_periode($nama, $jabatan, $periode_id);
        if ($existing_nama) {
            $this->session->set_flashdata('error', 'Nama dan jabatan alternatif sudah digunakan pada periode ini.');
            redirect('admin/alternatif');
        }

        $this->Alternatif_model->insert([
            'periode_id' => $periode_id,
            'user_id'    => 1,
            'kode'       => $kode,
            'nama'       => $nama,
            'jabatan'    => $jabatan
        ]);

        $this->session->set_flashdata('success', 'Data karyawan berhasil ditambahkan.');
        redirect('admin/alternatif');
    }

    public function alternatif_update(int $id)
    {
        $this->form_validation->set_rules('kode', 'Kode', 'required|trim');
        $this->form_validation->set_rules('nama', 'Nama Alternatif', 'required|trim');
        $this->form_validation->set_rules('jabatan', 'Jabatan', 'trim');
        $this->form_validation->set_rules('periode_id', 'Periode', 'required|integer');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('admin/alternatif');
        }

        $periode_id = $this->input->post('periode_id');
        $kode = $this->input->post('kode');
        $nama = $this->input->post('nama');
        $jabatan = $this->input->post('jabatan');

        // Kode = identitas tetap karyawan -> harus unik secara GLOBAL (kecuali baris ini sendiri)
        $existing_kode = $this->Alternatif_model->get_by_kode_except_id($kode, $id);
        if ($existing_kode) {
            if (strcasecmp(trim($existing_kode->nama), trim($nama)) !== 0) {
                $this->session->set_flashdata('error', "Kode \"{$kode}\" sudah terdaftar atas nama \"{$existing_kode->nama}\". Gunakan kode lain untuk karyawan berbeda.");
                redirect('admin/alternatif');
            }

            $existing_di_periode_ini = $this->Alternatif_model->get_by_kode_and_periode_except_id($kode, $periode_id, $id);
            if ($existing_di_periode_ini) {
                $this->session->set_flashdata('error', "Karyawan dengan kode \"{$kode}\" sudah dinilai pada periode ini.");
                redirect('admin/alternatif');
            }
        }

        $existing_nama = $this->Alternatif_model->get_by_nama_jabatan_periode_except_id($nama, $jabatan, $periode_id, $id);
        if ($existing_nama) {
            $this->session->set_flashdata('error', 'Nama dan jabatan alternatif sudah digunakan pada periode ini.');
            redirect('admin/alternatif');
        }

        $this->Alternatif_model->update($id, [
            'periode_id' => $periode_id,
            'kode'       => $kode,
            'nama'       => $nama,
            'jabatan'    => $jabatan
        ]);

        $this->session->set_flashdata('success', 'Data karyawan berhasil diperbarui.');
        redirect('admin/alternatif');
    }

    public function alternatif_delete(int $id)
    {
        $this->Alternatif_model->delete($id);
        $this->session->set_flashdata('success', 'Data karyawan berhasil dihapus.');
        redirect('admin/alternatif');
    }

    public function alternatif_delete_massal()
    {
        $ids = $this->input->post('ids');
        if (!empty($ids)) {
            $this->db->where_in('id', $ids);
            $this->db->delete('alternatif');
            $this->session->set_flashdata('success', count($ids) . 'data karyawan berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Tidak ada data yang dipilih.');
        }
        redirect('admin/alternatif');
    }

    // ALTERNATIF - Upload file excel //
    public function alternatif_upload()
    {
        $periode_id = $this->input->post('periode_id');

        if (empty($periode_id)) {
            $this->session->set_flashdata('error', '"Pilih periode terlebih dahulu sebelum upload.".');
            redirect('admin/alternatif');
        }

        if (empty($_FILES['file_excel']['name'])) {
            $this->session->set_flashdata('error', 'Silakan pilih file Excel untuk diupload.');
            redirect('admin/alternatif?periode_id=' . $periode_id);
        }

        $file = $_FILES['file_excel'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['xlsx', 'xls'])) {
            $this->session->set_flashdata('error', 'Format file harus .xlsx atau .xls.');
            redirect('admin/alternatif?periode_id=' . $periode_id);
        }

        require_once APPPATH . '../vendor/autoload.php';

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        } catch (\Exception $e) {
            $this->session->set_flashdata('error', 'Gagal membaca file Excel. Pastikan format sesuai.');
            redirect('admin/alternatif?periode_id=' . $periode_id);
        }

        $inserted          = 0;
        $skipped_duplicate = 0;
        $skipped_empty     = 0;
        $errors            = [];

        // Cari baris header untuk upload excel secara otomatis
        $headerRow = -1;

        foreach ($rows as $index => $row) {
            foreach ($row as $cell) {
                $cell = strtolower(trim((string)$cell));

                if (
                    strpos($cell, 'nama') !== false ||
                    strpos($cell, 'karyawan') !== false
                ) {
                    $headerRow = $index;
                    break 2;
                }
            }
        }

        if ($headerRow == -1) {
            $this->session->set_flashdata(
                'error',
                'Header Excel tidak ditemukan.'
            );
            redirect('admin/alternatif?periode_id=' . $periode_id);
        }

        $header = array_map('trim', $rows[$headerRow]);

        $colNama = null;
        $colJabatan = null;

        foreach ($header as $idx => $value) {
            $value = strtolower(trim($value));

            if (
                $colNama === null &&
                (
                    strpos($value, 'nama') !== false ||
                    strpos($value, 'karyawan') !== false
                )
            ) {
                $colNama = $idx;
            }

            if (
                $colJabatan === null &&
                (
                    strpos($value, 'jabatan') !== false ||
                    strpos($value, 'departemen') !== false
                )
            ) {
                $colJabatan = $idx;
            }
        }

        if ($colNama === null) {
            $this->session->set_flashdata(
                'error',
                'Kolom Nama tidak ditemukan.'
            );
            redirect('admin/alternatif?periode_id=' . $periode_id);
        }

        foreach ($rows as $i => $row) {
            if ($i <= $headerRow) {
                continue;
            }

            $nama = isset($row[$colNama]) ? trim($row[$colNama]) : '';

            $jabatan = '';

            if ($colJabatan !== null) {
                $jabatan = trim($row[$colJabatan]);
            }

            if (empty($nama)) {
                $skipped_empty++;
                continue;
            }

            if (!preg_match("/^[A-Za-z.,'\\-\\s]+$/", $nama)) {
                $errors[] = 'Baris '.($i+1).': Nama "'.$nama.'" mengandung angka tidak diperbolehkan.';
                continue;
            }

            if ($this->Alternatif_model->get_by_nama_jabatan_periode($nama, $jabatan, $periode_id)) {
                $skipped_duplicate++;
                continue;
            }

            $kode = 'A'.$this->Alternatif_model->get_next_kode_number();

            $this->Alternatif_model->insert([
                'periode_id' => $periode_id,
                'user_id'    => 1,
                'kode'       => $kode,
                'nama'       => $nama,
                'jabatan'    => $jabatan,
            ]);

            $inserted++;
        }

        $summary = "{$inserted} data karyawan berhasil ditambahkan.";
        if ($skipped_duplicate > 0) $summary .= " {$skipped_duplicate} dilewati (nama & jabatan sudah ada di periode ini).";
        if ($skipped_empty > 0)     $summary .= " {$skipped_empty} baris kosong dilewati.";

        if (!empty($errors)) {
            $this->session->set_flashdata('error', implode('<br>', array_slice($errors, 0, 5)));
        }
        $this->session->set_flashdata('success', $summary);
        redirect('admin/alternatif?periode_id=' . $periode_id);
    }
}