<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Nilai_model extends CI_Model
{
    protected $table = 'saw_penilaian';

    /**
     * Ambil semua penilaian berdasarkan user_id dan periode_id
     */
    public function get_by_user_and_periode($user_id, $periode_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('periode_id', $periode_id);
        return $this->db->get($this->table)->result_array();
    }

    
     /*Insert nilai baru*/
    public function insert_nilai($data)
    {
        return $this->db->insert($this->table, $data);
    }

    /*Hapus nilai berdasarkan kombinasi unik
     *Digunakan sebelum insert agar tidak ada duplikat*/
    public function delete_nilai($user_id, $alternatif_id, $kriteria_id, $periode_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('periode_id', $periode_id);
        $this->db->where('alternatif_id', $alternatif_id);
        $this->db->where('kriteria_id', $kriteria_id);
        return $this->db->delete($this->table);
    }

    /*Hitung jumlah penilaian untuk user dan periode tertentu*/
    public function count_by_user_and_periode($user_id, $periode_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('periode_id', $periode_id);
        return $this->db->count_all_results($this->table);
    }

    /*Hapus semua penilaian untuk user dan periode*/
    public function delete_by_user_and_periode($user_id, $periode_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('periode_id', $periode_id);
        return $this->db->delete($this->table);
    }
}