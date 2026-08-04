<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Periode_model extends CI_Model
{
    protected $table = 'periode';

    public function get_all()
    {
        return $this->db->order_by('tanggal_mulai', 'ASC')->get($this->table)->result_array();
    }

    public function get_active()
    {
        return $this->db->where('is_active', 1)->get($this->table)->row();
    }

    public function set_active($id)
    {
        $this->db->update($this->table, ['is_active' => 0]); // nonaktifkan semua
        return $this->db->update($this->table, ['is_active' => 1], ['id' => $id]);
    }

    public function insert($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function get_by_id($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

//cari periode aktif berdasarkan bulan berjalan,
public function get_active_by_month()
{
    $year  = date('Y');
    $month = date('m');

    $periode = $this->db
        ->where('YEAR(tanggal_mulai)', $year)
        ->where('MONTH(tanggal_mulai)', $month)
        ->get($this->table)
        ->row();

    if ($periode) {
        return $periode;
    }

    return $this->db
        ->where('YEAR(tanggal_mulai)', $year)
        ->order_by('tanggal_mulai', 'ASC')
        ->get($this->table)
        ->row();
}

    //Ambil periode yang sudah punya hasil SAW (untuk ditampilkan di dashboard user)
    public function get_periode_with_hasil()
    {
        $this->db->select('p.*');
        $this->db->from($this->table . ' p');
        $this->db->join('saw_hasil h', 'h.periode_id = p.id', 'inner');
        $this->db->group_by('p.id');
        $this->db->order_by('p.tanggal_mulai', 'DESC');
        return $this->db->get()->result_array();
    }
}