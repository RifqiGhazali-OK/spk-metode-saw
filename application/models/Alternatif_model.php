<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Alternatif_model extends CI_Model
{
    protected $table = 'alternatif';

    // FUNGSI CRUD DASAR & PENGAMBILAN DATA//
    public function get_all_by_periode($periode_id)
    {
        $this->db->where('periode_id', $periode_id);
        return $this->db->get($this->table)->result_array();
    }

    public function count_all_by_periode($periode_id)
    {
        $this->db->where('periode_id', $periode_id);
        return $this->db->count_all_results($this->table);
    }

    public function get_all_admin($periode_id = null)
    {
        if ($periode_id) $this->db->where('periode_id', $periode_id);
        return $this->db->get($this->table)->result_array();
    }

    public function count_all_admin($periode_id = null)
    {
        if ($periode_id) $this->db->where('periode_id', $periode_id);
        return $this->db->count_all_results($this->table);
    }

    public function get_all_global()
    {
        $this->db->where('user_id', 1);
        return $this->db->get($this->table)->result_array();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    public function insert($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data)
    {
        return $this->db->update($this->table, $data, ['id' => $id]);
    }

    public function delete($id)
    {
        return $this->db->delete($this->table, ['id' => $id]);
    }

   
    //FUNGSI PENGECEKAN KODE & COUNTER//
    public function get_by_kode($kode)
    {
        return $this->db->get_where($this->table, ['kode' => $kode])->row();
    }

    public function get_by_kode_except_id($kode, $id)
    {
        $this->db->where('kode', $kode);
        $this->db->where('id !=', $id);
        return $this->db->get($this->table)->row();
    }

    public function get_by_kode_and_periode($kode, $periode_id)
    {
        $this->db->where('kode', $kode);
        $this->db->where('periode_id', $periode_id);
        return $this->db->get($this->table)->row();
    }

    public function get_by_kode_and_periode_except_id($kode, $periode_id, $id)
    {
        $this->db->where('kode', $kode);
        $this->db->where('periode_id', $periode_id);
        $this->db->where('id !=', $id);
        return $this->db->get($this->table)->row();
    }

    public function get_max_kode_number()
    {
        $this->db->select("MAX(CAST(SUBSTRING(kode, 2) AS UNSIGNED)) as max_num", false);
        $row = $this->db->get($this->table)->row();
        return $row && $row->max_num !== null ? (int) $row->max_num : 0;
    }

    public function get_next_kode_number()
    {
        $this->db->query(
            "INSERT INTO alternatif_kode_counter (id, last_number)
             SELECT 1, IFNULL(MAX(CAST(SUBSTRING(kode, 2) AS UNSIGNED)), 0) FROM {$this->table}
             ON DUPLICATE KEY UPDATE last_number = last_number"
        );
        $this->db->query("UPDATE alternatif_kode_counter SET last_number = last_number + 1 WHERE id = 1");
        $row = $this->db->query("SELECT last_number FROM alternatif_kode_counter WHERE id = 1")->row();
        
        return (int) $row->last_number;
    }


    //FUNGSI VALIDASI DUPLIKAT (NAMA & JABATAN)
    // ----- [A] FUNGSI RETURN ARRAY (Dipakai oleh Upload Excel & Edit) -----

    public function get_by_nama_jabatan_periode($nama, $jabatan, $periode_id)
    {
        $this->db->where('periode_id', $periode_id);
        $this->db->where('LOWER(TRIM(nama))', strtolower(trim($nama)));
        $this->db->where('LOWER(TRIM(jabatan))', strtolower(trim($jabatan)));
        return $this->db->get($this->table)->row_array();
    }

    public function get_by_nama_jabatan_periode_except_id($nama, $jabatan, $periode_id, $id)
    {
        $this->db->where('periode_id', $periode_id);
        $this->db->where('LOWER(TRIM(nama))', strtolower(trim($nama)));
        $this->db->where('LOWER(TRIM(jabatan))', strtolower(trim($jabatan)));
        $this->db->where('id !=', $id);
        return $this->db->get($this->table)->row_array();
    }

    public function get_by_nama_periode($nama, $periode_id)
    {
        $this->db->where('periode_id', $periode_id);
        $this->db->where('LOWER(TRIM(nama))', strtolower(trim($nama)));
        return $this->db->get($this->table)->row_array();
    }

    //FUNGSI RETURN OBJECT (Dipakai oleh Controller Legacy)//
    public function get_by_nama_jabatan_and_periode($nama, $jabatan, $periode_id)
    {
        $this->db->where('periode_id', $periode_id);
        $this->db->where('LOWER(TRIM(nama))', strtolower(trim($nama)));
        $this->db->where('LOWER(TRIM(jabatan))', strtolower(trim($jabatan)));
        return $this->db->get($this->table)->row();
    }

    public function get_by_nama_jabatan_and_periode_except_id($nama, $jabatan, $periode_id, $id)
    {
        $this->db->where('periode_id', $periode_id);
        $this->db->where('LOWER(TRIM(nama))', strtolower(trim($nama)));
        $this->db->where('LOWER(TRIM(jabatan))', strtolower(trim($jabatan)));
        $this->db->where('id !=', $id);
        return $this->db->get($this->table)->row();
    }

    public function get_by_nama_and_periode($nama, $periode_id)
    {
        $this->db->where('periode_id', $periode_id);
        $this->db->where('LOWER(TRIM(nama))', strtolower(trim($nama)));
        return $this->db->get($this->table)->row();
    }

    public function get_by_nama_and_periode_except_id($nama, $periode_id, $id)
    {
        $this->db->where('periode_id', $periode_id);
        $this->db->where('LOWER(TRIM(nama))', strtolower(trim($nama)));
        $this->db->where('id !=', $id);
        return $this->db->get($this->table)->row();
    }

    public function get_all_by_nama_and_periode($nama, $periode_id)
    {
        $this->db->where('periode_id', $periode_id);
        $this->db->where('LOWER(TRIM(nama))', strtolower(trim($nama)));
        return $this->db->get($this->table)->result();
    }

    //PENGHITUNG TOTAL BARIS//
    public function cek_duplicate_nama_jabatan($nama, $jabatan, $periode_id)
    {
        $this->db->where('periode_id', $periode_id);
        $this->db->where('LOWER(TRIM(nama))', strtolower(trim($nama)));
        $this->db->where('LOWER(TRIM(jabatan))', strtolower(trim($jabatan)));
        return $this->db->count_all_results($this->table); 
    }
}