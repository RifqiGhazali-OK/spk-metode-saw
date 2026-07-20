<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Alternatif_model extends CI_Model
{
    protected $table = 'alternatif';

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

    // --- Tambahan validasi duplikat nama ---
    public function get_by_nama_and_periode($nama, $periode_id)
    {
        $this->db->where('nama', $nama);
        $this->db->where('periode_id', $periode_id);
        return $this->db->get($this->table)->row();
    }

    public function get_by_nama_and_periode_except_id($nama, $periode_id, $id)
    {
        $this->db->where('nama', $nama);
        $this->db->where('periode_id', $periode_id);
        $this->db->where('id !=', $id);
        return $this->db->get($this->table)->row();
    }
}