<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Hasil_model extends CI_Model
{
    protected $table_hasil = 'saw_hasil';
    protected $table_alternatif = 'alternatif';

    // Menghitung jumlah hasil untuk user dan periode tertentu
    public function count_all($user_id = null, $periode_id = null)
    {
        if ($user_id) {
            $this->db->where('user_id', $user_id);
        }
        if ($periode_id) {
            $this->db->where('periode_id', $periode_id);
        }
        return $this->db->count_all_results($this->table_hasil);
    }

    public function get_ranking($limit = 10, $user_id = null, $periode_id = null)
{
    $this->db->select('a.kode, a.nama as nama_alternatif, a.jabatan, h.nilai_akhir, h.ranking, h.status');
    $this->db->from($this->table_hasil . ' h');
    $this->db->join($this->table_alternatif . ' a', 'a.id = h.alternatif_id', 'left');
    if ($user_id) {
        $this->db->where('h.user_id', $user_id);
    }
    if ($periode_id) {
        $this->db->where('h.periode_id', $periode_id);
    }
    $this->db->order_by('h.ranking', 'ASC');
    if ($limit !== null) {
        $this->db->limit($limit);
    }
    return $this->db->get()->result_array();
}

    // Mendapatkan nilai tertinggi untuk user dan periode tertentu
    public function get_top_nilai($user_id = null, $periode_id = null)
    {
        $this->db->select_max('nilai_akhir');
        if ($user_id) {
            $this->db->where('user_id', $user_id);
        }
        if ($periode_id) {
            $this->db->where('periode_id', $periode_id);
        }
        $row = $this->db->get($this->table_hasil)->row();
        return $row->nilai_akhir ?? 0;
    }

    // Untuk laporan admin (bisa difilter periode jika diperlukan)
    public function get_all_with_user($periode_id = null)
    {
        $this->db->select('a.*, h.nilai_akhir, h.ranking, h.status, u.username as user_name, h.periode_id');
        $this->db->from($this->table_hasil . ' h');
        $this->db->join($this->table_alternatif . ' a', 'a.id = h.alternatif_id');
        $this->db->join('users u', 'u.id = h.user_id');
        if ($periode_id) {
            $this->db->where('h.periode_id', $periode_id);
        }
        return $this->db->get()->result_array();
    }
    
    // Cek apakah hasil SAW sudah pernah disimpan untuk user & periode tertentu
    public function exists($user_id, $periode_id)
    {
        return $this->db
            ->where('user_id', $user_id)
            ->where('periode_id', $periode_id)
            ->count_all_results($this->table_hasil) > 0;
    }

    // Refresh (hapus lalu simpan ulang) hasil SAW untuk user & periode tertentu
    public function replace_hasil($user_id, $periode_id, array $data_hasil)
    {
        $this->db->delete($this->table_hasil, [
            'user_id'    => $user_id,
            'periode_id' => $periode_id
        ]);

        if (!empty($data_hasil)) {
            return $this->db->insert_batch($this->table_hasil, $data_hasil);
        }

        return true;
    }
}